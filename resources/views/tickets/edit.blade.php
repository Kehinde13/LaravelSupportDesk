<x-layouts::app :title="__('Edit ticket')">
    <div class="max-w-2xl space-y-6">
        <flux:heading size="xl" level="1">{{ __('Edit ticket') }}</flux:heading>

        @if ($errors->any())
            <div role="alert" class="rounded-lg border border-red-600 p-4 text-red-700 dark:text-red-300">
                <p>{{ __('Please correct the errors below.') }}</p>
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('tickets.update', $ticket) }}" class="space-y-6">
            @csrf
            @method('PATCH')
            <flux:input name="title" :label="__('Title')" :value="old('title', $ticket->title)" maxlength="150" required />
            <flux:textarea name="description" :label="__('Description')" rows="6" maxlength="5000" required>{{ old('description', $ticket->description) }}</flux:textarea>
            <flux:select name="priority" :label="__('Priority')" required>
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->value }}" @selected(old('priority', $ticket->priority->value) === $priority->value)>{{ ucfirst($priority->value) }}</option>
                @endforeach
            </flux:select>
            <flux:select name="status" :label="__('Status')" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $ticket->status->value) === $status->value)>{{ ucfirst(str_replace('_', ' ', $status->value)) }}</option>
                @endforeach
            </flux:select>
            <div class="flex flex-wrap items-center gap-4">
                <flux:button type="submit" variant="primary">{{ __('Save changes') }}</flux:button>
                <flux:link :href="route('tickets.show', $ticket)">{{ __('Cancel') }}</flux:link>
            </div>
        </form>
    </div>
</x-layouts::app>
