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
        // Bảng lưu cấu hình chung
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Ví dụ: 'default_discount_rate'
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Bảng lưu cấu hình giảm giá theo hãng bay
        Schema::create('airline_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('airline_code')->unique(); // Mã hãng: BAMBOO, GALILEO, VNA,...
            $table->string('airline_name');            // Tên hãng: Bamboo Airways, Vietnam Airlines,...
            $table->decimal('discount_rate', 5, 2)->default(0); // % điều chỉnh
            $table->boolean('is_custom_enabled')->default(false); // Trạng thái "Bật riêng"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('airline_discounts');
        Schema::dropIfExists('settings');
    }
};