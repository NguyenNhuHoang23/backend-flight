<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function loginAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không chính xác.',
            ], 401);
        }

        // KHÔNG xóa token cũ
        // Để một tài khoản đăng nhập được nhiều máy
        $token = $user->createToken(
            $user->role === 'admin'
                ? 'AdminToken'
                : 'CustomerToken'
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công!',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ],
        ]);
    }

    /**
     * Đăng nhập bằng userName
     *
     * POST /api/client/login
     *
     * Mỗi thiết bị sẽ có 1 token riêng.
     * Đăng nhập máy mới KHÔNG logout máy cũ.
     */
    public function login(Request $request)
    {
        $request->validate([
            'userName' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('userName', $request->userName)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Tên đăng nhập hoặc mật khẩu không chính xác.',
            ], 401);
        }

        // Không xóa token cũ
        // Cho phép tài khoản đăng nhập nhiều thiết bị
        $token = $user->createToken(
            $user->role === 'admin'
                ? 'AdminToken'
                : 'CustomerToken'
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công!',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'userName' => $user->userName,
                    'role' => $user->role,
                    'balance' => $user->balance,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
            ],
        ]);
    }

    /**
     * Đăng xuất thiết bị hiện tại
     *
     * POST /api/client/logout
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công!',
        ]);
    }

    /**
     * Thông tin tài khoản hiện tại
     *
     * GET /api/client/profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $request->user()->id,
                'userName' => $request->user()->userName,
                'role' => $request->user()->role,
                'balance' => $request->user()->balance,
                'created_at' => $request->user()->created_at,
                'updated_at' => $request->user()->updated_at,
            ],
        ]);
    }
}