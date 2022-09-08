<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('netopia_token')
                    ->nullable()
                    ->default(null);
                $table->timestamp('netopia_token_expires_at')
                    ->nullable()
                    ->default(null);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn([
                    'netopia_token',
                    'netopia_token_expires_at'
                ]);
            });
        }
    }
};
