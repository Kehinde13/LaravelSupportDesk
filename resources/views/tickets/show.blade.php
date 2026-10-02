<x-layouts::app :title="$ticket->title">
    <div class="max-w-3xl space-y-6">
        <flux:link :href="route('tickets.index')">{{ __('Back to tickets') }}</flux:link>
        <flux:heading size="xl" level="1" class="break-words">{{ $ticket->title }}</flux:heading>

        @if (session('success'))
            <div role="status" class="rounded-lg border border-green-600 bg-green-50 p-4 text-green-800 dark:bg-green-950 dark:text-green-200">{{ session('success') }}</div>
        @endif

        <div class="flex flex-wrap gap-2">
            <flux:badge>{{ __('Priority:') }} {{ ucfirst($ticket->priority->value) }}</flux:badge>
            <flux:badge>{{ __('Status:') }} {{ ucfirst(str_replace('_', ' ', $ticket->status->value)) }}</flux:badge>
        </div>
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading level="2">{{ __('Description') }}</flux:heading>
            <p class="mt-2 whitespace-pre-wrap break-words text-zinc-700 dark:text-zinc-200">{{ $ticket->description }}</p>
        </div>
        <dl class="grid gap-4 text-sm sm:grid-cols-2">
            <div><dt class="font-medium">{{ __('Created') }}</dt><dd><time datetime="{{ $ticket->created_at->toIso8601String() }}">{{ $ticket->created_at->format('M j, Y H:i') }}</time></dd></div>
            <div><dt class="font-medium">{{ __('Last updated') }}</dt><dd><time datetime="{{ $ticket->updated_at->toIso8601String() }}">{{ $ticket->updated_at->format('M j, Y H:i') }}</time></dd></div>
        </dl>
        <div class="flex flex-wrap items-center gap-4">
            <flux:button :href="route('tickets.edit', $ticket)">{{ __('Edit ticket') }}</flux:button>
            <form method="POST" action="{{ route('tickets.destroy', $ticket) }}" onsubmit="return confirm('Delete this ticket permanently? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <flux:button type="submit" variant="danger">{{ __('Delete ticket') }}</flux:button>
            </form>
        </div>
    </div>
</x-layouts::app>
