<?php

namespace App\Http\Controllers;

use App\Actions\Ticket\CreateCommentAction;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketCommentController extends Controller
{
    public function store(Request $request, Ticket $ticket, CreateCommentAction $createCommentAction): RedirectResponse
    {
        $user = Auth::user();

        // Müşteri kendi talebine yazabilir, ekip üyeleri ise projede yetkili olmalı (ileride genişletilebilir).
        if ($ticket->customer_id !== $user->id && ! $user->projects()->where('projects.id', $ticket->project_id)->exists()) {
            abort(403, 'Bu talep üzerinde yorum yapma yetkiniz yok.');
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'is_internal' => ['sometimes', 'boolean'],
            'attachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $createCommentAction->execute($ticket, $user, [
            'body' => $validated['body'],
            'is_internal' => $validated['is_internal'] ?? false,
            'attachment' => $request->file('attachment'),
        ]);

        return back()->with('success', 'Mesajınız başarıyla iletildi.');
    }
}

