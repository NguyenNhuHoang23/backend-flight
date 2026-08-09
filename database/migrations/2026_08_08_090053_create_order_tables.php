<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_code', 50)->unique();

            $table->string('status', 30)->default('pending');

            $table->dateTime('booking_at');

            // Người đặt liên hệ
            $table->string('contact_name');
            $table->string('contact_phone', 30);
            $table->string('contact_email')->nullable();

            // Thanh toán
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('payment_method', 50)->nullable();

            // Ảnh bill
            $table->string('payment_bill_image')->nullable();

            // Nội dung chuyển khoản
            $table->string('transfer_content', 500)->nullable();

            $table->timestamps();
        });

        Schema::create('order_passengers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->string('full_name');

            $table->string('passenger_type', 30)
                ->default('adult');

            $table->string('document_type', 30)
                ->nullable();

            $table->string('document_number', 100)
                ->nullable();

            $table->timestamps();
        });

        Schema::create('order_flights', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // outbound = chiều đi
            // return = chiều về
            $table->string('trip_type', 20);

            $table->string('airline_name');
            $table->string('airline_code', 10)->nullable();

            $table->string('flight_number', 30);

            $table->string('departure_airport', 10);
            $table->string('arrival_airport', 10);

            $table->dateTime('departure_at');
            $table->dateTime('arrival_at')->nullable();

            $table->timestamps();

            $table->index('trip_type');
        });
    }

    public function down(): void
    {
        // Xóa bảng con trước
        Schema::dropIfExists('order_flights');
        Schema::dropIfExists('order_passengers');

        // Sau cùng mới xóa bảng cha
        Schema::dropIfExists('orders');
    }
};
