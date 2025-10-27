<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/login",
     * operationId="loginUser",
     * tags={"Authentication"},
     * summary="Đăng nhập người dùng",
     * @OA\RequestBody(...),
     * @OA\Response(
     * response=200,
     * description="Đăng nhập thành công",
     * @OA\JsonContent(
     * @OA\Property(property="user", type="object"),
     * @OA\Property(property="token", type="string") // Thêm token vào response description
     * )
     * ),
     * @OA\Response(response=401, ...),
     * @OA\Response(response=422, ...)
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        // Kiểm tra user và password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email hoặc mật khẩu không chính xác'], 401);
        }

        // --- 👇 BẮT ĐẦU PHẦN THÊM VÀO ---
        // Xóa token cũ (tùy chọn) và tạo token mới
        // $user->tokens()->delete(); // Bỏ comment nếu muốn đăng xuất các thiết bị khác
        $token = $user->createToken('api_token_for_' . $user->email)->plainTextToken;
        // --- 👆 KẾT THÚC PHẦN THÊM VÀO ---

        // Trả về cả user và token
        return response()->json([
            'user' => $user,
            'token' => $token, // <-- TRẢ TOKEN VỀ
        ], 200);
    }
}