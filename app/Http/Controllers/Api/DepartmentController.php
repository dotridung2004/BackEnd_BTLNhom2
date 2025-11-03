<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Tải khoa, cùng với quan hệ 'head' (để lấy tên trưởng khoa)
            // và đếm 'teachers' (giảng viên) và 'majors' (ngành)
            $departments = Department::with('head')
                                     ->withCount(['teachers', 'majors'])
                                     // 👇 SỬA LỖI: Sắp xếp theo 'updated_at'
                                     ->orderBy('updated_at', 'desc')
                                     ->get();

            // Biến đổi kết quả để khớp với key mà frontend (Flutter) đang mong đợi
            $departments->transform(function ($department) {
                
                // 1. Thêm 'head_teacher_name'
                $department->head_teacher_name = $department->head ? $department->head->name : 'N/A';
                
                // 2. (SỬA LỖI): Không cần đổi tên 'majors_count'
                // Model Flutter (department.dart) đã được cập nhật để đọc 'majors_count'
                
                // Xóa quan hệ 'head' đã tải để JSON trả về gọn gàng
                unset($department->head); 
                
                return $department;
            });

            return response()->json($departments);

        } catch (Exception $e) {
            Log::error("Lỗi DepartmentController@index: " . $e->getMessage());
            return response()->json(['message' => 'Lỗi máy chủ: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    // 👇 **** TRIỂN KHAI & SỬA LỖI 201 **** 👇
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:departments,code',
                'head_id' => 'nullable|exists:users,id',
                'description' => 'nullable|string', // (Đã thêm)
            ]);

            $department = Department::create($validated);
            
            // Tải lại dữ liệu (bao gồm 'head' và 'counts') để gửi về
            $department->load('head');
            $department->loadCount(['teachers', 'majors']);

            // Biến đổi dữ liệu trả về cho giống hàm index
            $department->head_teacher_name = $department->head ? $department->head->name : 'N/A';
            unset($department->head);

            // Trả về 201 Created (Fix lỗi 'Mã lỗi: 200' của bạn)
            return response()->json($department, 201); 

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Lỗi validation
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error("Lỗi DepartmentController@store: " . $e->getMessage());
            return response()->json(['message' => 'Lỗi máy chủ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     * (Hàm 'show' mặc định của apiResource - /api/departments/{id})
     */
    public function show(string $id)
    {
         try {
            // Chỉ trả về thông tin cơ bản
            $department = Department::with('head')
                                    ->withCount(['teachers', 'majors'])
                                    ->findOrFail($id);
            
            $department->head_teacher_name = $department->head ? $department->head->name : 'N/A';
            unset($department->head);

            return response()->json($department);
        } catch (Exception $e) {
            return response()->json(['message' => 'Không tìm thấy khoa'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    // 👇 **** TRIỂN KHAI **** 👇
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
                'head_id' => 'nullable|exists:users,id',
                'description' => 'nullable|string', // (Đã thêm)
            ]);

            $department->update($validated);
            
            // Tải lại dữ liệu (bao gồm 'head' và 'counts') để gửi về
            $department->load('head');
            $department->loadCount(['teachers', 'majors']);
            
            $department->head_teacher_name = $department->head ? $department->head->name : 'N/A';
            unset($department->head);

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
    // 👇 **** TRIỂN KHAI **** 👇
    public function destroy(string $id)
    {
        try {
            $department = Department::findOrFail($id);

            // (Tùy chọn: Kiểm tra an toàn)
            if ($department->divisions()->count() > 0 || $department->majors()->count() > 0) {
                 return response()->json(['message' => 'Không thể xóa khoa khi vẫn còn bộ môn hoặc ngành học.'], 409); // 409 Conflict
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

    /**
     * Display the specified resource with full details.
     * (Tương ứng với API: /api/departments/{id}/details)
     */
    public function getDetails(string $id)
    {
        try {
            $department = Department::with(['head', 'teachers', 'majors', 'divisions'])
                                     ->withCount(['teachers', 'majors'])
                                     ->findOrFail($id);

            // Tạo một cấu trúc JSON lồng nhau
            $details = [
                // 'department' key chứa tất cả thông tin của khoa
                'department' => [
                    'id' => $department->id,
                    'code' => $department->code,
                    'name' => $department->name,
                    'description' => $department->description,
                    'head_id' => $department->head_id,
                    'head_teacher_name' => $department->head ? $department->head->name : 'N/A', 
                    
                    // Gửi cả 2 count (Flutter sẽ đọc cái nó cần)
                    'teachers_count' => $department->teachers_count,
                    'majors_count' => $department->majors_count, 
                ],
                'teachers' => $department->teachers,
                'majors' => $department->majors,
                'divisions' => $department->divisions,
            ];

            return response()->json($details);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy khoa'], 404);
        } catch (Exception $e) {
            Log::error("Lỗi DepartmentController@getDetails: " . $e->getMessage());
            return response()->json(['message' => 'Lỗi máy chủ: ' . $e->getMessage()], 500);
        }
    }
}
