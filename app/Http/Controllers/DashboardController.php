<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Order;
use App\Models\Part;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Workplace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $data = ['user' => $user];

        if ($user->isAdmin()) {
            $data = array_merge($data, $this->adminData());
        }

        if ($user->isClient()) {
            $data = array_merge($data, $this->clientData($user));
        }

        if ($user->isEmployee()) {
            if ($user->isManager()) {
                $data = array_merge($data, $this->managerData());
            }
            if ($user->isMechanic()) {
                $data = array_merge($data, $this->mechanicData($user));
            }
            // Сотрудник без явной должности — показываем сводку менеджера
            if (! $user->isManager() && ! $user->isMechanic()) {
                $data = array_merge($data, $this->managerData());
            }
        }

        return view('dashboard', $data);
    }

    protected function adminData(): array
    {
        $logPath = storage_path('logs/laravel.log');
        $logTail = [];
        if (File::exists($logPath)) {
            $lines = @file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            $logTail = array_slice($lines, -8);
        }

        $now = now();

        $ordersByStatus = Order::query()
            ->select('status', DB::raw('count(*) as cnt'))
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $lowStockCount = Part::query()
            ->withSum('stock as stock_qty', 'quantity')
            ->get()
            ->filter(fn ($p) => (int) ($p->stock_qty ?? 0) <= 3)
            ->count();

        return [
            'logTail' => $logTail,
            'usersCount' => User::count(),
            'clientsCount' => User::role('client')->count(),
            'employeesCount' => User::role('employee')->count(),
            'ordersCount' => Order::count(),
            'ordersToday' => Order::whereDate('ordered_at', $now->toDateString())->count(),
            'activeOrders' => Order::whereNotIn('status', ['completed', 'cancelled'])->count(),
            'partsCount' => Part::count(),
            'servicesCount' => Service::count(),
            'suppliersCount' => Supplier::count(),
            'purchasesPending' => Purchase::whereIn('status', ['pending', 'ordered', 'in_transit'])->count(),
            'paymentsTotal' => Payment::sum('amount'),
            'revenueMonth' => Payment::where('paid_at', '>=', $now->copy()->startOfMonth())->sum('amount'),
            'ordersByStatus' => $ordersByStatus,
            'lowStockCount' => $lowStockCount,
            'recentOrders' => Order::with(['client', 'car'])
                ->latest('ordered_at')
                ->limit(8)
                ->get(),
            'busyWorkplaces' => Workplace::whereHas('orders', fn ($q) => $q->where('status', 'in_progress'))->count(),
            'totalWorkplaces' => Workplace::count(),
        ];
    }

    protected function clientData(User $user): array
    {
        $orders = Order::with(['car', 'workplace'])
            ->where('user_id', $user->id)
            ->latest('ordered_at')
            ->get();

        return [
            'clientCars' => Car::where('user_id', $user->id)->get(),
            'clientOrders' => $orders,
            'clientActiveOrders' => $orders->whereNotIn('status', ['completed', 'cancelled']),
        ];
    }

    protected function managerData(): array
    {
        $now = now();

        $revenueDay = Payment::whereDate('paid_at', $now->toDateString())->sum('amount');
        $revenueWeek = Payment::where('paid_at', '>=', $now->copy()->startOfWeek())->sum('amount');
        $revenueMonth = Payment::where('paid_at', '>=', $now->copy()->startOfMonth())->sum('amount');

        $ordersByStatus = Order::query()
            ->select('status', DB::raw('count(*) as cnt'))
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $workplaceOccupancy = Workplace::with(['room', 'orders' => fn ($q) => $q
            ->where('status', 'in_progress')
            ->with('car')])
            ->get()
            ->map(fn ($wp) => [
                'workplace' => $wp,
                'busy' => $wp->orders->isNotEmpty(),
                'order' => $wp->orders->first(),
            ]);

        $employeeStats = Order::query()
            ->where('status', 'completed')
            ->whereNotNull('employee_id')
            ->select('employee_id', DB::raw('count(*) as completed_count'))
            ->groupBy('employee_id')
            ->orderByDesc('completed_count')
            ->with('employee')
            ->get();

        $lowStockParts = Part::query()
            ->withSum('stock as stock_qty', 'quantity')
            ->orderBy('stock_qty')
            ->get()
            ->filter(fn ($p) => (int) ($p->stock_qty ?? 0) <= 3)
            ->take(10)
            ->values();

        return [
            'revenueDay' => $revenueDay,
            'revenueWeek' => $revenueWeek,
            'revenueMonth' => $revenueMonth,
            'ordersByStatus' => $ordersByStatus,
            'workplaceOccupancy' => $workplaceOccupancy,
            'employeeStats' => $employeeStats,
            'lowStockParts' => $lowStockParts,
            'totalOrders' => Order::count(),
        ];
    }

    protected function mechanicData(User $user): array
    {
        return [
            'mechanicActive' => Order::with(['car', 'client', 'workplace'])
                ->where('employee_id', $user->id)
                ->where('status', 'in_progress')
                ->latest('ordered_at')
                ->get(),
            'mechanicCompleted' => Order::with(['car', 'client'])
                ->where('employee_id', $user->id)
                ->where('status', 'completed')
                ->latest('completed_at')
                ->limit(10)
                ->get(),
            'mechanicQueue' => Order::with(['car', 'client', 'workplace'])
                ->where('status', 'accepted')
                ->latest('ordered_at')
                ->limit(15)
                ->get(),
        ];
    }
}
