<x-layouts::app :title="__('Tickets')">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <flux:heading size="xl" level="1">{{ __('Tickets') }}</flux:heading>
            <flux:button variant="primary" :href="route('tickets.create')">{{ __('Create ticket') }}</flux:button>
        </div>

        @if (session('success'))
            <div role="status" class="rounded-lg border border-green-600 bg-green-50 p-4 text-green-800 dark:bg-green-950 dark:text-green-200">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div role="alert" class="rounded-lg border border-red-600 p-4 text-red-700 dark:text-red-300">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="GET" action="{{ route('tickets.index') }}" class="grid items-end gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <flux:input name="search" :label="__('Search titles')" :value="$filters['search'] ?? ''" maxlength="100" />
            <flux:select name="status" :label="__('Status')">
                <option value="">{{ __('All statuses') }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ ucfirst(str_replace('_', ' ', $status->value)) }}</option>
                @endforeach
            </flux:select>
            <flux:select name="priority" :label="__('Priority')">
                <option value="">{{ __('All priorities') }}</option>
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->value }}" @selected(($filters['priority'] ?? '') === $priority->value)>{{ ucfirst($priority->value) }}</option>
                @endforeach
            </flux:select>
            <div class="flex items-center gap-3">
                <flux:button type="submit" variant="primary">{{ __('Apply') }}</flux:button>
                <flux:link :href="route('tickets.index')">{{ __('Clear filters') }}</flux:link>
            </div>
        </form>

        @if ($filters)
            <flux:text class="break-words">
                {{ __('Active filters:') }}
                {{ collect($filters)->map(fn ($value, $key) => ucfirst($key).': '.str_replace('_', ' ', $value))->implode(' ? ') }}
            </flux:text>
        @endif

        @if ($tickets->isEmpty())
            <div class="rounded-xl border border-zinc-200 p-8 text-center dark:border-zinc-700">
                <flux:heading level="2">{{ $hasTickets ? __('No matching tickets') : __('No tickets yet') }}</flux:heading>
                <flux:text class="mt-2">{{ $hasTickets ? __('Try different filters or clear them to see your tickets.') : __('Create your first ticket to get started.') }}</flux:text>
            </div>
        @else
            <ul class="space-y-3" aria-label="{{ __('Your tickets') }}">
                @foreach ($tickets as $ticket)
                    <li class="flex min-w-0 flex-col gap-3 rounded-xl border border-zinc-200 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-700">
                        <div class="min-w-0">
                            <flux:heading level="2" class="break-words"><flux:link :href="route('tickets.show', $ticket)">{{ $ticket->title }}</flux:link></flux:heading>
                            <flux:text class="mt-1">{{ __('Created') }} <time datetime="{{ $ticket->created_at->toIso8601String() }}">{{ $ticket->created_at->format('M j, Y') }}</time></flux:text>
                        </div>
                        <div class="flex shrink-0 flex-wrap gap-2">
                            <flux:badge>{{ __('Priority:') }} {{ ucfirst($ticket->priority->value) }}</flux:badge>
                            <flux:badge>{{ __('Status:') }} {{ ucfirst(str_replace('_', ' ', $ticket->status->value)) }}</flux:badge>
                            <flux:link :href="route('tickets.edit', $ticket)" :aria-label="__('Edit ticket: :title', ['title' => $ticket->title])">{{ __('Edit') }}</flux:link>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        {{ $tickets->links() }}
    </div>
</x-layouts::app>
