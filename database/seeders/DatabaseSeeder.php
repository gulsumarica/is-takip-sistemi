<?php

namespace Database\Seeders;

use App\Enums\TicketStatus;
use App\Enums\TaskStatus;
use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Şirket ve Roller
        $adminRole = Role::create(['name' => 'Admin', 'level' => 1]);
        $devRole = Role::create(['name' => 'Geliştirici', 'level' => 5]);
        $customerRole = Role::create(['name' => 'Müşteri', 'level' => 10]);
        $company = Company::create(['name' => 'KBT Bilişim', 'is_active' => true]);

        // 2. Kullanıcı (Siz - Admin)
        $user = User::create([
            'name' => 'Gülsüm Arıca',
            'email' => 'admin@test.com',
            'password' => Hash::make('123123'),
            'role_id' => $adminRole->id,
            'company_id' => $company->id,
        ]);

        // 2.b Müşteri Kullanıcısı
        $customerUser = User::create([
            'name' => 'Müşteri Kullanıcısı',
            'email' => 'customer@test.com',
            'password' => Hash::make('123123'),
            'role_id' => $customerRole->id,
            'company_id' => $company->id,
        ]);

        // 3. Örnek Bir Proje
        $project = Project::create([
            'name' => 'E-Ticaret Dönüşüm Projesi',
            'company_id' => $company->id,
            'is_active' => true
        ]);

        // 3.b Müşteriye Proje Ataması (pivot: project_user)
        $project->users()->attach($customerUser->id);

        // 4. Örnek Bir Müşteri Talebi (Ticket)
        $ticket = Ticket::create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'customer_id' => $user->id,
            'title' => 'Mobil Uygulama Hataları',
            'description' => 'Müşteriler sepet ekranında donma yaşıyor.',
            'status' => TicketStatus::OPENED,
        ]);

        // 5. Sizin Üzerinizde Görülecek Gerçek Görevler (Tasks)
        Task::create([
            'ticket_id' => $ticket->id,
            'assigned_by' => $user->id,
            'assigned_to' => $user->id,
            'title' => 'Sepet API optimizasyonu yapılacak',
            'status' => TaskStatus::TODO,
            'priority' => 'urgent',
            'deadline' => Carbon::now()->addDays(2),
        ]);

        Task::create([
            'ticket_id' => $ticket->id,
            'assigned_by' => $user->id,
            'assigned_to' => $user->id,
            'title' => 'Kredi kartı formuna maskeleme eklenecek',
            'status' => TaskStatus::IN_PROGRESS,
            'priority' => 'high',
            'deadline' => Carbon::now()->addDays(5),
        ]);

        $this->command->info('Canlı veritabanı başarıyla hazırlandı!');
    }
}