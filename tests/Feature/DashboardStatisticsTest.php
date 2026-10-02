<?php

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

it('shows zero counts and an empty state for an account without tickets', function () {
    Ticket::factory()->create();
    $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertOk()
        ->assertViewHas('counts', ['total' => 0, 'open' => 0, 'in_progress' => 0, 'resolved' => 0])
        ->assertViewHas('recentTickets', fn ($tickets) => $tickets->isEmpty())
        ->assertSee('No tickets yet');
});

it('counts only the current users tickets by status', function () {
    $user = User::factory()->create();
    foreach (['open' => 2, 'in_progress' => 3, 'resolved' => 1] as $status => $count) {
        Ticket::factory()->count($count)->for($user)->create(['status' => $status]);
        Ticket::factory()->count(2)->create(['status' => $status]);
    }
    $this->actingAs($user)->get(route('dashboard'))->assertOk()
        ->assertViewHas('counts', ['total' => 6, 'open' => 2, 'in_progress' => 3, 'resolved' => 1]);
});

it('shows only the five newest owned tickets in deterministic order', function () {
    $user = User::factory()->create();
    $tickets = collect();
    foreach (range(1, 7) as $i) {
        $tickets->push(Ticket::factory()->for($user)->create(['title' => 'Owned ticket '.$i, 'created_at' => now()->subDays(7 - $i)]));
    }
    $other = Ticket::factory()->create(['title' => 'Private other ticket']);
    $this->actingAs($user)->get(route('dashboard'))->assertOk()
        ->assertViewHas('recentTickets', fn ($recent) => $recent->modelKeys() === $tickets->reverse()->take(5)->pluck('id')->all())
        ->assertSeeInOrder(['Owned ticket 7', 'Owned ticket 6', 'Owned ticket 5', 'Owned ticket 4', 'Owned ticket 3'])
        ->assertDontSee('Owned ticket 1')->assertDontSee('Owned ticket 2')->assertDontSee($other->title);
});

it('links statistic cards to their ticket filters', function () {
    $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));
    foreach (['open', 'in_progress', 'resolved'] as $status) {
        $response->assertSee('href="'.route('tickets.index', ['status' => $status]).'"', false);
    }
    $response->assertSee('href="'.route('tickets.index').'"', false);
});

it('blocks unverified users from the dashboard', function () {
    $this->actingAs(User::factory()->unverified()->create())->get(route('dashboard'))
        ->assertRedirect(route('verification.notice'));
});

it('authorizes ticket listing before displaying the dashboard', function () {
    Gate::before(fn () => false);
    $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertForbidden();
});
