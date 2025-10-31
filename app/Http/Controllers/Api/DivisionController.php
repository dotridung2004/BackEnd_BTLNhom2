<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Division; // Import Model Division
use Illuminate\Http\Request; // Import Request
use Illuminate\Support\Facades\Log; // Để ghi log lỗi
use Illuminate\Validation\Rule; // Để validate unique
use Exception; // Để bắt lỗi chung

class DivisionController extends Controller
{
    /**
     * Hiển thị danh sách Bộ môn (KHÔNG PHÂN TRANG & CÓ TÌM KIẾM).
     * GET /api/divisions
     * @return \Illuminate\Http\JsonResponse
     */
    // 👇 **** BẮT ĐẦU SỬA ĐỔI **** 👇
    public function index(Request $request)
    {
        try {
            // Lấy query tìm kiếm từ URL (ví dụ: /api/divisions?search=công nghệ)
            $searchQuery = $request->query('search');

            // Bắt đầu câu truy vấn (Query Builder)
            $query = Division::with('department') 
                           ->withCount(['teachers', 'courses']);

            // Thêm logic tìm kiếm (nếu có)
            if ($searchQuery) {
                $query->where(function($q) use ($searchQuery) {
                    // Tìm theo Tên bộ môn
                    $q->where('name', 'LIKE', '%' . $searchQuery . '%')
                      // Hoặc tìm theo Mã bộ môn
                      ->orWhere('code', 'LIKE', '%' . $searchQuery . '%')
                      // Hoặc tìm theo Tên Khoa (qua quan hệ 'department')
                      ->orWhereHas('department', function($deptQuery) use ($searchQuery) {
                          $deptQuery->where('name', 'LIKE', '%' . $searchQuery . '%');
                      });
                });
            }

            // Sắp xếp VÀ LẤY TẤT CẢ (thay vì paginate)
            $divisions = $query->orderBy('updated_at', 'desc')
                                ->get(); // 👈 THAY ĐỔI CHÍNH

            // Map lại dữ liệu
            $mappedData = $divisions->map(function ($division) {
                return [
                    'id' => $division->id,
                    'code' => $division->code,
                    'name' => $division->name,
                    'department_id' => $division->department_id,
                    'departmentName' => $division->department ? $division->department->name : 'N/A', // Lấy tên khoa
                    'teacherCount' => $division->teachers_count ?? 0,
                    'courseCount' => $division->courses_count ?? 0,
                    'description' => $division->description ?? null,
                    'created_at' => $division->created_at,
                    'updated_at' => $division->updated_at,
                ];
            });

            // Trả về một JSON array (danh sách đầy đủ)
            return response()->json($mappedData);
            
        } catch (Exception $e) {
            Log::error("Lỗi DivisionController@index: " . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi tải danh sách bộ môn.'], 500); // Trả về lỗi 500
        }
    }
    // 👆 **** KẾT THÚC SỬA ĐỔI **** 👆


    /**
     * Lưu một Bộ môn mới vào database.
     * POST /api/divisions
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'code' => 'required|string|max:255|unique:divisions,code',
            'name' => 'required|string|max:255',
            'department_id' => 'required|integer|exists:departments,id',
            'description' => 'nullable|string',
        ]);

        try {
            $division = Division::create($validatedData);
            
            // Tải lại quan hệ để trả về dữ liệu đầy đủ
            $division->load('department');
            
            // Trả về dữ liệu đã map (để Flutter cập nhật đúng)
            $divisionData = [
                'id' => $division->id,
                'code' => $division->code,
                'name' => $division->name,
                'department_id' => $division->department_id,
                'departmentName' => $division->department ? $division->department->name : 'N/A', // Gửi cả departmentName
                'teacherCount' => 0, // Mới tạo
                'courseCount' => 0, // Mới tạo
                'description' => $division->description ?? null,
                'created_at' => $division->created_at,
                'updated_at' => $division->updated_at,
            ];

            return response()->json($divisionData, 201); // Trả về data đã map

        } catch (Exception $e) {
            Log::error("Lỗi DivisionController@store: " . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi thêm bộ môn.'], 500);
        }
    }

    /**
     * Hiển thị chi tiết một Bộ môn cụ thể.
     * GET /api/divisions/{id}
     */
    public function show(string $id)
    {
       try {
            // Tải bộ môn và các quan hệ
            $division = Division::with(['department', 'teachers', 'courses'])->findOrFail($id);

             // Format dữ liệu trả về cho Flutter
             $divisionData = [
                'id' => $division->id,
                'code' => $division->code,
                'name' => $division->name,
                'department_id' => $division->department_id,
                'departmentName' => $division->department ? $division->department->name : 'N/A',
                'description' => $division->description ?? null, 
                // Map danh sách giảng viên
                'teachersList' => $division->teachers->map(function($teacher) {
                    return [
                        'id' => $teacher->id, 
                        'name' => $teacher->name, 
                        'email' => $teacher->email, 
                        'phone_number' => $teacher->phone_number,
                        'first_name' => $teacher->first_name,
                        'last_name' => $teacher->last_name,
                        'role' => $teacher->role,
                        'status' => $teacher->status,
                        // (Thêm 'code' nếu model User của bạn có)
                        // 'code' => $teacher->code, 
                    ];
                }),
                // Map danh sách môn học
                'coursesList' => $division->courses->map(function($course) {
                       return [
                           'id' => $course->id, 
                           'code' => $course->code, 
                           'name' => $course->name, 
                           'credits' => $course->credits,
                       ];
                }),
                // Đếm số lượng từ danh sách đã tải
                'teacherCount' => $division->teachers->count(),
                'courseCount' => $division->courses->count(),
            ];

            return response()->json($divisionData); // Trả về JSON chi tiết
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
             return response()->json(['message' => 'Không tìm thấy bộ môn.'], 404);
        }
        catch (Exception $e) {
            Log::error("Lỗi DivisionController@show (ID: $id): " . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi tải chi tiết bộ môn.'], 500);
        }
    }

    /**
     * Cập nhật thông tin Bộ môn.
     * PUT /api/divisions/{id}
     */
    public function update(Request $request, string $id)
    {
       try {
            $division = Division::findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'department_id' => 'required|integer|exists:departments,id',
                'description' => 'nullable|string',
                // Không validate 'code' vì không cho sửa
            ]);

            $division->update($validatedData);

            $division->load('department'); 
            $division->loadCount(['teachers', 'courses']); 

            // Trả về dữ liệu đã map
            $divisionData = [
                'id' => $division->id,
                'code' => $division->code, // Giữ nguyên mã cũ
                'name' => $division->name,
                'department_id' => $division->department_id,
                'departmentName' => $division->department ? $division->department->name : 'N/A',
                'teacherCount' => $division->teachers_count ?? 0,
                'courseCount' => $division->courses_count ?? 0,
                'description' => $division->description ?? null,
                'created_at' => $division->created_at,
                'updated_at' => $division->updated_at,
            ];

            return response()->json($divisionData);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
             return response()->json(['message' => 'Không tìm thấy bộ môn để cập nhật.'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
             return response()->json(['message' => 'Dữ liệu không hợp lệ.', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error("Lỗi DivisionController@update (ID: $id): " . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi cập nhật bộ môn.'], 500);
        }
    }

    /**
     * Xóa một Bộ môn.
     * DELETE /api/divisions/{id}
     */
    public function destroy(string $id)
    {
       try {
            $division = Division::findOrFail($id);
            $division->delete();

            return response()->noContent(); // 204

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
             return response()->json(['message' => 'Không tìm thấy bộ môn để xóa.'], 404);
        } catch (Exception $e) {
            Log::error("Lỗi DivisionController@destroy (ID: $id): " . $e->getMessage());
            if ($e instanceof \Illuminate\Database\QueryException && str_contains($e->getMessage(), 'foreign key constraint fails')) {
                 return response()->json(['message' => 'Không thể xóa bộ môn vì còn dữ liệu liên quan.'], 409);
            }
            return response()->json(['message' => 'Lỗi khi xóa bộ môn.'], 500);
        }
    }
}