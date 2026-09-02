<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Creates one default back-office admin so /login works out of the
     * box, and prints its credentials to the console. Change the
     * password immediately in a real deployment (or delete this seeder
     * and create admins via `php artisan tinker` instead).
     */
    public function run(): void
    {
        $email = "admin@example.com";
        $password = Str::password(14);

        $admin = Admin::firstOrCreate(
            ["email" => $email],
            [
                "name" => "Admin",
                "password" => Hash::make($password),
            ]
        );

        if ($admin->wasRecentlyCreated) {
            $this->command->info("Admin created | email: {$email} | password: {$password}");
        } else {
            $this->command->info("Admin already exists | email: {$email} (password unchanged)");
        }
    }
}
