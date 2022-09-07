<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('netopia_payment_methods', function (Blueprint $table): void {
            $table->string('id', 64)->primary();
            $table->string('masked_number');
            $table->string('token_id');
            $table->morphs('billable');
            $table->timestamp('token_expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('netopia_payment_methods');
    }
};
