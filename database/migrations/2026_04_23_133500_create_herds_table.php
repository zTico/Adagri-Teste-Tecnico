<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('herds', function (Blueprint $table): void {
            $table->id();
            $table->string('species');
            $table->unsignedInteger('quantity');
            $table->string('purpose');
            $table->foreignId('farm_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('herds');
    }
};
