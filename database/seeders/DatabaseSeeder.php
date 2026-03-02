<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\Task;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Şirket ve Roller
        $adminRole = Role::create(['name' => 'Admin', 'level' => 1]);
        $devRole = Role::create(['name' => 'Geliştirici', 'level' => 5]);
        $company = Company::create(['name' => 'KBT Bilişim', 'is_active' => true]);

        // 2. Kullanıcı (Siz)
        $user = User::create([
            'name' => 'Gülsüm Arıca',
            'email' => 'admin@test.com',
            'password' => Hash::make('123123'),
            'role_id' => $adminRole->id,
            'company_id' => $company->id,
        ]);

        // 3. Örnek Bir Proje
        $project = Project::create([
            'name' => 'E-Ticaret Dönüşüm Projesi',
            'company_id' => $company->id,
            'is_active' => true
        ]);

        // 4. Örnek Bir Müşteri Talebi (Ticket)
        $ticket = Ticket::create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'customer_id' => $user->id,
            'title' => 'Mobil Uygulama Hataları',
            'description' => 'Müşteriler sepet ekranında donma yaşıyor.',
            'status' => 'opened'
        ]);

        // 5. Sizin Üzerinizde Görülecek Gerçek Görevler (Tasks)
        Task::create([
            'ticket_id' => $ticket->id,
            'assigned_by' => $user->id,
            'assigned_to' => $user->id,
            'title' => 'Sepet API optimizasyonu yapılacak',
            'status' => 'todo',
            'priority' => 'urgent',
            'deadline' => Carbon::now()->addDays(2),
        ]);

        Task::create([
            'ticket_id' => $ticket->id,
            'assigned_by' => $user->id,
            'assigned_to' => $user->id,
            'title' => 'Kredi kartı formuna maskeleme eklenecek',
            'status' => 'in_progress',
            'priority' => 'high',
            'deadline' => Carbon::now()->addDays(5),
        ]);

        $this->command->info('Canlı veritabanı başarıyla hazırlandı!');
    }
}