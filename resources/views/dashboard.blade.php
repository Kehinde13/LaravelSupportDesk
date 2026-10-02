<x-layouts::app :title="__('Dashboard')">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <flux:heading size="xl" level="1">{{ __('Dashboard') }}</flux:heading>
            <flux:button variant="primary" :href="route('tickets.create')">{{ __('Create ticket') }}</flux:button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="{{ __('Ticket statistics') }}">
            @foreach (['total' => 'Total tickets', 'open' => 'Open tickets', 'in_progress' => 'In-progress tickets', 'resolved' => 'Resolved tickets'] as $key => $label)
                <a href="{{ route('tickets.index', $key === 'total' ? [] : ['status' => $key]) }}" class="rounded-xl border border-zinc-200 p-5 text-zinc-900 hover:bg-zinc-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-700">
                    <span class="block text-sm font-medium">{{ __($label) }}</span>
                    <span class="mt-2 block text-3xl font-semibold">{{ $counts[$key] }}</span>
                </a>
            @endforeach
        </div>

        <section aria-labelledby="recent-tickets-heading" class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <flux:heading level="2" size="lg" id="recent-tickets-heading">{{ __('Recent tickets') }}</flux:heading>
                <flux:link :href="route('tickets.index')">{{ __('View all tickets') }}</flux:link>
            </div>
            @if ($recentTickets->isEmpty())
                <div class="rounded-xl border border-zinc-200 p-8 text-center dark:border-zinc-700">
                    <flux:heading level="3">{{ __('No tickets yet') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Create your first ticket to track an issue and see its progress here.') }}</flux:text>
                </div>
            @else
                <ul class="space-y-3" aria-label="{{ __('Recent tickets') }}">
                    @foreach ($recentTickets as $ticket)
                        <li class="flex min-w-0 flex-col gap-3 rounded-xl border border-zinc-200 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-700">
                            <div class="min-w-0">
                                <flux:heading level="3" class="break-words"><flux:link :href="route('tickets.show', $ticket)">{{ $ticket->title }}</flux:link></flux:heading>
                                <flux:text class="mt-1">{{ __('Created') }} <time datetime="{{ $ticket->created_at->toIso8601String() }}">{{ $ticket->created_at->format('M j, Y') }}</time></flux:text>
                            </div>
                            <div class="flex shrink-0 flex-wrap gap-2">
                                <flux:badge>{{ __('Priority:') }} {{ ucfirst($ticket->priority->value) }}</flux:badge>
                                <flux:badge>{{ __('Status:') }} {{ ucfirst(str_replace('_', ' ', $ticket->status->value)) }}</flux:badge>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-layouts::app>
