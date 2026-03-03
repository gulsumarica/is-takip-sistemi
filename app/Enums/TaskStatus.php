<?php

namespace App\Enums;

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case TESTING = 'testing';
    case DONE = 'done';

    /**
     * Tailwind sınıfları (Kanban sütun renkleri).
     */
    public function color(): string
    {
        return match ($this) {
            self::TODO => 'bg-slate-100 text-slate-700',
            self::IN_PROGRESS => 'bg-amber-100 text-amber-700',
            self::TESTING => 'bg-purple-100 text-purple-700',
            self::DONE => 'bg-emerald-100 text-emerald-700',
        };
    }
}
