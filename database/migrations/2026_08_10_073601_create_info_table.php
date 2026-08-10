<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('info', function (Blueprint $table) {
            $table->id();

            // Kênh liên hệ trực tiếp
            $table->string('hotline')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();

            // Website & mạng xã hội
            $table->string('website')->nullable();
            $table->text('facebook')->nullable();
            $table->text('zalo')->nullable();
            $table->text('messenger')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('info');
    }
};