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
        
            $table->string('hotline')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
        
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