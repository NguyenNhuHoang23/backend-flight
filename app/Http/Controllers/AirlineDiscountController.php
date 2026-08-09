<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\AirlineDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AirlineDiscountController extends Controller
{
    /**
     * 1. GET: Lấy toàn bộ cấu hình (Dùng để đổ dữ liệu ra giao diện)
     */
    public function index()
    {
        $defaultDiscount = Setting::where('key', 'default_discount_rate')->value('value') ?? 0;
        $airlines = AirlineDiscount::all();

        return response()->json([
            'success' => true,
            'data' => [
                'default_discount_rate' => (float) $defaultDiscount,
                'airlines' => $airlines
            ]
        ]);
    }

    /**
     * 2. POST/PUT: Lưu toàn bộ cấu hình (Khớp với nút "LƯU CẤU HÌNH GIẢM GIÁ" ở đáy trang)
     */
    public function saveAll(Request $request)
    {
        $request->validate([
            'default_discount_rate' => 'required|numeric|min:0|max:100',
            'airlines' => 'required|array',
            'airlines.*.airline_code' => 'required|string',
            'airlines.*.airline_name' => 'required|string',
            'airlines.*.discount_rate' => 'required|numeric|min:0|max:100',
            'airlines.*.is_custom_enabled' => 'required|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Cập nhật cấu hình mặc định chung
            Setting::updateOrCreate(
                ['key' => 'default_discount_rate'],
                ['value' => $request->default_discount_rate]
            );

            // Cập nhật danh sách hãng bay (Upsert từng dòng theo airline_code)
            foreach ($request->airlines as $item) {
                AirlineDiscount::updateOrCreate(
                    ['airline_code' => $item['airline_code']],
                    [
                        'airline_name' => $item['airline_name'],
                        'discount_rate' => $item['discount_rate'],
                        'is_custom_enabled' => $item['is_custom_enabled'],
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lưu cấu hình giảm giá thành công!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 3. PUT: Cập nhật nhanh cấu hình mặc định chung (Nút "Áp dụng X% cho toàn bộ hãng")
     */
    public function updateDefault(Request $request)
    {
        $request->validate([
            'default_discount_rate' => 'required|numeric|min:0|max:100'
        ]);

        $setting = Setting::updateOrCreate(
            ['key' => 'default_discount_rate'],
            ['value' => $request->default_discount_rate]
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật mức giảm giá mặc định chung!',
            'data' => $setting
        ]);
    }

    /**
     * 4. POST: Khôi phục ban đầu (Nút "Khôi phục ban đầu" ở góc trên bên phải)
     */
    public function restoreDefault()
    {
        DB::beginTransaction();
        try {
            // Đặt lại mặc định về 0 hoặc một giá trị mặc định hệ thống
            Setting::updateOrCreate(
                ['key' => 'default_discount_rate'],
                ['value' => 10] // Mặc định ban đầu ví dụ là 10%
            );

            // Reset trạng thái các hãng bay về tắt riêng và mức mặc định
            AirlineDiscount::query()->update([
                'discount_rate' => 10,
                'is_custom_enabled' => false
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã khôi phục cấu hình ban đầu thành công!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Khôi phục thất bại: ' . $e->getMessage()
            ], 500);
        }
    }
}