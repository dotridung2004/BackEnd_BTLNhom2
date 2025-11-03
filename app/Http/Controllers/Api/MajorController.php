<?php
// Tên file: app/Http/Controllers/Api/MajorController.php
// *** ĐÃ SỬA LỖI SẮP XẾP (orderBy) ***

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Major;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Department; 

class MajorController extends Controller
{
    /**
     * Helper: Map một Major model về dạng JSON cho front-end
     */
    private function mapMajorToJson($major)
    {
        $major->loadMissing('department');
        $departmentName = $major->department ? $major->department->name : 'N/A';
        
        return [
            'id' => $major->id,
            'code' => $major->code,
            'name' => $major->name,
            'departmentName' => $departmentName, 
            'teachers_count' => $major->teachers()->count(), 
            'updated_at' => $major->updated_at->toIso8601String(),
        ];
    }

    /**
     * Hiển thị danh sách Ngành học (cho Bảng dữ liệu).
     */
    public function index()
    {
        try {
            // 👇 **** SỬA ĐỔI QUAN TRỌNG Ở ĐÂY **** 👇
            $majors = Major::with('department') 
                           ->withCount('teachers') 
                           ->orderBy('updated_at', 'desc') // <-- DÒNG NÀY SỬA LỖI SẮP XẾP
                           ->get();
            // 👆 **** KẾT THÚC SỬA ĐỔI **** 👆
            
            $mappedMajors = $majors->map(function ($major) {
                $departmentName = $major->department ? $major->department->name : 'N/A';
                
                return [
                    'id' => $major->id,
                    'code' => $major->code,
                    'name' => $major->name,
                    'departmentName' => $departmentName, 
                    'teachers_count' => $major->teachers_count,
                    'updated_at' => $major->updated_at->toIso8601String(),
                ];
            });

            return response()->json($mappedMajors);

        } catch (Exception $e) {
            Log::error("Lỗi MajorController@index: " . $e->getMessage());
            return response()->json([], 500); 
        }
    }

    /**
     * Lưu một Ngành học mới vào database.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'ma_nganh' => 'required|string|unique:majors,code', 
                'ten_nganh' => 'required|string|max:255',
                'khoa_id' => 'required|integer|exists:departments,id', 
                'mo_ta' => 'nullable|string', 
            ]);

            $major = Major::create([
                'code' => $validatedData['ma_nganh'],
                'name' => $validatedData['ten_nganh'],
                'department_id' => $validatedData['khoa_id'],
                'description' => $validatedData['mo_ta'], 
            ]);

            // Trả về đối tượng đã được map (SỬA LỖI 5 GIÂY)
            return response()->json($this->mapMajorToJson($major), 201);

        } catch (Exception $e) {
            Log::error("Lỗi MajorController@store: " . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Hiển thị chi tiết một Ngành học (cho Dialog Xem và Sửa).
     */
    public function show(Major $major)
    {
        try {
            $major->load('department', 'teachers');

            $tenKhoa = $major->department ? $major->department->name : null;

            $teachersList = $major->teachers->map(function ($teacher) {
                return [
                    'ma_gv' => $teacher->code, 
                    'ho_ten' => $teacher->name, 
                    'email' => $teacher->email,
                ];
            });

            return response()->json([
                'id' => $major->id,
                'ma_nganh' => $major->code,         
                'ten_nganh' => $major->name,       
                'mo_ta' => $major->description,
                'khoa_id' => $major->department_id, 
                'khoa' => $major->department ? [
                    'id' => $major->department->id,
                    'ten_khoa' => $tenKhoa 
                ] : null,
                'teachers_count' => $major->teachers->count(), 
                'teachers' => $teachersList,
            ]);

        } catch (Exception $e) {
            Log::error("Lỗi MajorController@show: " . $e->getMessage());
            return response()->json(['message' => 'Không tìm thấy dữ liệu: ' . $e->getMessage()], 404);
        }
    }

    /**
     * Cập nhật Ngành học.
     */
    public function update(Request $request, Major $major)
    {
        try {
            $validatedData = $request->validate([
                'ma_nganh' => ['required', 'string', Rule::unique('majors', 'code')->ignore($major->id)],
                'ten_nganh' => 'required|string|max:255',
                'khoa_id' => 'required|integer|exists:departments,id',
                'mo_ta' => 'nullable|string',
            ]);

            $major->update([
                'code' => $validatedData['ma_nganh'],
                'name' => $validatedData['ten_nganh'],
                'department_id' => $validatedData['khoa_id'],
                'description' => $validatedData['mo_ta'],
            ]);

            // Trả về đối tượng đã được map (SỬA LỖI 5 GIÂY)
            return response()->json($this->mapMajorToJson($major));

        } catch (Exception $e) {
            Log::error("Lỗi MajorController@update: " . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Xóa Ngành học.
     */
    public function destroy(Major $major)
    {
        try {
            $major->delete();
            return response()->json(null, 204); 
        } catch (Exception $e) {
            Log::error("Lỗi MajorController@destroy: " . $e->getMessage());
            return response()->json(['message' => 'Không thể xóa ngành này. Có thể do ràng buộc dữ liệu.'], 409);
        }
    }
}