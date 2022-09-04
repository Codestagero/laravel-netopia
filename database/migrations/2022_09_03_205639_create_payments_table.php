<?php

use Codestage\Netopia\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('netopia_payments', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->string('status')->default(PaymentStatus::Pending);
            $table->decimal('amount');
            $table->string('currency', 6);
            $table->text('description')->nullable()->default(null);
            $table->nullableMorphs('billable');
            $table->json('shipping_address')->default(json_encode(null));
            $table->json('billing_address')->default(json_encode(null));
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('netopia_payments');
    }
};
