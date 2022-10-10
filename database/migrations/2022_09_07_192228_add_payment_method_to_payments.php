<?php

use Codestage\Netopia\Models\PaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('netopia_payments', function (Blueprint $table): void {
            $table->string('payment_method_id', 64)
                ->after('payment_method_saved')
                ->nullable();
            $table->foreign('payment_method_id')
                ->references('id')
                ->on('netopia_payment_methods')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('netopia_payments', function (Blueprint $table): void {
            $table->dropConstrainedForeignIdFor(PaymentMethod::class, 'payment_method_id');
        });
    }
};
