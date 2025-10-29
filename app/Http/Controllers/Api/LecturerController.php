<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;     // <<< THÊM MỚI: Để mã hóa mật khẩu
use Illuminate\Support\Facades\Validator; // <<< THÊM MỚI: Để kiểm tra dữ liệu

class LecturerController extends Controller
{
    // ... hàm index() giữ nguyên ...
    public function index()
    {
       try {
            $lecturers = User::where('role', 'teacher')
                                ->with('department')
                                ->orderBy('name', 'asc')
                                ->get();
            return response()->json($lecturers, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Đã xảy ra lỗi khi truy vấn dữ liệu.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // <<< THAY THẾ TOÀN BỘ HÀM NÀY
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'user_code' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',
            'department_id' => 'required|integer|exists:departments,id',
            'phone_number' => 'nullable|string',
            'dob' => 'nullable|date_format:d/m/Y',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
             $dob = $request->dob ? \DateTime::createFromFormat('d/m/Y', $request->dob)->format('Y-m-d') : null;

            $lecturer = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'user_code' => $request->user_code,
                'password' => Hash::make($request->password),
                'department_id' => $request->department_id,
                'phone_number' => $request->phone_number,
                'dob' => $dob,
                'role' => 'teacher', // Gán vai trò là giảng viên
            ]);

            // Trả về dữ liệu giảng viên vừa tạo kèm thông tin khoa
            $lecturer->load('department');

            return response()->json($lecturer, 201); // 201 Created

        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi thêm giảng viên: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Tùy chọn: có thể triển khai hàm này để lấy chi tiết 1 giảng viên
        try {
            $lecturer = User::with('department')->findOrFail($id);
            return response()->json($lecturer, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy giảng viên'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // <<< THAY THẾ TOÀN BỘ HÀM NÀY
         $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            // Kiểm tra email duy nhất, nhưng bỏ qua chính user đang sửa
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'user_code' => 'required|string|max:255|unique:users,user_code,' . $id,
            'department_id' => 'required|integer|exists:departments,id',
            'phone_number' => 'nullable|string',
            'dob' => 'nullable|date_format:d/m/Y',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $lecturer = User::findOrFail($id);
            $dob = $request->dob ? \DateTime::createFromFormat('d/m/Y', $request->dob)->format('Y-m-d') : null;

            $lecturer->update([
                'name' => $request->name,
                'email' => $request->email,
                'user_code' => $request->user_code,
                'department_id' => $request->department_id,
                'phone_number' => $request->phone_number,
                'dob' => $dob,
            ]);

            $lecturer->load('department');
            return response()->json($lecturer, 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi cập nhật giảng viên: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // <<< THAY THẾ TOÀN BỘ HÀM NÀY
        try {
            $lecturer = User::findOrFail($id);
            $lecturer->delete();
            // 204 No Content là response chuẩn cho việc xóa thành công mà không cần trả về body
            // return response()->noContent();
            // Hoặc trả về message để dễ debug ở frontend
            return response()->json(['message' => 'Xóa giảng viên thành công'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi xóa giảng viên'], 500);
        }
    }
}