<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('netopia_payments', function (Blueprint $table): void {
            $table->boolean('payment_method_saved')
                ->after('metadata')
                ->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('netopia_payments', function (Blueprint $table): void {
            $table->dropColumn(['payment_method_saved']);
        });
    }
};
