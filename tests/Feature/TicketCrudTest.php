<?php

use App\Models\Ticket;
use App\Models\User;

it('allows owners to view and edit tickets', function (string $route) {
    $ticket = Ticket::factory()->create();
    $this->actingAs($ticket->user)->get(route($route, $ticket))->assertOk()->assertSee($ticket->title);
})->with(['tickets.show', 'tickets.edit']);

it('denies other users all ticket-specific actions', function (string $method, string $route) {
    $ticket = Ticket::factory()->create();
    $before = $ticket->fresh()->getAttributes();
    $this->actingAs(User::factory()->create())->{$method}(route($route, $ticket), ['title' => 'Unauthorized'])
        ->assertForbidden();
    expect($ticket->fresh()->getAttributes())->toBe($before);
})->with([['get', 'tickets.show'], ['get', 'tickets.edit'], ['patch', 'tickets.update'], ['delete', 'tickets.destroy']]);

it('updates all editable fields without changing ownership', function () {
    $ticket = Ticket::factory()->create();
    $other = User::factory()->create();
    $data = ['title' => 'Updated title', 'description' => 'Updated description', 'priority' => 'high', 'status' => 'resolved'];
    $this->actingAs($ticket->user)->patch(route('tickets.update', $ticket), $data + ['user_id' => $other->id])
        ->assertRedirect(route('tickets.show', $ticket))->assertSessionHas('success', 'Ticket updated successfully.');
    $this->assertDatabaseHas('tickets', $data + ['id' => $ticket->id, 'user_id' => $ticket->user_id]);
});

it('preserves stored data and input when updates fail validation', function () {
    $ticket = Ticket::factory()->create();
    $before = $ticket->fresh()->getAttributes();
    $this->actingAs($ticket->user)->from(route('tickets.edit', $ticket))
        ->patch(route('tickets.update', $ticket), ['title' => 'Keep input', 'description' => '', 'priority' => 'urgent', 'status' => 'invalid'])
        ->assertRedirect(route('tickets.edit', $ticket))->assertSessionHasErrors(['description', 'priority', 'status'])
        ->assertSessionHasInput('title', 'Keep input');
    expect($ticket->fresh()->getAttributes())->toBe($before);
});

it('deletes an owned ticket and redirects with success', function () {
    $ticket = Ticket::factory()->create();
    $this->actingAs($ticket->user)->delete(route('tickets.destroy', $ticket))
        ->assertRedirect(route('tickets.index'))->assertSessionHas('success', 'Ticket deleted successfully.');
    $this->assertModelMissing($ticket);
});

it('requires authentication on ticket-specific routes', function (string $method, string $route) {
    $ticket = Ticket::factory()->create();
    $this->{$method}(route($route, $ticket))->assertRedirect(route('login'));
    $this->assertModelExists($ticket);
})->with([['get', 'tickets.show'], ['get', 'tickets.edit'], ['patch', 'tickets.update'], ['delete', 'tickets.destroy']]);

it('requires verified email on ticket-specific routes', function (string $method, string $route) {
    $user = User::factory()->unverified()->create();
    $ticket = Ticket::factory()->for($user)->create();
    $this->actingAs($user)->{$method}(route($route, $ticket))->assertRedirect(route('verification.notice'));
    $this->assertModelExists($ticket);
})->with([['get', 'tickets.show'], ['get', 'tickets.edit'], ['patch', 'tickets.update'], ['delete', 'tickets.destroy']]);

it('returns not found for a missing bound ticket', function () {
    $this->actingAs(User::factory()->create())->get(route('tickets.show', 999999))->assertNotFound();
});
