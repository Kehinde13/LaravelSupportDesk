<?php

use App\Models\Ticket;
use App\Models\User;
use App\TicketPriority;
use App\TicketStatus;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

// RefreshDatabase is applied to Feature tests in tests/Pest.php.

it('belongs to a user', function () {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->for($user)->create();

    expect($ticket->user->is($user))->toBeTrue();
});

it('allows a user to have multiple tickets', function () {
    $user = User::factory()->create();
    $tickets = Ticket::factory()->count(2)->for($user)->create();
    Ticket::factory()->create();

    expect($user->tickets)->toHaveCount(2);
    expect($user->tickets->modelKeys())->toEqualCanonicalizing($tickets->modelKeys());
});

it('casts priority and status to enums', function () {
    $ticket = Ticket::factory()->create([
        'priority' => 'high',
        'status' => 'in_progress',
    ])->fresh();

    expect($ticket->priority)->toBe(TicketPriority::High)
        ->and($ticket->status)->toBe(TicketStatus::InProgress);

    $ticket->update(['priority' => TicketPriority::Medium, 'status' => TicketStatus::Resolved]);

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'priority' => 'medium',
        'status' => 'resolved',
    ]);
});

it('defaults priority and status in the database', function () {
    $id = DB::table('tickets')->insertGetId([
        'user_id' => User::factory()->create()->id,
        'title' => 'Cannot sign in',
        'description' => 'The login page is unavailable.',
    ]);

    $this->assertDatabaseHas('tickets', ['id' => $id, 'priority' => 'low', 'status' => 'open']);
    $ticket = Ticket::findOrFail($id);
    expect($ticket->priority)->toBe(TicketPriority::Low)
        ->and($ticket->status)->toBe(TicketStatus::Open);
});

it('cascades user deletion to their tickets only', function () {
    $user = User::factory()->create();
    $tickets = Ticket::factory()->count(2)->for($user)->create();
    $otherTicket = Ticket::factory()->create();

    $user->delete();

    foreach ($tickets as $ticket) {
        $this->assertModelMissing($ticket);
    }
    $this->assertModelExists($otherTicket);
});

it('guards ownership while allowing relationship creation', function () {
    expect((new Ticket)->isFillable('user_id'))->toBeFalse();

    $user = User::factory()->create();
    $ticket = $user->tickets()->create([
        'title' => 'Cannot sign in',
        'description' => 'The login page is unavailable.',
        'priority' => TicketPriority::Low,
        'status' => TicketStatus::Open,
    ]);

    expect($ticket->fresh()->user->is($user))->toBeTrue();
});

it('enforces the title length when inserting into the database', function () {
    $ticket = Ticket::factory()->create(['title' => str_repeat('a', 150)]);
    expect($ticket->fresh()->title)->toHaveLength(150);

    expect(fn () => DB::table('tickets')->insert([
        'user_id' => $ticket->user_id,
        'title' => str_repeat('a', 151),
        'description' => 'Too long',
    ]))->toThrow(QueryException::class);
});

it('enforces the title length when updating the database', function () {
    $ticket = Ticket::factory()->create();

    expect(fn () => DB::table('tickets')->where('id', $ticket->id)->update([
        'title' => str_repeat('a', 151),
    ]))->toThrow(QueryException::class);
});
