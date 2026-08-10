<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Info;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    /**
     * Lấy thông tin website
     */
    public function show()
    {
        $info = Info::first();

        return response()->json([
            'success' => true,
            'data' => $info,
        ]);
    }

    /**
     * Cập nhật thông tin website
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'hotline' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'website' => ['nullable', 'string', 'max:500'],
            'facebook' => ['nullable', 'string', 'max:500'],
            'zalo' => ['nullable', 'string', 'max:500'],
            'messenger' => ['nullable', 'string', 'max:500'],
        ]);

        $info = Info::first();

        if (!$info) {
            $info = Info::create($validated);
        } else {
            $info->update($validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin thành công',
            'data' => $info,
        ]);
    }
}