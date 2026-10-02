<?php

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;
use Illuminate\Validation\ValidationException;

function ticketFormRequest(string $class, array $data, ?User $user, ?Ticket $ticket = null): FormRequest
{
    $request = $class::create('/tickets', 'POST', $data);
    $request->setContainer(app());
    $request->setRedirector(app(Redirector::class));
    $request->setUserResolver(fn () => $user);
    $route = new Route('POST', 'tickets', fn () => null);
    $route->bind($request);
    $route->setParameter('ticket', $ticket);
    $request->setRouteResolver(fn () => $route);

    return $request;
}

it('accepts store data and excludes client ownership and status', function () {
    $data = ['title' => str_repeat('a', 150), 'description' => str_repeat('b', 5000), 'priority' => 'medium'];
    $request = ticketFormRequest(StoreTicketRequest::class, $data + [
        'user_id' => 999,
        'status' => 'resolved',
    ], User::factory()->create());
    $request->validateResolved();

    expect($request->validated())->toBe($data);
});

it('rejects unauthenticated store requests', function () {
    $request = ticketFormRequest(StoreTicketRequest::class, [], null);

    expect(fn () => $request->validateResolved())->toThrow(AuthorizationException::class);
});

it('rejects invalid store fields', function (string $field, mixed $value) {
    $data = ['title' => 'Help', 'description' => 'Please help.', 'priority' => 'low'];
    $data[$field] = $value;
    $request = ticketFormRequest(StoreTicketRequest::class, $data, User::factory()->create());

    try {
        $request->validateResolved();
        $this->fail('Expected validation failure.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey($field);
    }
})->with([
    ['priority', 'urgent'],
    ['title', str_repeat('a', 151)],
    ['description', str_repeat('a', 5001)],
    ['title', ''],
    ['description', ''],
    ['priority', null],
]);

it('accepts partial updates and valid enum values', function (array $data) {
    $ticket = Ticket::factory()->create();
    $request = ticketFormRequest(UpdateTicketRequest::class, $data + ['user_id' => 999], $ticket->user, $ticket);
    $request->validateResolved();

    expect($request->validated())->toBe($data);
})->with([
    [[]],
    [['priority' => 'low', 'status' => 'open']],
    [['priority' => 'medium', 'status' => 'in_progress']],
    [['priority' => 'high', 'status' => 'resolved']],
    [['title' => str_repeat('a', 150), 'description' => str_repeat('b', 5000)]],
]);

it('rejects invalid update fields', function (string $field, mixed $value) {
    $ticket = Ticket::factory()->create();
    $request = ticketFormRequest(UpdateTicketRequest::class, [$field => $value], $ticket->user, $ticket);

    try {
        $request->validateResolved();
        $this->fail('Expected validation failure.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey($field);
    }
})->with([
    ['priority', 'urgent'],
    ['status', 'closed'],
    ['title', ''],
    ['description', null],
    ['priority', null],
    ['status', ''],
    ['title', str_repeat('a', 151)],
    ['description', str_repeat('a', 5001)],
]);

it('rejects updates by another user or guest', function (bool $guest) {
    $ticket = Ticket::factory()->create();
    $user = $guest ? null : User::factory()->create();
    $request = ticketFormRequest(UpdateTicketRequest::class, ['status' => 'resolved'], $user, $ticket);

    expect(fn () => $request->validateResolved())->toThrow(AuthorizationException::class);
})->with([false, true]);

it('rejects updates without a route-bound ticket', function () {
    $request = ticketFormRequest(UpdateTicketRequest::class, [], User::factory()->create());

    expect(fn () => $request->validateResolved())->toThrow(AuthorizationException::class);
});
