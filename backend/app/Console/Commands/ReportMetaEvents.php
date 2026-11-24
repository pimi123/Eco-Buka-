<?php

namespace App\Console\Commands;

use App\Models\MetaConversionEvent;
use Illuminate\Console\Command;

class ReportMetaEvents extends Command
{
    protected $signature = 'meta:report {--from=} {--to=}';
    protected $description = 'Aggregate conversion occurrence and delivery totals without personal data';
    public function handle(): int
    {
        $from = $this->option('from') ?: now()->subDays(7)->toDateString();
        $to = $this->option('to') ?: now()->addDay()->toDateString();
        $events = MetaConversionEvent::where('occurred_at', '>=', $from)->where('occurred_at', '<', $to);
        $this->table(['Event', 'Status', 'Count', 'EUR'], (clone $events)->selectRaw('event_name, status, count(*) as total, sum(value) as revenue')->groupBy('event_name', 'status')->get()->map(fn ($row) => [$row->event_name, $row->status, $row->total, $row->revenue]));
        $submitted = \App\Models\Order::where('created_at', '>=', $from)->where('created_at', '<', $to)->count();
        $payments = \App\Models\Payment::join('orders', 'orders.id', '=', 'payments.order_id')->where('payments.status', 'approved')->whereNotNull('payments.paid_at')
            ->where('payments.currency', 'EUR')->whereColumn('payments.amount', 'orders.total')
            ->selectRaw('payments.order_id, min(payments.paid_at) as first_paid_at, max(payments.amount) as paid_amount')->groupBy('payments.order_id');
        $paid = \Illuminate\Support\Facades\DB::query()->fromSub($payments, 'paid_orders')->where('first_paid_at', '>=', $from)->where('first_paid_at', '<', $to);
        $this->line('Orders submitted: '.$submitted.'; fully paid orders: '.(clone $paid)->count().'; confirmed EUR: '.(clone $paid)->sum('paid_amount').'.');
        $this->line('Suppressed duplicate event attempts: '.(clone $events)->sum('duplicate_attempts').'.');
        $this->line('Counts use occurrence time [from,to); API acceptance is not advertising attribution.');
        return self::SUCCESS;
    }
}
