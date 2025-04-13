<?php

namespace App\Http\Controllers\Pages;

use App\Trading\Champion;
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
            ]
        ]);
    }
}
