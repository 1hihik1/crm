<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        User::query()->where('balance', '>', 0)->each(function (User $user) {
            $amount = (float) $user->balance;
            if ($amount > 0) {
                $user->depositFloat($amount, ['meta' => 'migration from users.balance']);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('balance');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('balance', 12, 2)->default(0)->after('salary');
        });
    }
};
