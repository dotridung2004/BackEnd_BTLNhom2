<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Division; // Import Model Division
use Illuminate\Http\Request; // Import Request
use Illuminate\Support\Facades\Log; // Để ghi log lỗi
use Illuminate\Validation\Rule; // Để validate unique
use Exception; // Để bắt lỗi chung
use OpenApi\Annotations as OA; // <-- THÊM DÒNG NÀY

/**
 * @OA\Tag(
 * name="Divisions",
 * description="Các API liên quan đến quản lý Bộ Môn"
 * )
 */
class DivisionController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/divisions",
     * summary="Lấy danh sách Bộ Môn (có tìm kiếm)",
     * description="Hiển thị danh sách Bộ môn (KHÔNG PHÂN TRANG). Hỗ trợ tìm kiếm theo Tên/Mã bộ môn, Tên khoa.",
     * tags={"Divisions"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="search",
     * in="query",
     * description="Tìm kiếm theo Tên/Mã bộ môn hoặc Tên khoa",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công. Trả về một mảng các bộ môn."
     * )
     * )
     */
    public function index(Request $request)
    {
        try {
            $searchQuery = $request->query('search');

            $query = Division::with('department')
                ->withCount(['teachers', 'courses']);

            if ($searchQuery) {
                $query->where(function ($q) use ($searchQuery) {
                    $q->where('name', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('code', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhereHas('department', function ($deptQuery) use ($searchQuery) {
                            $deptQuery->where('name', 'LIKE', '%' . $searchQuery . '%');
                        });
                });
            }

            $divisions = $query->orderBy('updated_at', 'desc')
                ->get();

            $mappedData = $divisions->map(function ($division) {
                return [
                    'id' => $division->id,
                    'code' => $division->code,
                    'name' => $division->name,
                    'department_id' => $division->department_id,
                    'departmentName' => $division->department ? $division->department->name : 'N/A',
                    'teacherCount' => $division->teachers_count ?? 0,
                    'courseCount' => $division->courses_count ?? 0,
                    'description' => $division->description ?? null,
                    'created_at' => $division->created_at,
                    'updated_at' => $division->updated_at,
                ];
            });

            return response()->json($mappedData);
        } catch (Exception $e) {
            Log::error("Lỗi DivisionController@index: " . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi tải danh sách bộ môn.'], 500);
        }
    }


    /**
     * @OA\Post(
     * path="/api/divisions",
     * summary="Tạo Bộ môn mới",
     * description="Lưu một Bộ môn mới vào database.",
     * tags={"Divisions"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * description="Dữ liệu của bộ môn mới",
     * @OA\JsonContent(
     * required={"code", "name", "department_id"},
     * @OA\Property(property="code", type="string", example="BM-KHMT"),
     * @OA\Property(property="name", type="string", example="Bộ môn Khoa học Máy tính"),
     * @OA\Property(property="department_id", type="integer", example=1),
     * @OA\Property(property="description", type="string", nullable=true, example="Mô tả về bộ môn...")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Tạo thành công"
     * ),
     * @OA\Response(
     * response=422,
     * description="Dữ liệu không hợp lệ (Validation error)"
     * )
     * )
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
            
            $division->load('department');
            
            $divisionData = [
                'id' => $division->id,
                'code' => $division->code,
                'name' => $division->name,
                'department_id' => $division->department_id,
                'departmentName' => $division->department ? $division->department->name : 'N/A',
                'teacherCount' => 0,
                'courseCount' => 0,
                'description' => $division->description ?? null,
                'created_at' => $division->created_at,
                'updated_at' => $division->updated_at,
            ];

            return response()->json($divisionData, 201);
        } catch (Exception $e) {
            Log::error("Lỗi DivisionController@store: " . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi thêm bộ môn.'], 500);
        }
    }

    /**
     * @OA\Get(
     * path="/api/divisions/{division}",
     * summary="Lấy chi tiết 1 Bộ môn",
     * description="Hiển thị chi tiết một Bộ môn cụ thể, bao gồm danh sách giảng viên và môn học.",
     * tags={"Divisions"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="division",
     * in="path",
     * description="ID của bộ môn",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy bộ môn"
     * )
     * )
     */
    public function show(string $id)
    {
        try {
            $division = Division::with(['department', 'teachers', 'courses'])->findOrFail($id);

            $divisionData = [
                'id' => $division->id,
                'code' => $division->code,
                'name' => $division->name,
                'department_id' => $division->department_id,
                'departmentName' => $division->department ? $division->department->name : 'N/A',
                'description' => $division->description ?? null,
                'teachersList' => $division->teachers->map(function ($teacher) {
                    return [
                        'id' => $teacher->id,
                        'name' => $teacher->name,
                        'email' => $teacher->email,
                        'phone_number' => $teacher->phone_number,
                        'first_name' => $teacher->first_name,
                        'last_name' => $teacher->last_name,
                        'role' => $teacher->role,
                        'status' => $teacher->status,
                    ];
                }),
                'coursesList' => $division->courses->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'code' => $course->code,
                        'name' => $course->name,
                        'credits' => $course->credits,
                    ];
                }),
                'teacherCount' => $division->teachers->count(),
                'courseCount' => $division->courses->count(),
            ];

            return response()->json($divisionData);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy bộ môn.'], 404);
        } catch (Exception $e) {
            Log::error("Lỗi DivisionController@show (ID: $id): " . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi tải chi tiết bộ môn.'], 500);
        }
    }

    /**
     * @OA\Put(
     * path="/api/divisions/{division}",
     * summary="Cập nhật thông tin Bộ môn",
     * description="Cập nhật thông tin Bộ môn (Lưu ý: Không cho phép sửa 'code').",
     * tags={"Divisions"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="division",
     * in="path",
     * description="ID của bộ môn",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="Dữ liệu cập nhật của bộ môn",
     * @OA\JsonContent(
     * required={"name", "department_id"},
     * @OA\Property(property="name", type="string", example="Bộ môn Kỹ thuật Máy tính"),
     * @OA\Property(property="department_id", type="integer", example=1),
     * @OA\Property(property="description", type="string", nullable=true, example="Mô tả đã cập nhật...")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Cập nhật thành công"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy bộ môn"
     * ),
     * @OA\Response(
     * response=422,
     * description="Dữ liệu không hợp lệ (Validation error)"
     * )
     * )
     */
    public function update(Request $request, string $id)
    {
        try {
            $division = Division::findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'department_id' => 'required|integer|exists:departments,id',
                'description' => 'nullable|string',
            ]);

            $division->update($validatedData);

            $division->load('department');
            $division->loadCount(['teachers', 'courses']);

            $divisionData = [
                'id' => $division->id,
                'code' => $division->code,
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
     * @OA\Delete(
     * path="/api/divisions/{division}",
     * summary="Xóa một Bộ môn",
     * description="Xóa một Bộ môn.",
     * tags={"Divisions"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="division",
     * in="path",
     * description="ID của bộ môn",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=204,
     * description="Xóa thành công (No Content)"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy bộ môn"
     * ),
     * @OA\Response(
     * response=409,
     * description="Không thể xóa (còn dữ liệu liên quan)"
     * )
     * )
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