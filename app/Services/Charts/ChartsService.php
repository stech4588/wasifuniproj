<?php

namespace App\Services\Charts;


use App\Models\Invoice\Invoice;
use App\Models\Order\Order;
use App\Models\Order\OrderItemDetail;
use App\Models\Product\Product;
use App\Models\User;
use App\Traits\ImageUpload;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ChartsService
{
    use ImageUpload;
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = "Charts";
    }
    private function MonthsInYear($data){
        $allMonths = range(1, 12);
        $result = [];
        foreach ($data as $row) {
            $result[] = [
                'year' => $row->year,
                'month' => $row->month,
                'total_amount' => $row->total_amount,
            ];
        }
        // Loop through all months and add missing months with total_amount 0
        foreach ($allMonths as $month) {
            $found = false;
            foreach ($result as $row) {
                if ($row['month'] == $month) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $result[] = [
                    'year' => date('Y'),
                    'month' => $month,
                    'total_amount' => 0,
                ];
            }
        }

        // Sort the results by month
        usort($result, function ($a, $b) {
            return $a['month'] - $b['month'];
        });
        return $result;

    }
    private function DaysInMonth($data)
    {
        $firstDayOfMonth = Carbon::now()->firstOfMonth();
        $lastDayOfMonth = Carbon::now()->lastOfMonth();

        $datesArray = [];

        $currentDay = clone $firstDayOfMonth;
        while ($currentDay->lte($lastDayOfMonth)) {
            $datesArray[] = $currentDay->toDateString();
            $currentDay->addDay();
        }

        $result = [];
        foreach ($data as $row) {
            $result[$row->date] = [
                'date' => $row->date,
                'total_amount' => $row->total_amount,
            ];
        }

        // Loop through all months and add missing dates with total_amount 0
        foreach ($datesArray as $day) {
            if (!isset($result[$day])) {
                $result[$day] = [
                    'date' => $day,
                    'total_amount' => 0,
                ];
            }
        }

        // Sort the results by date (as strings)
        ksort($result);

        // Reset keys to ensure numeric indexes
        $result = array_values($result);

        return $result;
    }
    private function DaysInWeek($data)
    {
        $endOfWeek = Carbon::now()->subWeek()->endOfWeek();
        $startOfWeek = Carbon::now()->subWeek()->startOfWeek();

        $datesArray = [];

        $currentDay =  clone $startOfWeek;
        while ($currentDay->lte($endOfWeek)) {
            $datesArray[] = $currentDay->toDateString();
            $currentDay->addDay();
        }

        $result = [];
        foreach ($data as $row) {
            $result[$row->date] = [
                'date' => $row->date,
                'total_amount' => $row->total_amount,
            ];
        }

        // Loop through all days in the week and add missing dates with total_amount 0
        foreach ($datesArray as $day) {
            if (!isset($result[$day])) {
                $result[$day] = [
                    'date' => $day,
                    'total_amount' => 0,
                ];
            }
        }

        // Sort the results by date (as strings)
        ksort($result);

        // Reset keys to ensure numeric indexes
        $result = array_values($result);

        return $result;
    }



    public function salesChart($dateRange)
    {
        try {

            $salesQuery = Order::where('invoice_status','paid');
            $pre = Order::where('invoice_status','paid');
            switch ($dateRange) {
                case 'last_week':
                    $endOfWeek = strtotime('last week');
                    $startOfWeek = strtotime('last week', $endOfWeek);

                    $salesQuery->whereBetween('created_at', [date( 'Y-m-d',$endOfWeek), now()->startOfWeek()]);
                    $totalAmount = $salesQuery->whereBetween('created_at', [date( 'Y-m-d',$endOfWeek), now()->startOfWeek()])->sum('total_amount');

                    $pre->whereBetween('created_at', [date( 'Y-m-d',$startOfWeek), date('Y-m-d', $endOfWeek)]);
                    break;
                case 'this_month':
                    $salesQuery->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month);
                    $totalAmount = $salesQuery->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->sum('total_amount');
                    $startDate = now()->subMonth()->startOfMonth(); // Start of the last month
                    $endDate = now()->subMonth()->endOfMonth();     // End of the last month
                    $pre->whereBetween('created_at', [$startDate, $endDate]);
                    break;
                case 'this_year':
                    $salesQuery->whereYear('created_at', now()->year);
                    $totalAmount = $salesQuery->whereYear('created_at', now()->year)->sum('total_amount');
                    $startDate = now()->subYear()->startOfYear(); // Start of the last year
                    $endDate = now()->subYear()->endOfYear();     // End of the last year
                    $pre->whereBetween('created_at', [$startDate, $endDate]);

                    break;
                case 'today':
                    $totalAmount = $salesQuery->whereDate('created_at', now()->toDateString())->sum('total_amount');

                    break;
                // For 'all' or other cases, no additional filtering is needed.
            }
            if ($dateRange === 'this_year'){
                $salesQuery->groupByRaw('YEAR(created_at), MONTH(created_at)')
                    ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total_amount) as total_amount');
                $salesQuery = $salesQuery->get();
                $salesQuery = $this->MonthsInYear($salesQuery);

                $pre->groupByRaw('YEAR(created_at), MONTH(created_at)')
                    ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total_amount) as total_amount');
                $pre = $pre->get();
                $pre = $this->MonthsInYear($pre);
            } elseif ($dateRange === 'this_month') {
                $salesQuery->groupByRaw('DATE(created_at)')
                    ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total_amount');
                $salesQuery = $salesQuery->get();
                $salesQuery = $this->DaysInMonth($salesQuery);

                $pre->groupByRaw('DATE(created_at)')
                    ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total_amount');
                $pre = $pre->get();
                $pre = $this->DaysInMonth($pre);
            } elseif ($dateRange === 'last_week') {
                $salesQuery->groupByRaw('DATE(created_at)')
                    ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total_amount');
                $salesQuery = $salesQuery->get();
                $salesQuery = $this->DaysInWeek($salesQuery);

                $pre->groupByRaw('DATE(created_at)')
                    ->selectRaw('DATE(created_at) as date, SUM(total_amount) as total_amount');
                $pre = $pre->get();
                $pre = $this->DaysInWeek($pre);
            } elseif ($dateRange === 'all') {

                $totalAmount = $salesQuery->sum('total_amount');
            }


            return [
                'current_data' => $salesQuery,
                'previous_data' => $pre,
                'total' => $totalAmount
            ];

        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }
    public function ordersChart($dateRange)
    {
        try {

            $salesQuery = Order::query();
            $pre = Order::query();
            switch ($dateRange) {
                case 'last_week':
                    $endOfWeek = strtotime('last week');
                    $startOfWeek = strtotime('last week', $endOfWeek);

                    $salesQuery->whereBetween('created_at', [date( 'Y-m-d',$endOfWeek), now()->startOfWeek()]);
                    $totalCount = $salesQuery->whereBetween('created_at', [date( 'Y-m-d',$endOfWeek), now()->startOfWeek()])->count();

                    $pre->whereBetween('created_at', [date( 'Y-m-d',$startOfWeek), date('Y-m-d', $endOfWeek)]);
                    break;
                case 'this_month':
                    $salesQuery->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month);
                    $totalCount = $salesQuery->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();
                    $startDate = now()->subMonth()->startOfMonth(); // Start of the last month
                    $endDate = now()->subMonth()->endOfMonth();     // End of the last month
                    $pre->whereBetween('created_at', [$startDate, $endDate]);
                    break;
                case 'this_year':
                    $salesQuery->whereYear('created_at', now()->year);
                    $totalCount = $salesQuery->whereYear('created_at', now()->year)->count();
                    $startDate = now()->subYear()->startOfYear(); // Start of the last year
                    $endDate = now()->subYear()->endOfYear();     // End of the last year
                    $pre->whereBetween('created_at', [$startDate, $endDate]);

                    break;
                case 'today':
                    $totalCount = $salesQuery->whereDate('created_at', now()->toDateString())->count();
                    break;
                // For 'all' or other cases, no additional filtering is needed.
            }
            if ($dateRange === 'this_year'){
                $salesQuery->groupByRaw('YEAR(created_at), MONTH(created_at)')
                    ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total_amount');
                $salesQuery = $salesQuery->get();
                $salesQuery = $this->MonthsInYear($salesQuery);

                $pre->groupByRaw('YEAR(created_at), MONTH(created_at)')
                    ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total_amount');
                $pre = $pre->get();
                $pre = $this->MonthsInYear($pre);
            } elseif ($dateRange === 'this_month') {
                $salesQuery->groupByRaw('DATE(created_at)')
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as total_amount');
                $salesQuery = $salesQuery->get();
                $salesQuery = $this->DaysInMonth($salesQuery);

                $pre->groupByRaw('DATE(created_at)')
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as total_amount');
                $pre = $pre->get();
                $pre = $this->DaysInMonth($pre);
            } elseif ($dateRange === 'last_week') {
                $salesQuery->groupByRaw('DATE(created_at)')
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as total_amount');
                $salesQuery = $salesQuery->get();
                $salesQuery = $this->DaysInWeek($salesQuery);

                $pre->groupByRaw('DATE(created_at)')
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as total_amount');
                $pre = $pre->get();
                $pre = $this->DaysInWeek($pre);
            } elseif ($dateRange === 'all') {

                $totalCount = $salesQuery->count();
            }


            return [
                'current_data' => $salesQuery,
                'previous_data' => $pre,
                'total' => $totalCount
            ];

        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }
    public function cartChart($dateRange)
    {
        try {

            $cartQuery = Order::query();
            $cartQuery2 = Order::query();
            $cartQuery3 = Order::query();
            switch ($dateRange) {
                case 'last_week':
                    $endOfWeek = strtotime('last week');

                    $confirmedOrder = $cartQuery->whereBetween('created_at', [date( 'Y-m-d',$endOfWeek), now()->startOfWeek()])->where('status','confirmed')->count();
                    $draftOrder = $cartQuery2->whereBetween('created_at', [date( 'Y-m-d',$endOfWeek), now()->startOfWeek()])->where('status','draft')->count();
                    $rejectedOrder = $cartQuery3->whereBetween('created_at', [date( 'Y-m-d',$endOfWeek), now()->startOfWeek()])->where('status','rejected')->count();

                    break;
                case 'this_month':
                    $confirmedOrder = $cartQuery->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->where('status','confirmed')->count();
                    $draftOrder = $cartQuery2->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->where('status','draft')->count();
                    $rejectedOrder = $cartQuery3->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->where('status','rejected')->count();
                    break;
                case 'this_year':
                    $confirmedOrder = $cartQuery->whereYear('created_at', now()->year)->where('status','confirmed')->count();
                    $draftOrder = $cartQuery2->whereYear('created_at', now()->year)->where('status','draft')->count();
                    $rejectedOrder = $cartQuery3->whereYear('created_at', now()->year)->where('status','rejected')->count();

                    break;
                case 'today':
                    $confirmedOrder = $cartQuery->whereDate('created_at', now()->toDateString())->where('status','confirmed')->count();
                    $draftOrder = $cartQuery2->whereDate('created_at', now()->toDateString())->where('status','draft')->count();
                    $rejectedOrder = $cartQuery3->whereDate('created_at', now()->toDateString())->where('status','rejected')->count();
                    break;
            }
            if ($dateRange === 'all') {
                $confirmedOrder = $cartQuery->where('status','confirmed')->count();
                $draftOrder = $cartQuery2->where('status','draft')->count();
                $rejectedOrder = $cartQuery3->where('status','rejected')->count();
            }


            return [
                'confirmedOrder' => $confirmedOrder,
                'draftOrder' => $draftOrder,
                'rejectedOrder' => $rejectedOrder,
                'total' => $confirmedOrder + $draftOrder + $rejectedOrder
            ];

        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    public function salesOverview($dateRange)
    {
        try {

            $salesQuery = Order::query();
            $invoiceQuery = Invoice::query();

            switch ($dateRange) {
                case 'last_week':
                    $endOfWeek = strtotime('last week');
                    $noOfOrders = $salesQuery->whereBetween('created_at', [date( 'Y-m-d',$endOfWeek), now()->startOfWeek()])->where('status','!=','rejected')->count();
                    $totalDiscount = $salesQuery->whereBetween('created_at', [date( 'Y-m-d',$endOfWeek), now()->startOfWeek()])->where('invoice_status','paid')->sum('discount');
                    $totalSales = $salesQuery->whereBetween('created_at', [date( 'Y-m-d',$endOfWeek), now()->startOfWeek()])->where('invoice_status','paid')->sum('total_amount');
                    $totalRefund = $invoiceQuery->whereBetween('created_at', [date( 'Y-m-d',$endOfWeek), now()->startOfWeek()])->where('status','refunded')->sum('price');

                    break;
                case 'this_month':
                    $salesQuery->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month);
                    $noOfOrders = $salesQuery->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->where('status','!=','rejected')->count();
                    $totalDiscount = $salesQuery->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->where('invoice_status','paid')->sum('discount');
                    $totalSales = $salesQuery->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->where('invoice_status','paid')->sum('total_amount');
                    $totalRefund = $invoiceQuery->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->where('status','refunded')->sum('price');
                    break;
                case 'this_year':
                    $salesQuery->whereYear('created_at', now()->year);
                    $noOfOrders = $salesQuery->whereYear('created_at', now()->year)->where('status','!=','rejected')->count();
                    $totalDiscount = $salesQuery->whereYear('created_at', now()->year)->where('invoice_status','paid')->sum('discount');
                    $totalSales = $salesQuery->whereYear('created_at', now()->year)->where('invoice_status','paid')->sum('total_amount');
                    $totalRefund = $invoiceQuery->whereYear('created_at', now()->year)->where('status','refunded')->sum('price');
                    break;
                case 'today':
                    $noOfOrders = $salesQuery->whereDate('created_at', now()->toDateString())->where('status','!=','rejected')->count();
                    $totalDiscount = $salesQuery->whereDate('created_at', now()->toDateString())->where('invoice_status','paid')->sum('discount');
                    $totalSales = $salesQuery->whereDate('created_at', now()->toDateString())->where('invoice_status','paid')->sum('total_amount');
                    $totalRefund = $invoiceQuery->whereDate('created_at', now()->toDateString())->where('status','refunded')->sum('price');
                    break;
                case 'all':
                    $noOfOrders = $salesQuery->count();
                    $totalDiscount = $salesQuery->where('invoice_status','paid')->sum('discount');
                    $totalSales = $salesQuery->where('invoice_status','paid')->sum('total_amount');
                    $totalRefund = $invoiceQuery->where('status','refunded')->sum('price');
                    break;
            }

            return [
                'Orders' => $noOfOrders,
                'Discounts' => $totalDiscount,
                'Net Sales' => $totalSales,
                'Refunds' => $totalRefund,
            ];

        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    public function customersOverview($dateRange)
    {
        try {

            $customersQuery = User::query();
            $totalCustomers = $customersQuery->count();

            switch ($dateRange) {
                case 'last_week':
                    $endOfWeek = strtotime('last week');
                    $newCustomers = $customersQuery->whereBetween('created_at', [date( 'Y-m-d',$endOfWeek), now()->startOfWeek()])->doesntHave('orders')->count();
                    break;
                case 'this_month':
                    $customersQuery->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month);
                    $newCustomers = $customersQuery->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->doesntHave('orders')->count();
                    break;
                case 'this_year':
                    $customersQuery->whereYear('created_at', now()->year);
                    $newCustomers = $customersQuery->whereYear('created_at', now()->year)->doesntHave('orders')->count();
                    break;
                case 'today':
                    $newCustomers = $customersQuery->whereDate('created_at', now()->toDateString())->doesntHave('orders')->count();
                    break;
                case 'all':
                    $newCustomers = $customersQuery->doesntHave('orders')->count();
                    break;
            }

            return [
                'newCustomers' => $newCustomers,
                'totalCustomers' => $totalCustomers,
            ];

        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

    public function productsOverview($dateRange)
    {
        try {

            $mostSellingProducts = OrderItemDetail::
                select('product_id', DB::raw('SUM(quantity) as total_quantity_sold'))
                ->groupBy('product_id')
                ->orderByDesc('total_quantity_sold')
                ->take(3)
                ->get();
            $products = [];

            foreach ($mostSellingProducts as $sellingProduct) {
                $product = Product::find($sellingProduct->product_id);

                if ($product) {
                    $products[] = [
                        'name' => $product->name,
                        'total_quantity_sold' => $sellingProduct->total_quantity_sold,
                    ];
                }
            }

            return [
                'productName' => $products,
            ];

        } catch (QueryException $e) {
            // Handle the database query exception here, log it or return an error response
            return ['error' => config('constants.query_error')($this->moduleName,$e->getMessage())];
        } catch (\Exception $e) {
            // Handle other exceptions
            return ['error' => config('constants.internal_error')];
        }
    }

}
