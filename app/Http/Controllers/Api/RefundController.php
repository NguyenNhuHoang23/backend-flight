<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    /**
     * Danh sách tất cả refund
     * Admin sử dụng
     */
    public function index(Request $request)
    {
        try {
            $refunds = Refund::with('user')
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách refund thành công',
                'data' => $refunds,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách refund',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tạo yêu cầu refund
     * Client sử dụng
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => [
                'required',
                'string',
                'max:255',
            ],

            'account_holder' => [
                'required',
                'string',
                'max:255',
            ],

            'account_number' => [
                'required',
                'string',
                'max:100',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0',
            ],

            'date' => [
                'nullable',
                'date',
            ],

            'time' => [
                'nullable',
                'date_format:H:i',
            ],

            'ampm' => [
                'nullable',
                'in:AM,PM',
            ],

            'note' => [
                'nullable',
                'string',
            ],

            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
        ]);

        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chưa đăng nhập',
                ], 401);
            }

            $refund = DB::transaction(function () use ($validated, $user) {
                return Refund::create([
                    'user_id' => $validated['user_id'] ?? $user->id,

                    'bank_name' => $validated['bank_name'],
                    'account_holder' => $validated['account_holder'],
                    'account_number' => $validated['account_number'],

                    'amount' => $validated['amount'],

                    'date' => $validated['date'] ?? now()->toDateString(),
                    'time' => $validated['time'] ?? now()->format('H:i'),

                    'ampm' => $validated['ampm']
                        ?? (now()->format('H') >= 12 ? 'PM' : 'AM'),

                    'note' => $validated['note'] ?? null,

                    'status' => 'pending',
                ]);
            });

            $refund->load('user');

            return response()->json([
                'success' => true,
                'message' => 'Gửi yêu cầu hoàn tiền thành công',
                'data' => $refund,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gửi yêu cầu hoàn tiền thất bại',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xem chi tiết refund
     */
    public function show(string $id)
    {
        try {
            $refund = Refund::with('user')->find($id);

            if (!$refund) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy yêu cầu refund',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin refund thành công',
                'data' => $refund,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy thông tin refund',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cập nhật refund
     * Admin sử dụng
     */
    public function update(Request $request, string $id)
    {
        $refund = Refund::find($id);

        if (!$refund) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu refund',
            ], 404);
        }

        $validated = $request->validate([
            'bank_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'account_holder' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'account_number' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'amount' => [
                'sometimes',
                'required',
                'numeric',
                'min:0',
            ],

            'date' => [
                'nullable',
                'date',
            ],

            'time' => [
                'nullable',
                'date_format:H:i',
            ],

            'ampm' => [
                'nullable',
                'in:AM,PM',
            ],

            'note' => [
                'nullable',
                'string',
            ],

            'status' => [
                'sometimes',
                'required',
                'in:pending,approved,rejected',
            ],
        ]);

        try {
            DB::transaction(function () use ($refund, $validated) {
                $refund->update($validated);
            });

            $refund->load('user');

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật refund thành công',
                'data' => $refund,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật refund thất bại',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Duyệt refund
     */
    public function approve(Request $request, string $id)
    {
        $validated = $request->validate([
            'note' => [
                'nullable',
                'string',
            ],
        ]);

        try {
            $refund = Refund::find($id);

            if (!$refund) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy yêu cầu refund',
                ], 404);
            }

            if ($refund->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Yêu cầu này đã được xử lý',
                ], 422);
            }

            DB::transaction(function () use ($refund, $validated) {
                $refund->update([
                    'status' => 'approved',
                    'note' => $validated['note'] ?? $refund->note,
                ]);
            });

            $refund->load('user');

            return response()->json([
                'success' => true,
                'message' => 'Đã duyệt yêu cầu hoàn tiền',
                'data' => $refund,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Duyệt refund thất bại',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Từ chối refund
     */
    public function reject(Request $request, string $id)
    {
        $validated = $request->validate([
            'note' => [
                'nullable',
                'string',
            ],
        ]);

        try {
            $refund = Refund::find($id);

            if (!$refund) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy yêu cầu refund',
                ], 404);
            }

            if ($refund->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Yêu cầu này đã được xử lý',
                ], 422);
            }

            DB::transaction(function () use ($refund, $validated) {
                $refund->update([
                    'status' => 'rejected',
                    'note' => $validated['note'] ?? $refund->note,
                ]);
            });

            $refund->load('user');

            return response()->json([
                'success' => true,
                'message' => 'Đã từ chối yêu cầu hoàn tiền',
                'data' => $refund,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Từ chối refund thất bại',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xóa refund
     * Admin sử dụng
     */
    public function destroy(string $id)
    {
        try {
            $refund = Refund::find($id);

            if (!$refund) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy yêu cầu refund',
                ], 404);
            }

            $refund->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa refund thành công',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xóa refund thất bại',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}