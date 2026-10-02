<?php

use App\Models\Ticket;
use App\Models\User;
use App\TicketPriority;
use App\TicketStatus;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Hash;

it('creates a verified demo user with the documented local password', function () {
    $this->seed(DemoSeeder::class);
    $user = User::where('email', 'demo@supportdesk.test')->sole();
    expect($user->name)->toBe('Demo User')
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and(Hash::check('password', $user->password))->toBeTrue();
});

it('creates eight owned tickets covering every status and priority with varied dates', function () {
    $this->seed(DemoSeeder::class);
    $user = User::where('email', 'demo@supportdesk.test')->sole();
    $tickets = $user->tickets()->get();
    expect($tickets)->toHaveCount(8)
        ->and(Ticket::count())->toBe(8)
        ->and($tickets->pluck('user_id')->unique()->all())->toBe([$user->id])
        ->and($tickets->pluck('priority')->unique()->values()->all())->toEqualCanonicalizing(TicketPriority::cases())
        ->and($tickets->pluck('status')->unique()->values()->all())->toEqualCanonicalizing(TicketStatus::cases())
        ->and($tickets->pluck('created_at')->unique())->toHaveCount(8);
});

it('does not duplicate demo records on repeated runs', function () {
    $this->seed(DemoSeeder::class);
    $before = Ticket::orderBy('id')->get()->map->getAttributes()->all();
    $this->travel(1)->days();
    $this->seed(DemoSeeder::class);
    expect(User::count())->toBe(1)->and(Ticket::count())->toBe(8)
        ->and(Ticket::orderBy('id')->get()->map->getAttributes()->all())->toBe($before);
});

it('preserves unrelated users and tickets even with matching demo titles', function () {
    $other = User::factory()->create();
    $ticket = Ticket::factory()->for($other)->create(['title' => 'VPN connection fails after password reset']);
    $userBefore = $other->fresh()->getAttributes();
    $ticketBefore = $ticket->fresh()->getAttributes();
    $this->seed(DemoSeeder::class);
    $this->seed(DemoSeeder::class);
    expect($other->fresh()->getAttributes())->toBe($userBefore)
        ->and($ticket->fresh()->getAttributes())->toBe($ticketBefore)
        ->and(User::count())->toBe(2)->and(Ticket::count())->toBe(9);
});

it('refuses to seed public demo credentials in production', function () {
    app()->instance('env', 'production');
    try {
        expect(fn () => $this->seed(DemoSeeder::class))->toThrow(LogicException::class);
        expect(User::count())->toBe(0)->and(Ticket::count())->toBe(0);
    } finally {
        app()->instance('env', 'testing');
    }
});
