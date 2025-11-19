<?php

namespace App\Services\Order;

use App\Models\Image\Image;
use App\Models\Order\Order;
use App\Models\Order\OrderItemDetail;
use App\Models\Product\Product;
use App\Models\ProductVariant;
use App\Models\Invoice\Invoice;
use App\Services\Coupons\CouponsService;
use App\Services\Invoice\InvoiceService;
use App\Services\Product\ProductService;
use App\Services\Payment\StripePaymentService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Traits\ImageUpload;

class OrderService
{
    use ImageUpload;
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = "Order";
    }
    private function productImagesUrl($products){
        foreach ($products as $p) {
            $productImage = Image::where('module_name','product')->where('module_id', $p->product_id)->where('default', 1)->pluck('image')->first();
            if ($productImage) {
                $p->image_url = asset('images/product_images/' . $productImage);
            } else {
                $p->image_url = null;
            }
        }

        return $products;
    }
    /**
     * Get all Orders
     *
     * @param null|integer $view
     * @param null|integer $page
     * @param null|integer $search
     * @param null|string  $filter
     * @return mixed
     */
    public function orderlist($view = null, $page = null,$search = null,  $filter)
    {
        try {
            $user = Auth::user();
            $order = Order::orderBy('id', 'desc');

            $roleName = optional(optional($user)->role)->name;
            $shouldRestrictByUser = !$user || !in_array($roleName, ['Super Admin', 'Admin']);

            if ($shouldRestrictByUser) {
                $order->where('user_id', optional($user)->id);
            }

            switch ($filter) {
                case 'active':
                    $order->where('is_active', 1);
                    break;
                case 'inactive':
                    $order->where('is_active', 0);
                    break;
            }

            if($search){
                $order = $order->where('id', 'like', '%' .$search. '%' )
                    ->orderByRaw('CASE
               WHEN id LIKE "'.$search.'%" THEN 1
               WHEN id LIKE "%'.$search.'%" THEN 2
               ELSE 3
               END');
            }
            if($view){
                $order = $order->paginate($view, ['*'], 'page', $page);
            } else {
                $order = $order->get();
            }
            return $order;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get Data To Create Order
     *
     * @return mixed
     */
    public function getCreateData()
    {
        try {
            return ([
                'products'   => with(new ProductService())->productlist(null, null,null,'child'),
            ]);
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Create a Order
     *
     * @param array $data
     * @return mixed
     */
    public function store($data)
    {

        try {
            Log::info('OrderService@store: starting order creation', [
                'user_id' => optional(Auth::user())->id,
                'payload_keys' => array_keys($data),
                'payment_method' => $data['payment_method'] ?? 'cod',
            ]);
            DB::beginTransaction();

            $stripeService = null;
            $stripeIntent = null;
            $paymentIntentId = $data['payment_intent_id'] ?? null;
            $stripeCurrency = $data['currency'] ?? config('services.stripe.currency', 'usd');

            $subTotal = 0;
            $saleTotal = 0;
            $couponAmount = 0;
            $currentDate = date('Y-m-d');
            $paymentMethod = $data['payment_method'] ?? 'cod';

            foreach ($data['product_details'] as $productDetails) {
                $subTotal += $productDetails['price'] * $productDetails['quantity'];
                if (!empty($productDetails['sale']) && $currentDate <= ($productDetails['sale']['end_date'] ?? $currentDate)) {
                    $saleTotal += ($productDetails['price'] / 100 * $productDetails['sale']['sale_price']) * $productDetails['quantity'];
                }
            }
            $total = $subTotal - $saleTotal;

            if (!empty($data['coupon_id'])) {
                $couponService = new CouponsService();
                $coupon = $couponService->applyCoupon($data['coupon_id']);
                if (isset($coupon['error'])) {
                    Log::warning('OrderService@store: coupon lookup failed', [
                        'coupon_id' => $data['coupon_id'],
                        'coupon_response' => $coupon,
                    ]);
                    DB::rollBack();
                    return [
                        'error' => config('constants.not_found')("Coupon Not Found"),
                    ];
                }
                if (isset($coupon['expired'])) {
                    Log::warning('OrderService@store: coupon expired', [
                        'coupon_id' => $data['coupon_id'],
                    ]);
                    DB::rollBack();
                    return [
                        'error' => config('constants.not_found')("Coupon is Expired"),
                    ];
                }

                if ($coupon['type'] === 'amount') {
                    $total -= $coupon['amount'];
                    $couponAmount = $coupon['amount'];
                } elseif ($coupon['type'] === 'percentage') {
                    $couponAmount = ($total / 100) * $coupon['percentage'];
                    if ($couponAmount > $total) {
                        Log::warning('OrderService@store: coupon amount greater than total', [
                            'coupon_id' => $data['coupon_id'],
                            'coupon_percentage' => $coupon['percentage'],
                            'total_before_coupon' => $total,
                        ]);
                        DB::rollBack();
                        return [
                            'error' => config('constants.not_found')("Low Order Amount"),
                        ];
                    }
                    $total -= $couponAmount;
                }
            }

            if ($paymentMethod === 'stripe') {
                if (!$paymentIntentId) {
                    Log::error('OrderService@store: missing payment intent id for stripe payment');
                    DB::rollBack();
                    return [
                        'error' => ['status_code' => 422, 'message' => 'Payment intent is required for card payments.'],
                    ];
                }

                $stripeService = new StripePaymentService();
                $intentResult = $stripeService->retrievePaymentIntent($paymentIntentId);
                if (isset($intentResult['error'])) {
                    DB::rollBack();
                    return [
                        'error' => ['status_code' => 422, 'message' => $intentResult['error']],
                    ];
                }

                $stripeIntent = $intentResult['intent'];
                $validStatuses = ['succeeded', 'requires_capture'];
                if (!in_array($stripeIntent->status, $validStatuses, true)) {
                    DB::rollBack();
                    return [
                        'error' => ['status_code' => 422, 'message' => 'Payment is not completed.'],
                    ];
                }

                $intentCurrency = $stripeIntent->currency ?? $stripeCurrency;
                $expectedMinorAmount = $stripeService->toMinorUnit($total, $intentCurrency);
                $intentAmountReceived = (int) ($stripeIntent->amount_received ?? 0);
                $intentAmount = $intentAmountReceived > 0 ? $intentAmountReceived : (int) ($stripeIntent->amount ?? 0);

                if ($intentAmount < $expectedMinorAmount) {
                    DB::rollBack();
                    return [
                        'error' => ['status_code' => 422, 'message' => 'Paid amount is less than order total.'],
                    ];
                }
            }

            $userId = Auth::user()->id;

            $paymentStatusValue = $paymentMethod === 'stripe' ? 'paid' : 'pending';
            $invoiceStatusValue = $paymentMethod === 'stripe' ? 'paid' : 'unpaid';
            $paymentReference = $stripeIntent->id ?? null;
            $paymentAmount = $paymentMethod === 'stripe' ? $total : null;

            $orderData = [
                'status'                 => $data['status'] ?? 'draft',
                'billing_address_id'     => $data['billing_address_id'],
                'shipping_address_id'    => $data['shipping_address_id'],
                'coupon_id'              => $data['coupon_id'] ?? null,
                'other_charge_id'        => $data['other_charge_id'] ?? null,
                'sub_total'              => $subTotal,
                'discount_type'          => $data['discount_type'] ?? null,
                'discount'               => $couponAmount ?: null,
                'total_amount'           => $total,
                'user_id'                => $userId,
            ];

            if (Schema::hasColumn('orders', 'payment_method')) {
                $orderData['payment_method'] = $paymentMethod;
            }

            if (Schema::hasColumn('orders', 'payment_status')) {
                $orderData['payment_status'] = $paymentStatusValue;
            }

            if (Schema::hasColumn('orders', 'invoice_status')) {
                $orderData['invoice_status'] = $invoiceStatusValue;
            }

            if (Schema::hasColumn('orders', 'payment_reference') && $paymentReference) {
                $orderData['payment_reference'] = $paymentReference;
            }

            if (Schema::hasColumn('orders', 'payment_amount') && $paymentAmount) {
                $orderData['payment_amount'] = $paymentAmount;
            }

            $order = Order::create($orderData);

            Log::info('OrderService@store: order record created', [
                'order_id' => $order->id,
                'payment_method' => $order->payment_method,
                'total_amount' => $order->total_amount,
            ]);

            foreach ($data['product_details'] as $productDetails) {
                $variantId = $productDetails['variant_id'];
                $variant = ProductVariant::find($variantId);

                if (!$variant) {
                    Log::error('OrderService@store: product variant not found', [
                        'variant_id' => $variantId,
                        'order_id' => $order->id,
                    ]);
                    DB::rollBack();
                    return [
                        'error' => config('constants.not_found')("Product Variant"),
                    ];
                }

                $newQuantity = $variant->quantity - $productDetails['quantity'];
                $variant->update(['quantity' => $newQuantity]);

                OrderItemDetail::create([
                    'order_id'            => $order->id,
                    'product_id'          => $productDetails['id'],
                    'sale_id'             => $productDetails['sale']['id'] ?? null,
                    'product_variant_id'  => $productDetails['variant_id'],
                    'product_name'        => $productDetails['name'],
                    'price'               => $productDetails['price'],
                    'quantity'            => $productDetails['quantity'],
                    'created_by'          => $userId,
                ]);
            }

            $invoice = null;
            if ($paymentMethod === 'cod') {
                Log::info('OrderService@store: creating COD invoice', [
                    'order_id' => $order->id,
                    'price' => $total,
                ]);
                $invoiceService = new InvoiceService();
                $invoiceResponse = $invoiceService->store([
                    'order_id' => $order->id,
                    'status'   => 'draft',
                    'price'    => $total,
                ]);

                if (isset($invoiceResponse['error'])) {
                    Log::error('OrderService@store: invoice creation failed', [
                        'order_id' => $order->id,
                        'invoice_error' => $invoiceResponse['error'],
                    ]);
                    DB::rollBack();
                    return $invoiceResponse;
                }

                $invoice = $invoiceResponse['data'] ?? null;
                Log::info('OrderService@store: invoice created', [
                    'order_id' => $order->id,
                    'invoice_id' => optional($invoice)->id,
                ]);
            } elseif ($paymentMethod === 'stripe') {
                Log::info('OrderService@store: creating Stripe invoice', [
                    'order_id' => $order->id,
                    'price' => $total,
                ]);
                $invoiceService = new InvoiceService();
                $invoiceResponse = $invoiceService->store([
                    'order_id' => $order->id,
                    'status'   => 'paid',
                    'price'    => $total,
                ]);

                if (isset($invoiceResponse['error'])) {
                    Log::error('OrderService@store: stripe invoice creation failed', [
                        'order_id' => $order->id,
                        'invoice_error' => $invoiceResponse['error'],
                    ]);
                    DB::rollBack();
                    return $invoiceResponse;
                }

                $invoice = $invoiceResponse['data'] ?? null;
                Log::info('OrderService@store: stripe invoice created', [
                    'order_id' => $order->id,
                    'invoice_id' => optional($invoice)->id,
                ]);
            }

            DB::commit();

            Log::info('OrderService@store: order creation completed', [
                'order_id' => $order->id,
                'invoice_id' => optional($invoice)->id ?? null,
            ]);

            return [
                'message' => config('constants.record_created')($this->moduleName),
                'data' => array_filter([
                    'order' => $order,
                    'invoice' => $invoice,
                ]),
            ];
        } catch (QueryException $e) {
            Log::error('OrderService@store: query exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);
            DB::rollBack();
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            Log::error('OrderService@store: unexpected exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);
            DB::rollBack();
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get a Order
     *
     * @param integer $id
     * @return mixed
     */
    public function show($id)
    {
        try {
            $user = Auth::user();
            $orderQuery = Order::with(['orderItems', 'user'])->where('id', $id);

            $roleName = optional(optional($user)->role)->name;
            $shouldRestrictByUser = !$user || !in_array($roleName, ['Super Admin', 'Admin']);

            if ($shouldRestrictByUser) {
                if (!Order::exists($id, optional($user)->id)) {
                    return [
                        'error' => config('constants.not_found')($this->moduleName),
                    ];
                }
                $orderQuery->where('user_id', optional($user)->id);
            }

            $order = $orderQuery->first();
            if (!$order) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }
            $order->order_items = $this->productImagesUrl($order->orderItems);


            return $order;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get a Order for edit
     *
     * @param integer $id
     * @return mixed
     */
    public function getEditData($id)
    {
        try {
            return [
                'Order'      => $this->show($id),
                'products'   => with(new ProductService())->productList(null, null,null,'all'),
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Update a Order
     *
     * @param array $data
     * @param integer $id
     * @return mixed
     */
    public function update($data, $id)
    {
        try {
            $user = Auth::user();

            // update Product Data.
            $update = [
                'status'                => $data['status'] ?? 'draft',
                'billing_address_id'    => $data['billing_address_id'],
                'shipping_address_id'   => $data['shipping_address_id'],
                'coupon_id'             => $data['coupon_id'] ?? null,
                'other_charge_id'       => $data['other_charge_id'] ?? null,
                'sub_total'             => $data['sub_total'],
                'discount'              => $data['discount'] ?? null,
                'total_amount'          => $data['total_amount'],
                'user_id'               => optional($user)->id,
            ];

            $orderQuery = Order::where('id', $id);
            $roleName = optional(optional($user)->role)->name;
            $shouldRestrictByUser = !$user || !in_array($roleName, ['Super Admin', 'Admin']);

            if ($shouldRestrictByUser) {
                if (!Order::exists($id, optional($user)->id)) {
                    return [
                        'error' => config('constants.not_found')($this->moduleName),
                    ];
                }
                $orderQuery->where('user_id', optional($user)->id);
            }

            $order = $orderQuery->first();
            if (!$order) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }
            $order->update($update);

            return [
                'message' => config('constants.record_updated')($this->moduleName),
                'data' => $order
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Delete a Order
     *
     * @param integer $id
     * @return mixed
     */
    public function destroy($id)
    {
        try {
            $userId = Auth::user()->id;
            $order = Order::where('id', $id)->where('user_id', $userId)->first();
            if (!$order) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }
            $order->delete();
            return [
                'message' => config('constants.record_deleted')($this->moduleName),
            ];
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * change Order Status
     *
     * @param integer $id
     * @param string $status
     * @return mixed
     */
    public function changeStatus($id, $status)
    {
        try {
            $userId = Auth::user()->id;
            $order = Order::where('id', $id)->first();
            if (!$order) {
                return [
                    'error' => config('constants.not_found')($this->moduleName)
                ];
            }
            if ($order->status == $status) {
                return [
                    'error' => config('constants.already_marked')($this->moduleName, $status)
                ];
            }
            if ($order->status == 'draft') {
            DB::beginTransaction();
                if($status == 'confirmed'){
                    $updatePayload = ['status' => $status];
                    if ($order->payment_method === 'cod') {
                        $updatePayload['invoice_status'] = 'unpaid';
                    }
                    Order::find($id)->update($updatePayload);

                    $existingInvoice = Invoice::where('order_id', $order->id)->first();
                    if (!$existingInvoice && $order->payment_method === 'cod') {
                        $invoiceData = [
                            'order_id' => $order->id,
                            'price' => $order->total_amount
                        ];
                        $invoice = with(new InvoiceService())->store($invoiceData);
                    }

                    $message = config('constants.marked_as')($this->moduleName, $status);
                }else{
                    Order::find($id)->update(['status' => $status]);
                    $message = config('constants.marked_as')($this->moduleName, $status);
                }
                DB::commit();
                return [
                    'message' => $message,
                ];
            } else {
                return config('constants.already_marked')($this->moduleName, $status);
            }
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => $e->getMessage()];
        }
    }
}
