<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating a new admin user');

        // Get admin details interactively
        $name = $this->ask('Enter full name:');
        $username = $this->ask('Enter username:');
        $password = $this->secret('Enter password:');

        try {
            $admin = User::create([
                'name' => $name,
                'username' => $username,
                'is_active' => '1',
                'booking_id' => null,
                'password' => Hash::make($password),
                'role_name' => 'admin'
            ]);

            $this->info('Admin created successfully!');
            $this->info('Name: ' . $admin->name);
            $this->info('Username: ' . $admin->username);
            $this->info('Active: ' . $admin->is_active);
            $this->info('booking_id: ' . $admin->booking_id);
            $this->info('role: ' . $admin->role_name);

            return true;
        } catch (\Exception $e) {
            $this->error('Failed to create admin user: ' . $e->getMessage());
            return false;
        }
    }
}
