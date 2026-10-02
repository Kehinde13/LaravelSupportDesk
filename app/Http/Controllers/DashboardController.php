<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\TicketStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        Gate::authorize('viewAny', Ticket::class);

        $statusCounts = $request->user()->tickets()
            ->select('status')->selectRaw('COUNT(*) as aggregate')
            ->groupBy('status')->toBase()->pluck('aggregate', 'status');

        $counts = ['total' => (int) $statusCounts->sum()];
        foreach (TicketStatus::cases() as $status) {
            $counts[$status->value] = (int) $statusCounts->get($status->value, 0);
        }

        $recentTickets = $request->user()->tickets()
            ->latest()->orderByDesc('id')->limit(5)->get();

        return view('dashboard', compact('counts', 'recentTickets'));
    }
}
