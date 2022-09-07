<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    public function up(): void
    {
        Schema::table('netopia_payments', function (Blueprint $table): void {
            $table->json('metadata')
                ->after('billing_address')
                ->nullable();
        });
        DB::table('netopia_payments')->whereNull('metadata')->update([
            'metadata' => json_encode('null')
        ]);
        Schema::table('netopia_payments', function (Blueprint $table): void {
            $table->json('metadata')
                ->nullable(false)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('netopia_payments', function (Blueprint $table): void {
            $table->dropColumn('metadata');
        });
    }
};
