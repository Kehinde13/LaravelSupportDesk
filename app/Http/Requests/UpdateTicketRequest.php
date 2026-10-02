<?php

namespace App\Http\Requests;

use App\Models\Ticket;
use App\TicketPriority;
use App\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket
            && ($this->user()?->can('update', $ticket) ?? false);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:150'],
            'description' => ['sometimes', 'required', 'string', 'max:5000'],
            'priority' => ['sometimes', 'required', Rule::enum(TicketPriority::class)],
            'status' => ['sometimes', 'required', Rule::enum(TicketStatus::class)],
            'user_id' => ['exclude'],
        ];
    }
}
