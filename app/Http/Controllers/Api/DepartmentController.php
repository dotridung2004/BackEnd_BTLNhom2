<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule; // Thêm import này

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // 👇 CẬP NHẬT HÀM NÀY 👇
            // Đếm cả 'divisions' và 'teachers' để khớp với Flutter Model
            $departments = Department::withCount(['divisions', 'teachers'])->get();
            return response()->json($departments);
        } catch (Exception $e) {
            Log::error("Lỗi DepartmentController@index: " . $e->getMessage());
            return response()->json(['message' => 'Lỗi máy chủ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    // 👇 CẬP NHẬT HÀM NÀY 👇
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:departments,code', // Đảm bảo code là duy nhất
            ]);

            $department = Department::create($validated);
            
            // Tải lại với 'counts' để trả về cho Flutter
            $department->loadCount(['divisions', 'teachers']);

            return response()->json($department, 201); // 201 Created

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error("Lỗi DepartmentController@store: " . $e->getMessage());
            return response()->json(['message' => 'Lỗi máy chủ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // (Bạn có thể làm hàm này sau nếu cần xem chi tiết)
        try {
            $department = Department::withCount(['divisions', 'teachers'])
                                    ->with(['divisions.teachers']) // Lấy cả danh sách con
                                    ->findOrFail($id);
            return response()->json($department);
        } catch (Exception $e) {
            return response()->json(['message' => 'Không tìm thấy khoa'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    // 👇 CẬP NHẬT HÀM NÀY 👇
    public function update(Request $request, string $id)
    {
        try {
            $department = Department::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('departments')->ignore($department->id), // Cho phép code này nếu là của chính nó
                ],
            ]);

            $department->update($validated);
            
            // Tải lại với 'counts' để trả về cho Flutter
            $department->loadCount(['divisions', 'teachers']);

            return response()->json($department); // 200 OK

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $e->errors()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy khoa'], 404);
        } catch (Exception $e) {
            Log::error("Lỗi DepartmentController@update: " . $e->getMessage());
            return response()->json(['message' => 'Lỗi máy chủ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    // 👇 CẬP NHẬT HÀM NÀY 👇
    public function destroy(string $id)
    {
        try {
            $department = Department::findOrFail($id);

            // (Tùy chọn: Kiểm tra an toàn)
            // Nếu khoa vẫn còn bộ môn, không cho xóa
            if ($department->divisions()->count() > 0) {
                 return response()->json(['message' => 'Không thể xóa khoa khi vẫn còn bộ môn.'], 409); // 409 Conflict
            }

            $department->delete();

            return response()->json(null, 204); // 204 No Content

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy khoa'], 404);
        } catch (Exception $e) {
            Log::error("Lỗi DepartmentController@destroy: " . $e->getMessage());
            return response()->json(['message' => 'Lỗi máy chủ: ' . $e->getMessage()], 500);
        }
    }
}