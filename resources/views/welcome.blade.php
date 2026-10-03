<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head', ['title' => __('Welcome')])
        <meta name="description" content="Create, organize, track, and resolve support requests from one focused dashboard.">
    </head>
    <body class="min-h-screen bg-white text-zinc-900 antialiased dark:bg-zinc-900 dark:text-white">
        <div class="mx-auto max-w-6xl px-6 py-6 sm:px-10">
            <header class="flex flex-wrap items-center justify-between gap-4">
                <x-app-logo :href="route('home')" />
                <nav aria-label="{{ __('Account navigation') }}" class="flex flex-wrap items-center gap-4">
                    @auth
                        <flux:button :href="route('dashboard')" variant="primary">{{ __('Dashboard') }}</flux:button>
                    @else
                        <flux:link :href="route('login')">{{ __('Sign in') }}</flux:link>
                        @if (Route::has('register'))
                            <flux:button :href="route('register')" variant="primary">{{ __('Register') }}</flux:button>
                        @endif
                    @endauth
                </nav>
            </header>
            <main class="py-16 sm:py-24">
                <section class="max-w-3xl space-y-6">
                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ config('app.name') }}</p>
                    <h1 class="text-4xl font-semibold tracking-tight sm:text-6xl">{{ __('Simple support ticket management') }}</h1>
                    <p class="max-w-2xl text-lg leading-relaxed text-zinc-600 dark:text-zinc-300">{{ __('Create, organize, track, and resolve support requests from one focused dashboard.') }}</p>
                </section>
                <section aria-label="{{ __('Features') }}" class="mt-12 grid gap-4 md:grid-cols-3">
                    @foreach ([
                        'Ticket organization' => 'Keep support requests together. Search titles and filter your tickets to find what matters.',
                        'Status and priority tracking' => 'Set priorities and follow each request from open to in progress to resolved.',
                        'Secure personal workspace' => 'Sign in to manage your own tickets in a workspace protected by ownership checks.',
                    ] as $heading => $description)
                        <article class="rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
                            <h2 class="text-lg font-semibold">{{ __($heading) }}</h2>
                            <p class="mt-3 leading-relaxed text-zinc-600 dark:text-zinc-300">{{ __($description) }}</p>
                        </article>
                    @endforeach
                </section>
            </main>
            <footer class="border-t border-zinc-200 pt-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">{{ config('app.name') }}</footer>
        </div>
        @fluxScripts
    </body>
</html>
