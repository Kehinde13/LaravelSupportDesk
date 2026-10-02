<?php

use App\Models\Ticket;
use App\Models\User;

it('filters owned tickets individually and in combination', function (array $filters, array $expected) {
    $user = User::factory()->create();
    $rows = [
        ['title' => 'Printer offline', 'status' => 'open', 'priority' => 'high'],
        ['title' => 'Printer paper', 'status' => 'resolved', 'priority' => 'low'],
        ['title' => 'Network issue', 'status' => 'open', 'priority' => 'low'],
    ];
    foreach ($rows as $row) {
        Ticket::factory()->for($user)->create($row + ['description' => 'Printer mentioned only in description.']);
    }
    Ticket::factory()->create($rows[0]);
    $this->actingAs($user)->get(route('tickets.index', $filters))->assertOk()
        ->assertViewHas('tickets', fn ($tickets) => $tickets->pluck('title')->sort()->values()->all() === collect($expected)->sort()->values()->all());
})->with([
    [['search' => 'Printer'], ['Printer offline', 'Printer paper']],
    [['status' => 'open'], ['Printer offline', 'Network issue']],
    [['priority' => 'high'], ['Printer offline']],
    [['search' => 'Printer', 'status' => 'open', 'priority' => 'high'], ['Printer offline']],
]);

it('rejects invalid index filters', function (string $key, mixed $value) {
    $this->actingAs(User::factory()->create())->getJson(route('tickets.index', [$key => $value]))
        ->assertUnprocessable()->assertJsonValidationErrors($key);
})->with([['status', 'closed'], ['priority', 'urgent'], ['search', str_repeat('a', 101)], ['search', ['bad']]]);

it('redirects invalid browser filters to the clean index', function () {
    $this->actingAs(User::factory()->create())->get(route('tickets.index', ['status' => 'invalid']))
        ->assertRedirect(route('tickets.index'))->assertSessionHasErrors('status');
});

it('preserves filters and newest first ordering across pagination', function () {
    $user = User::factory()->create();
    $rows = Ticket::factory()->count(11)->for($user)->create([
        'title' => 'Printer issue', 'status' => 'open', 'priority' => 'high', 'created_at' => now(),
    ]);
    $filters = ['search' => 'Printer', 'status' => 'open', 'priority' => 'high'];
    $this->actingAs($user)->get(route('tickets.index', $filters))->assertOk()
        ->assertViewHas('tickets', function ($tickets) use ($filters, $rows) {
            parse_str(parse_url($tickets->nextPageUrl(), PHP_URL_QUERY), $query);

            return $tickets->count() === 10 && $tickets->total() === 11
                && $tickets->first()->id === $rows->last()->id
                && $query === $filters + ['page' => '2'];
        });
});

it('distinguishes an empty account from no matching tickets', function () {
    $user = User::factory()->create();
    Ticket::factory()->create(['title' => 'Other user ticket']);
    $this->actingAs($user)->get(route('tickets.index', ['search' => 'Missing']))
        ->assertSee('No tickets yet')->assertDontSee('No matching tickets');
    Ticket::factory()->for($user)->create(['title' => 'Existing ticket']);
    $this->get(route('tickets.index', ['search' => 'Missing']))
        ->assertSee('No matching tickets')->assertDontSee('No tickets yet');
});

it('accepts the search length boundary and treats wildcards literally', function () {
    $user = User::factory()->create();
    Ticket::factory()->for($user)->create(['title' => '100% complete']);
    Ticket::factory()->for($user)->create(['title' => 'Other title']);
    $this->actingAs($user)->get(route('tickets.index', ['search' => '%']))->assertOk()
        ->assertViewHas('tickets', fn ($tickets) => $tickets->total() === 1);
    $this->get(route('tickets.index', ['search' => str_repeat('a', 100)]))->assertOk();
});
