<?php

use Codestage\Netopia\Models\PaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('netopia_payments', function (Blueprint $table): void {
            $table->foreignIdFor(PaymentMethod::class, 'payment_method_id')
                ->after('payment_method_saved')
                ->nullable()
                ->constrained('netopia_payment_methods')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('netopia_payments', function (Blueprint $table): void {
            $table->dropConstrainedForeignIdFor(PaymentMethod::class, 'payment_method_id');
        });
    }
};