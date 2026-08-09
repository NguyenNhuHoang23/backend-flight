<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            // Tên ngân hàng
            $table->string('bank_name');

            // Số tài khoản
            $table->string('account_number');

            // Tên người thụ hưởng
            $table->string('account_name');

            // Nội dung chuyển khoản
            $table->string('transfer_content')->nullable();

            // 1 = đang sử dụng, 0 = tắt
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
