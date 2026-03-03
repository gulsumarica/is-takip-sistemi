<?php

namespace App\Http\Controllers;

use App\Actions\Ticket\CreateTicketAction;
use App\Actions\Ticket\ResolveTicketAction;
use App\Enums\TicketStatus;
use App\Http\Requests\ResolveTicketRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketCsatRequest;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function store(StoreTicketRequest $request, CreateTicketAction $createTicketAction): RedirectResponse
    {
        $validatedData = $request->validated();
        $createTicketAction->execute($validatedData, auth()->user());

        return back()->with('success', 'Talebiniz başarıyla oluşturuldu ve ekibe iletildi!');
    }

    public function show(Ticket $ticket): Response
    {
        if ($ticket->customer_id !== auth()->id() && ! auth()->user()->projects()->where('projects.id', $ticket->project_id)->exists()) {
            abort(403, 'Bu talebi görüntüleme yetkiniz yok.');
        }

        $ticket->load([
            'project:id,name',
            'media',
            'tasks',
            'comments' => fn ($q) => $q
                ->where('is_internal', false)
                ->with(['user:id,name', 'media'])
                ->oldest(),
        ]);
        $ticket->append(['first_media_url', 'attachment_url']);

        return Inertia::render('Customer/TicketShow', [
            'ticket' => $ticket->toArray(),
        ]);
    }

    public function resolve(ResolveTicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $ticket->update([
            'status' => TicketStatus::RESOLVED,
            'resolved_at' => now(),
        ]);

        return back()->with('success', 'Talep çözüldü olarak işaretlendi.');
    }

    public function csat(UpdateTicketCsatRequest $request, Ticket $ticket, ResolveTicketAction $resolveTicketAction): RedirectResponse
    {
        $validated = $request->validated();

        $resolveTicketAction->execute($ticket, $validated['csat_score']);

        return back()->with('success', 'Geri bildiriminiz için teşekkürler! Değerlendirmeniz kaydedildi.');
    }
}