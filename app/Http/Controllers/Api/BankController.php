<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankController extends Controller
{
    /**
     * GET /api/banks
     */
    public function index()
    {
        $banks = Bank::orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách ngân hàng thành công',
            'data' => $banks,
        ]);
    }

    /**
     * GET /api/banks/{id}
     */
    public function show($id)
    {
        $bank = Bank::find($id);

        if (!$bank) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy ngân hàng',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin ngân hàng thành công',
            'data' => $bank,
        ]);
    }

    /**
     * POST /api/banks
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => [
                'required',
                'string',
                'max:255',
            ],

            'account_number' => [
                'required',
                'string',
                'max:50',
            ],

            'account_name' => [
                'required',
                'string',
                'max:255',
            ],

            'transfer_content' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'required',
                'boolean',
            ],
        ]);

        $bank = DB::transaction(function () use ($validated) {

            /*
             * Nếu bank mới được tạo với status = true
             * thì tắt tất cả bank khác trước.
             */
            if ($validated['status'] === true) {
                Bank::query()->update([
                    'status' => false,
                ]);
            }

            return Bank::create($validated);
        });

        return response()->json([
            'success' => true,
            'message' => 'Thêm ngân hàng thành công',
            'data' => $bank,
        ], 201);
    }

    /**
     * PUT /api/banks/{id}
     */
    public function update(Request $request, $id)
    {
        $bank = Bank::find($id);

        if (!$bank) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy ngân hàng',
            ], 404);
        }

        $validated = $request->validate([
            'bank_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'account_number' => [
                'sometimes',
                'required',
                'string',
                'max:50',
            ],

            'account_name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'transfer_content' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'sometimes',
                'required',
                'boolean',
            ],
        ]);

        $bank = DB::transaction(function () use (
            $bank,
            $validated
        ) {

            /*
             * Nếu bank hiện tại được bật
             * thì tắt tất cả bank khác.
             */
            if (
                isset($validated['status']) &&
                $validated['status'] === true
            ) {
                Bank::where('id', '!=', $bank->id)
                    ->update([
                        'status' => false,
                    ]);
            }

            $bank->update($validated);

            return $bank->fresh();
        });

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật ngân hàng thành công',
            'data' => $bank,
        ]);
    }

    /**
     * DELETE /api/banks/{id}
     */
    public function destroy($id)
    {
        $bank = Bank::find($id);

        if (!$bank) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy ngân hàng',
            ], 404);
        }

        /*
         * Không cho phép xóa bank đang active.
         */
        if ($bank->status === true) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa tài khoản ngân hàng đang hoạt động',
            ], 400);
        }

        $bank->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa ngân hàng thành công',
        ]);
    }
}
