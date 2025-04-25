<?php

namespace App\Http\Controllers\Pages;

use App\Trading\Champion;
use App\Trading\Order;
use Illuminate\Http\Request;
use Inertia\Response;
use Laravel\Jetstream\Jetstream;

class ChampionController
{
    public function dashboard(Request $request): Response
    {
        $champions = Champion::query()
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($champion) {
                return (object)[
                    'id' => $champion->id,
                    'remain_capital' => number_format($champion->current_capital - $champion->onduty, 2),
                    'capital' => $champion->capital,
                    'roi' => number_format($champion->roi * 100, 2),
                    'profit' => number_format($champion->profit + $champion->income - $champion->fee, 2),
                    'name' => $champion->name,
                    'archetype' => $champion->archetype,
                    'onduty' => number_format($champion->onduty, 2),
                    'age' => $champion->created_at->diffForHumans(),
                    'grind' => $champion->grind,
                    'fee' => number_format($champion->fee, 2),
                    'income' => number_format($champion->income, 2),
                    'current_capital' => number_format($champion->current_capital, 2),
                    'apy' => number_format($champion->roi / $champion->created_at->diffInHours(now()) * 876000, 2),
                    'entry' => number_format($champion->entry, 4),
                    'avatar_url' => $champion->avatar_url
                ];
            });

        return Jetstream::inertia()->render($request, 'Champion/Dashboard', [
            'champions' => $champions
        ]);
    }

    public function __invoke(Request $request, int $id): Response
    {
        $champion = Champion::query()->findOrFail($id);

        $orders = $champion->orders()
            ->where('status', Order::STATUS_FILLED)
            ->orderBy('update_time', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                $type = match (true) {
                    $order->side === 'BUY' && $order->position_side === 'LONG' => 'Open Long',
                    $order->side === 'SELL' && $order->position_side === 'LONG' => 'Close Long',
                    $order->side === 'SELL' && $order->position_side === 'SHORT' => 'Open Short',
                    default => 'Close Short'
                };

                $source = str_starts_with($order->client_order_id, 'ios') ? 'Human' : 'Bot';
                $realizedPnl = $order->trades->sum('realized_pnl');

                return (object)[
                    'id' => $order->order_id,
                    'type' => $type,
                    'source' => $source,
                    'quantity' => number_format($order->executed_qty, 0),
                    'avg_price' => number_format($order->avg_price, 5),
                    'status' => array_search($order->status, Order::STATUS),
                    'update_time' => now()->setTimestamp((int)($order->update_time / 1000))->diffForHumans(),
                    'realized_pnl' => $realizedPnl !== 0 ? number_format($realizedPnl, 2) : null
                ];
            });

        return Jetstream::inertia()->render($request, 'Champion/Show', [
            'background' => $champion->avatar_url,
            'champion' => (object)[
                'remain_capital' => number_format($champion->current_capital - $champion->onduty, 2),
                'capital' => $champion->capital,
                'roi' => number_format($champion->roi * 100, 2),
                'profit' => number_format($champion->profit, 2),
                'name' => $champion->name,
                'archetype' => $champion->archetype,
                'onduty' => number_format($champion->onduty, 2),
                'age' => $champion->created_at->diffForHumans(),
                'grind' => $champion->grind,
                'fee' => number_format($champion->fee, 2),
                'income' => number_format($champion->income, 2),
                'current_capital' => number_format($champion->current_capital, 2),
                'apy' => number_format($champion->roi / $champion->created_at->diffInHours(now()) * 876000, 2),
                'entry' => number_format($champion->entry, 4)
            ],
            'orders' => $orders
        ]);
    }
}
