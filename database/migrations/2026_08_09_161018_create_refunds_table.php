<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();

            // Người yêu cầu hoàn tiền
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Thông tin ngân hàng
            $table->string('bank_name');
            $table->string('account_holder');
            $table->string('account_number');

            // Số tiền hoàn
            $table->decimal('amount', 15, 2);

            // Ngày và giờ yêu cầu / xử lý
            $table->date('date')->nullable();
            $table->time('time')->nullable();

            // AM / PM
            $table->enum('ampm', ['AM', 'PM'])->nullable();

            // Ghi chú của khách hàng
            $table->text('note')->nullable();

            // Trạng thái
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');

            // Ghi chú của admin nếu cần
            $table->text('admin_note')->nullable();

            // Thời gian duyệt / từ chối
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
