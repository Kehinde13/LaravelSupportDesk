<x-layouts::app :title="__('Create ticket')">
    <div class="max-w-2xl space-y-6">
        <flux:heading size="xl" level="1">{{ __('Create ticket') }}</flux:heading>

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

        <form method="POST" action="{{ route('tickets.store') }}" class="space-y-6">
            @csrf
            <flux:input name="title" :label="__('Title')" :value="old('title')" maxlength="150" required />
            <flux:textarea name="description" :label="__('Description')" rows="6" maxlength="5000" required>{{ old('description') }}</flux:textarea>
            <flux:select name="priority" :label="__('Priority')" required>
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->value }}" @selected(old('priority', 'low') === $priority->value)>{{ ucfirst($priority->value) }}</option>
                @endforeach
            </flux:select>
            <div class="flex flex-wrap items-center gap-4">
                <flux:button type="submit" variant="primary">{{ __('Create ticket') }}</flux:button>
                <flux:link :href="route('tickets.index')">{{ __('Cancel') }}</flux:link>
            </div>
        </form>
    </div>
</x-layouts::app>
