<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexTicketRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Ticket;
use App\TicketPriority;
use App\TicketStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(IndexTicketRequest $request): View
    {
        // IndexTicketRequest authorizes the policy's viewAny action.
        $filters = array_filter($request->validated(), fn ($value) => $value !== null && $value !== '');
        $query = $request->user()->tickets();

        if (isset($filters['search'])) {
            // Treat SQL wildcard characters as literal search text.
            $search = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['search']);
            $query->whereRaw("title LIKE ? ESCAPE '!'", ['%'.$search.'%']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        $tickets = $query->latest()->orderByDesc('id')->paginate(10)->appends($filters);
        $hasTickets = $tickets->total() > 0 || $request->user()->tickets()->exists();

        return view('tickets.index', [
            'tickets' => $tickets,
            'filters' => $filters,
            'hasTickets' => $hasTickets,
            'statuses' => TicketStatus::cases(),
            'priorities' => TicketPriority::cases(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Ticket::class);

        return view('tickets.create', ['priorities' => TicketPriority::cases()]);
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        // StoreTicketRequest authorizes the policy's create action.
        $request->user()->tickets()->create($request->validated());

        return to_route('tickets.index')->with('success', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket): View
    {
        Gate::authorize('view', $ticket);

        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket): View
    {
        Gate::authorize('update', $ticket);

        return view('tickets.edit', [
            'ticket' => $ticket,
            'priorities' => TicketPriority::cases(),
            'statuses' => TicketStatus::cases(),
        ]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        // UpdateTicketRequest authorizes the route-bound ticket via its policy.
        $ticket->update($request->validated());

        return to_route('tickets.show', $ticket)->with('success', 'Ticket updated successfully.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        Gate::authorize('delete', $ticket);
        $ticket->delete();

        return to_route('tickets.index')->with('success', 'Ticket deleted successfully.');
    }
}
