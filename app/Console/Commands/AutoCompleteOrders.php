<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;

class AutoCompleteOrders extends Command
{
    protected $signature = 'orders:auto-complete';
    protected $description = 'Auto complete shipped orders after 3 days';

    public function handle()
    {
        $orders = Order::where('status', 'shipped')
            ->whereNotNull('estimated_arrival')
            ->where('estimated_arrival', '<=', Carbon::today())
            ->get();

        foreach ($orders as $order) {
            $order->update(['status' => 'delivered']);
        }

        $this->info("Auto-completed {$orders->count()} orders.");
    }
}
