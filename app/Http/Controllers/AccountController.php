<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /**
     * Danh sách tài khoản
     *
     * GET /api/admin/accounts
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->select([
                'id',
                'userName',
                'role',
                'balance',
                'created_at',
                'updated_at',
            ])
            ->latest();

        /**
         * Tìm kiếm
         *
         * ?search=client
         */
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(
                'userName',
                'like',
                "%{$search}%"
            );
        }

        /**
         * Lọc role
         *
         * ?role=customer
         */
        if ($request->filled('role')) {
            $request->validate([
                'role' => [
                    Rule::in(['admin', 'customer']),
                ],
            ]);

            $query->where('role', $request->role);
        }

        /**
         * Pagination
         *
         * ?per_page=10&page=1
         */
        $perPage = min(
            max((int) $request->input('per_page', 10), 1),
            100
        );

        $users = $query->paginate($perPage);

        /*
         * Đổi userName của DB thành username
         * để frontend không cần thay đổi.
         */
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->userName,
                'role' => $user->role,
                'balance' => $user->balance,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách tài khoản thành công.',
            'data' => $users,
        ]);
    }

    /**
     * Tạo tài khoản
     *
     * POST /api/admin/accounts
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            /*
             * Frontend vẫn gửi username
             */
            'username' => [
                'required',
                'string',
                'max:255',
                'unique:users,userName',
            ],

            'password' => [
                'required',
                'string',
                'min:6',
            ],

            'role' => [
                'nullable',
                Rule::in(['admin', 'customer']),
            ],

            'balance' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        $role = $validated['role'] ?? 'customer';

        $user = User::create([
            /*
             * Map username -> userName
             */
            'userName' => $validated['username'],

            'password' => Hash::make(
                $validated['password']
            ),

            'role' => $role,

            'balance' => $validated['balance'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo tài khoản thành công.',
            'data' => [
                'id' => $user->id,
                'username' => $user->userName,
                'role' => $user->role,
                'balance' => $user->balance,
                'created_at' => $user->created_at,
            ],
        ], 201);
    }

    /**
     * Xem chi tiết tài khoản
     *
     * GET /api/admin/accounts/{id}
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->userName,
                'role' => $user->role,
                'balance' => $user->balance,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

    /**
     * Cập nhật tài khoản
     *
     * PUT /api/admin/accounts/{id}
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'unique:users,userName,' . $user->id,
            ],

            'password' => [
                'nullable',
                'string',
                'min:6',
            ],

            'role' => [
                'sometimes',
                Rule::in(['admin', 'customer']),
            ],

            'balance' => [
                'sometimes',
                'numeric',
                'min:0',
            ],
        ]);

        if (isset($validated['username'])) {
            $user->userName = $validated['username'];
        }

        if (isset($validated['role'])) {
            $user->role = $validated['role'];
        }

        if (isset($validated['balance'])) {
            $user->balance = $validated['balance'];
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make(
                $validated['password']
            );
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật tài khoản thành công.',
            'data' => [
                'id' => $user->id,
                'username' => $user->userName,
                'role' => $user->role,
                'balance' => $user->balance,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

    /**
     * Xóa tài khoản
     *
     * DELETE /api/admin/accounts/{id}
     */
    public function destroy(Request $request, User $user)
    {
        /**
         * Không cho admin tự xóa chính mình.
         */
        if ($request->user()->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không thể tự xóa tài khoản đang đăng nhập.',
            ], 422);
        }

        /**
         * Xóa toàn bộ token của tài khoản bị xóa.
         */
        $user->tokens()->delete();

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa tài khoản thành công.',
        ]);
    }
}
