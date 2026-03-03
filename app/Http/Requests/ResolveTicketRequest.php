<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;

class ResolveTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');
        $user = $this->user();

        if (! $ticket instanceof Ticket || $user === null) {
            return false;
        }

        return $ticket->customer_id === $user->id;
    }

    public function rules(): array
    {
        return [];
    }
}

