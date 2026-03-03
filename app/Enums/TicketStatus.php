<?php

namespace App\Enums;

enum TicketStatus: string
{
    case OPENED = 'opened';
    case IN_PROGRESS = 'in_progress';
    case WAITING_CUSTOMER = 'waiting_customer';
    case RESOLVED = 'resolved';

    /**
     * Status etiketleri için Tailwind renk sınıfları.
     */
    public function color(): string
    {
        return match ($this) {
            self::OPENED => 'bg-slate-100 text-slate-700',
            self::IN_PROGRESS => 'bg-amber-100 text-amber-700',
            self::WAITING_CUSTOMER => 'bg-purple-100 text-purple-700',
            self::RESOLVED => 'bg-emerald-100 text-emerald-700',
        };
    }
}

