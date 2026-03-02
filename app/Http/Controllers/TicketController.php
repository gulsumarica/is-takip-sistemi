<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest; [cite: 131]
use App\Actions\Ticket\CreateTicketAction; [cite: 132]
use Illuminate\Http\RedirectResponse; [cite: 133]
use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Actions\Ticket\ResolveTicketAction;

class TicketController extends Controller [cite: 134]
{
    public function store(StoreTicketRequest $request, CreateTicketAction $createTicketAction) [cite: 136]
    {
        // 1. Validasyondan ve Yetki kontrolünden geçen temiz veriyi al [cite: 138]
        $validatedData = $request->validated(); [cite: 139]

        // 2. Asıl işi Action sınıfına devret [cite: 140]
        $createTicketAction->execute($validatedData, auth()->user()); [cite: 141]

        // 3. Kullanıcıyı geri yönlendir ve Vue tarafında toast çıkartacak mesajı flashla [cite: 142]
        return back()->with('success', 'Talebiniz başarıyla oluşturuldu ve ekibe iletildi!'); [cite: 143]
    }

    public function resolve(Request $request, Ticket $ticket, ResolveTicketAction $resolveTicketAction)
    {
        // 1. Güvenlik ve Doğrulama: Puan 1 ile 5 arasında bir tam sayı olmalı. (Boş da geçilebilir)
        $validatedData = $request->validate([
            'csat_score' => ['nullable', 'integer', 'min:1', 'max:5']
        ]);

        // 2. Yetki Kontrolü: Sadece talebi açan müşteri (veya projede yetkili biri) kapatabilir.
        if ($ticket->customer_id !== auth()->id()) {
            abort(403, 'Bu talebi kapatma yetkiniz bulunmuyor.');
        }

        // 3. İşi Action Sınıfına Devret
        $resolveTicketAction->execute($ticket, $validatedData['csat_score'] ?? null);

        // 4. Müşteriye teşekkür et
        return back()->with('success', 'Geri bildiriminiz için teşekkürler! Talep başarıyla kapatıldı.');
    }
}