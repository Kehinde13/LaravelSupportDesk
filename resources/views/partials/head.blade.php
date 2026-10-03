<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name') : config('app.name') }}
</title>

<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Crect width='24' height='24' rx='5' fill='%2318181b'/%3E%3Cpath d='M4 6h16v4a2 2 0 0 0 0 4v4H4v-4a2 2 0 0 0 0-4V6Z' fill='none' stroke='white' stroke-width='1.5'/%3E%3C/svg%3E">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
