<?php

use Codestage\Netopia\Models\{Payment, PaymentCard};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('netopia_payment_cards', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Payment::class)
                ->unique()
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('masked_number')->nullable()->default(null);
            $table->string('token_id')->nullable()->default(null);
            $table->timestamp('token_expires_at')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::table('netopia_payments', function (Blueprint $table): void {
            $table->foreignIdFor(PaymentCard::class, 'card_details_id')
                ->nullable()
                ->default(null)
                ->constrained('netopia_payment_cards')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('netopia_payments', function (Blueprint $table): void {
            $table->dropConstrainedForeignIdFor(PaymentCard::class, 'card_details_id');
        });

        Schema::dropIfExists('netopia_payment_cards');
    }
};
