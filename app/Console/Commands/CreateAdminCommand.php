<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stic:create-admin {--email= : The email for the admin user} {--name= : The name of the admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Securely create a production Administrator account for STIC Info';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('==========================================');
        $this->info('  STIC Info — Secure Admin Creation Tool  ');
        $this->info('==========================================');

        $name = $this->option('name') ?: $this->ask('Enter Administrator Full Name (e.g. Admin STIC)');
        $email = $this->option('email') ?: $this->ask('Enter Administrator Email Address');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $password = $this->secret('Enter Secure Password (min 8 characters)');
        $passwordConfirmation = $this->secret('Confirm Secure Password');

        if ($password !== $passwordConfirmation) {
            $this->error('Passwords do not match.');
            return self::FAILURE;
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters long.');
            return self::FAILURE;
        }

        $admin = new User([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);
        $admin->role = 'admin';
        $admin->status = 'active';
        $admin->approved_at = now();
        $admin->save();

        $this->newLine();
        $this->info('✅ Administrator account created successfully!');
        $this->table(['Name', 'Email', 'Role', 'Status'], [
            [$admin->name, $admin->email, $admin->role, $admin->status],
        ]);

        return self::SUCCESS;
    }
}

