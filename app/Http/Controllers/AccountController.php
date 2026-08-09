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
                'name',
                'email',
                'role',
                'balance',
                'created_at',
                'updated_at',
            ])
            ->latest();

        /**
         * Tìm kiếm
         *
         * ?search=nguyen
         */
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
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
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
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
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,

            // Số dư ban đầu
            'balance' => $validated['balance'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo tài khoản thành công.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
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
            'data' => $user->only([
                'id',
                'name',
                'email',
                'role',
                'created_at',
                'updated_at',
            ]),
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
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],

            'password' => [
                'nullable',
                'string',
                'min:6',
                'confirmed',
            ],

            'role' => [
                'sometimes',
                Rule::in(['admin', 'customer']),
            ],
        ]);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        if (isset($validated['role'])) {
            $user->role = $validated['role'];
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật tài khoản thành công.',
            'data' => $user->only([
                'id',
                'name',
                'email',
                'role',
                'created_at',
                'updated_at',
            ]),
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
