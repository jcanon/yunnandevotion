<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateFirstAdmin extends Command
{
    protected $signature = 'yda:first-admin {email : An existing, verified account email}';

    protected $description = 'Promote a verified account only when no active verified administrator exists';

    public function handle(): int
    {
        return DB::transaction(function () {
            $users = User::orderBy('id')->lockForUpdate()->get();
            if ($users->contains(fn ($user) => $user->active && $user->hasVerifiedEmail() && $user->role === 'admin')) {
                $this->error('An administrator already exists. Use the admin interface.');

                return self::FAILURE;
            }
            $user = $users->firstWhere('email', strtolower(trim($this->argument('email'))));
            if (! $user || ! $user->active || ! $user->hasVerifiedEmail()) {
                $this->error('Register and verify an active account first.');

                return self::FAILURE;
            }
            $user->forceFill(['role' => 'admin'])->save();
            $this->info('First administrator configured.');

            return self::SUCCESS;
        });
    }
}
