<?php

namespace App\Services\Invoice;

use App\Models\Invoice\Invoice;
use App\Models\Order\Order;
use App\Models\Product\Product;
use App\Models\Setting\Setting;
use App\Services\Category\CategoryService;
use App\Services\Order\OrderService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\ImageUpload;

class InvoiceService
{
    use ImageUpload;
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = "Invoice";
    }
    /**
     * Get all invoice
     *
     * @param null|integer $view
     * @param null|integer $page
     * @param null|integer $search
     * @param null|string  $filter
     * @return mixed
     */
    public function invoicelist($view = null, $page = null,$search = null,  $filter)
    {
        try {
            $user = Auth::user();
            $invoice = Invoice::with('order')->orderBy('id', 'desc');

            $roleName = optional(optional($user)->role)->name;
            $shouldRestrictByUser = !$user || !in_array($roleName, ['Super Admin', 'Admin']);

            if ($shouldRestrictByUser) {
                $invoice->where('user_id', optional($user)->id);
            }

            switch ($filter) {
                case 'active':
                    $invoice->where('is_active', 1);
                    break;
                case 'inactive':
                    $invoice->where('is_active', 0);
                    break;
            }

            if($search){
                $invoice = $invoice->where('id', 'like', '%' .$search. '%' )
                    ->orderByRaw('CASE
               WHEN id LIKE "'.$search.'%" THEN 1
               WHEN id LIKE "%'.$search.'%" THEN 2
               ELSE 3
               END');
            }
            if($view){
                $invoice = $invoice->paginate($view, ['*'], 'page', $page);
            } else {
                $invoice = $invoice->get();
            }

            return $invoice;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get Data To Create invoice
     *
     * @return mixed
     */
    public function getCreateData()
    {
        try {
            return ([
                'orders'   => with(new OrderService())->orderList(null, null,null,'all'),
            ]);
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Create a invoice
     *
     * @param array $data
     * @return mixed
     */
    public function store($data)
    {
        try {
            $userId = Auth::user()->id;
            Log::info('InvoiceService@store: starting invoice creation', [
                'user_id' => $userId,
                'order_id' => $data['order_id'] ?? null,
                'price' => $data['price'] ?? null,
            ]);
//            if (Invoice::nameExists($data['name'], $userId)) {
//                return [
//                    'error' => config('constants.name_exist')($this->moduleName),
//                ];
//            }

            // get auto created serial Number
            $invoiceNoResult = getNextNumber('invoice', 'invoice_no', $userId);
            if (is_array($invoiceNoResult) && isset($invoiceNoResult['error'])) {
                Log::error('InvoiceService@store: failed to fetch next invoice number', [
                    'order_id' => $data['order_id'],
                    'user_id' => $userId,
                    'error' => $invoiceNoResult['error'],
                ]);
                return [
                    'error' => $invoiceNoResult['error'],
                ];
            }

            $invoiceNo = $invoiceNoResult;

            $invoice = Invoice::create([
                'order_id'      => $data['order_id'],
                'status'        => $data['status'] ?? 'draft',
                'invoice_no'    => $invoiceNo,
                'price'         => $data['price'],
                'user_id'       => $userId,
            ]);

            $order = Order::where('id', $invoice->order_id)->first();
            $order->update([
                'invoice_status' => $invoice->status,
                'payment_status' => $invoice->status === 'paid' ? 'paid' : $order->payment_status,
            ]);

            Log::info('InvoiceService@store: invoice created', [
                'invoice_id' => $invoice->id,
                'order_id' => $invoice->order_id,
                'invoice_no' => $invoice->invoice_no,
            ]);

            return [
                'message' => config('constants.record_created')($this->moduleName),
                'data' => $invoice
            ];
        } catch (QueryException $e) {
            Log::error('InvoiceService@store: query exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            Log::error('InvoiceService@store: unexpected exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['error' => config('constants.internal_error'),];
        }
    }

    /**
     * Get a invoice
     *
     * @param integer $id
     * @return mixed
     */
    public function show($id)
    {
        try {
            $userId = Auth::user()->id;
            if (!Invoice::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            $invoice = Invoice::where('id', $id)->where('user_id', $userId)->with('order')->first();

            return $invoice;
        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    /**
     * Get a invoice for edit
     *
     * @param integer $id
     * @return mixed
     */
    public function getEditData($id)
    {
        try {
            return [
                'invoice'      => $this->show($id),
                'orders'   => with(new OrderService())->orderList(null, null,null,'all'),
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
     * Update a invoice
     *
     * @param array $data
     * @param integer $id
     * @return mixed
     */
    public function update($data, $id)
    {
        try {
            $userId = Auth::user()->id;
            if (!Invoice::exists($id, $userId)) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }

            // update Product Data.
            $update = [
                'order_id'      => $data['order_id'],
                'status'        => $data['status'] ?? 'draft',
                'price'         => $data['price'],
                'user_id'       => $userId,
            ];

            $invoice = Invoice::where('id', $id)->where('user_id', $userId)->first();
            $invoice->update($update);

            return [
                'message' => config('constants.record_updated')($this->moduleName),
                'data' => $invoice
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
     * Delete a invoice
     *
     * @param integer $id
     * @return mixed
     */
    public function destroy($id)
    {
        try {
            $userId = Auth::user()->id;
            $invoice = Invoice::where('id', $id)->where('user_id', $userId)->first();
            if (!$invoice) {
                return [
                    'error' => config('constants.not_found')($this->moduleName),
                ];
            }
            $invoice->delete();
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
     * change Invoice Status
     *
     * @param integer $id
     * @param string $status
     * @return mixed
     */
    public function changeStatus($id, $status)
    {
        try {
            $userId = Auth::user()->id;
            $invoice = Invoice::where('id', $id)->first();
            if (!$invoice) {
                return [
                    'error' => config('constants.not_found')($this->moduleName)
                ];
            }
            if ($invoice->status == $status) {
                return [
                    'error' => config('constants.already_marked')($this->moduleName, $status)
                ];
            }
            if ($invoice->status == 'draft') {
                DB::beginTransaction();
                if($status == 'paid'){
                    Invoice::find($id)->update(['status' => $status]);
                    Order::where('id', $invoice->order_id)->update(['invoice_status' => $status]);
                    $message = config('constants.marked_as')($this->moduleName, $status);
                }else{
                    Invoice::find($id)->update(['status' => $status]);
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
