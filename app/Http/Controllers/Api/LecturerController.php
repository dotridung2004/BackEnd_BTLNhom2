<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA; // <-- THÊM DÒNG NÀY

/**
 * @OA\Tag(
 * name="Lecturers",
 * description="Các API liên quan đến quản lý Giảng viên (User role='teacher')"
 * )
 */
class LecturerController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/lecturers",
     * summary="Lấy danh sách Giảng viên",
     * description="Lấy danh sách tất cả người dùng có vai trò 'teacher'.",
     * tags={"Lecturers"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Thành công. Trả về một mảng các giảng viên."
     * )
     * )
     */
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
     * @OA\Post(
     * path="/api/lecturers",
     * summary="Tạo Giảng viên mới",
     * tags={"Lecturers"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * description="Dữ liệu để tạo giảng viên mới",
     * @OA\JsonContent(
     * required={"name", "email", "password", "department_id"},
     * @OA\Property(property="name", type="string", example="Nguyễn Văn A"),
     * @OA\Property(property="email", type="string", format="email", example="nguyenvana@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="password123"),
     * @OA\Property(property="department_id", type="integer", example=1),
     * @OA\Property(property="phone_number", type="string", nullable=true, example="0912345678"),
     * @OA\Property(property="date_of_birth", type="string", nullable=true, example="25/10/1990", description="Định dạng dd/mm/YYYY")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Tạo thành công"
     * ),
     * @OA\Response(
     * response=422,
     * description="Lỗi validation (dữ liệu không hợp lệ)"
     * )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'department_id' => 'required|integer|exists:departments,id',
            'phone_number' => 'nullable|string',
            'date_of_birth' => 'nullable|date_format:d/m/Y',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $dob = $request->date_of_birth ? \DateTime::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d') : null;

            $lecturer = User::create([
                'name' => $request->name,
                'first_name' => $request->name,
                'last_name' => '',
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'department_id' => $request->department_id,
                'phone_number' => $request->phone_number,
                'date_of_birth' => $dob,
                'role' => 'teacher',
            ]);

            $lecturer->load('department');
            return response()->json($lecturer, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi thêm giảng viên: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     * path="/api/lecturers/{lecturer}",
     * summary="Lấy thông tin 1 Giảng viên",
     * tags={"Lecturers"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="lecturer",
     * in="path",
     * description="ID của giảng viên (user ID)",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy giảng viên"
     * )
     * )
     */
    public function show($id)
    {
        try {
            $lecturer = User::with('department')->findOrFail($id);
            return response()->json($lecturer, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy giảng viên'], 404);
        }
    }

    /**
     * @OA\Put(
     * path="/api/lecturers/{lecturer}",
     * summary="Cập nhật thông tin Giảng viên",
     * tags={"Lecturers"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="lecturer",
     * in="path",
     * description="ID của giảng viên (user ID)",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="Dữ liệu cập nhật của giảng viên. Lưu ý: Mật khẩu không được cập nhật qua route này.",
     * @OA\JsonContent(
     * required={"name", "email", "department_id"},
     * @OA\Property(property="name", type="string", example="Nguyễn Văn B"),
     * @OA\Property(property="email", type="string", format="email", example="nguyenvana@example.com"),
     * @OA\Property(property="department_id", type="integer", example=1),
     * @OA\Property(property="phone_number", type="string", nullable=true, example="0912345678"),
     * @OA\Property(property="date_of_birth", type="string", nullable=true, example="25/10/1990", description="Định dạng dd/mm/YYYY")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Cập nhật thành công"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy giảng viên"
     * ),
     * @OA\Response(
     * response=422,
     * description="Lỗi validation (dữ liệu không hợp lệ)"
     * )
     * )
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'department_id' => 'required|integer|exists:departments,id',
            'phone_number' => 'nullable|string',
            'date_of_birth' => 'nullable|date_format:d/m/Y',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $lecturer = User::findOrFail($id);
            $dob = $request->date_of_birth ? \DateTime::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d') : null;

            $lecturer->update([
                'name' => $request->name,
                'first_name' => $request->name,
                'email' => $request->email,
                'department_id' => $request->department_id,
                'phone_number' => $request->phone_number,
                'date_of_birth' => $dob,
            ]);

            $lecturer->load('department');
            return response()->json($lecturer, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi cập nhật giảng viên: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     * path="/api/lecturers/{lecturer}",
     * summary="Xóa Giảng viên",
     * tags={"Lecturers"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="lecturer",
     * in="path",
     * description="ID của giảng viên (user ID)",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Xóa giảng viên thành công"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy giảng viên"
     * ),
     * @OA\Response(
     * response=500,
     * description="Lỗi khi xóa giảng viên"
     * )
     * )
     */
    public function destroy($id)
    {
        try {
            $lecturer = User::findOrFail($id);
            $lecturer->delete();
            return response()->json(['message' => 'Xóa giảng viên thành công'], 200);
        } catch (\Exception $e) {
            // Check if ModelNotFoundException
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                 return response()->json(['message' => 'Không tìm thấy giảng viên'], 404);
            }
            return response()->json(['message' => 'Lỗi khi xóa giảng viên'], 500);
        }
    }
}