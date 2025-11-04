<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 * name="Departments",
 * description="Các API liên quan đến quản lý Khoa"
 * )
 */
class DepartmentController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/departments",
     * summary="Lấy danh sách Khoa",
     * description="Tải danh sách tất cả các khoa, cùng với tên trưởng khoa và số lượng giảng viên, ngành học.",
     * tags={"Departments"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Thành công. Trả về một mảng các khoa."
     * )
     * )
     */
    public function index()
    {
        try {
            $departments = Department::with('head')
                ->withCount(['teachers', 'majors'])
                ->orderBy('updated_at', 'desc')
                ->get();

            $departments->transform(function ($department) {
                $department->head_teacher_name = $department->head ? $department->head->name : 'N/A';
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
     * @OA\Post(
     * path="/api/departments",
     * summary="Tạo Khoa mới",
     * description="Tạo một khoa mới.",
     * tags={"Departments"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * description="Dữ liệu của khoa mới",
     * @OA\JsonContent(
     * required={"name", "code"},
     * @OA\Property(property="name", type="string", example="Khoa Công nghệ thông tin"),
     * @OA\Property(property="code", type="string", example="CNTT"),
     * @OA\Property(property="head_id", type="integer", nullable=true, example=2),
     * @OA\Property(property="description", type="string", nullable=true, example="Mô tả về khoa...")
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
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:departments,code',
                'head_id' => 'nullable|exists:users,id',
                'description' => 'nullable|string',
            ]);

            $department = Department::create($validated);
            
            $department->load('head');
            $department->loadCount(['teachers', 'majors']);

            $department->head_teacher_name = $department->head ? $department->head->name : 'N/A';
            unset($department->head);

            return response()->json($department, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            Log::error("Lỗi DepartmentController@store: " . $e->getMessage());
            return response()->json(['message' => 'Lỗi máy chủ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     * path="/api/departments/{department}",
     * summary="Lấy thông tin 1 Khoa (cơ bản)",
     * description="Hàm 'show' mặc định của apiResource.",
     * tags={"Departments"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="department",
     * in="path",
     * description="ID của khoa",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy khoa"
     * )
     * )
     */
    public function show(string $id)
    {
        try {
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
     * @OA\Put(
     * path="/api/departments/{department}",
     * summary="Cập nhật thông tin Khoa",
     * description="Cập nhật thông tin của một khoa đã tồn tại.",
     * tags={"Departments"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="department",
     * in="path",
     * description="ID của khoa",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="Dữ liệu cập nhật của khoa",
     * @OA\JsonContent(
     * required={"name", "code"},
     * @OA\Property(property="name", type="string", example="Khoa CNTT (đã cập nhật)"),
     * @OA\Property(property="code", type="string", example="CNTT"),
     * @OA\Property(property="head_id", type="integer", nullable=true, example=2),
     * @OA\Property(property="description", type="string", nullable=true, example="Mô tả đã cập nhật...")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Cập nhật thành công"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy khoa"
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
            $department = Department::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('departments')->ignore($department->id),
                ],
                'head_id' => 'nullable|exists:users,id',
                'description' => 'nullable|string',
            ]);

            $department->update($validated);
            
            $department->load('head');
            $department->loadCount(['teachers', 'majors']);
            
            $department->head_teacher_name = $department->head ? $department->head->name : 'N/A';
            unset($department->head);

            return response()->json($department);

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
     * @OA\Delete(
     * path="/api/departments/{department}",
     * summary="Xóa một Khoa",
     * description="Xóa một khoa khỏi cơ sở dữ liệu.",
     * tags={"Departments"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="department",
     * in="path",
     * description="ID của khoa",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=204,
     * description="Xóa thành công (No Content)"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy khoa"
     * ),
     * @OA\Response(
     * response=409,
     * description="Không thể xóa (còn bộ môn/ngành học)"
     * )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $department = Department::findOrFail($id);

            if ($department->divisions()->count() > 0 || $department->majors()->count() > 0) {
                 return response()->json(['message' => 'Không thể xóa khoa khi vẫn còn bộ môn hoặc ngành học.'], 409);
            }

            $department->delete();

            return response()->json(null, 204);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy khoa'], 404);
        } catch (Exception $e) {
            Log::error("Lỗi DepartmentController@destroy: " . $e->getMessage());
            return response()->json(['message' => 'Lỗi máy chủ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     * path="/api/departments/{id}/details",
     * summary="Lấy chi tiết đầy đủ của 1 Khoa",
     * description="Lấy thông tin khoa, cùng danh sách Giảng viên, Ngành học, và Bộ môn.",
     * tags={"Departments"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID của khoa",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công. Trả về object lồng nhau."
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy khoa"
     * )
     * )
     */
    public function getDetails(string $id)
    {
        try {
            $department = Department::with(['head', 'teachers', 'majors', 'divisions'])
                ->withCount(['teachers', 'majors'])
                ->findOrFail($id);

            $details = [
                'department' => [
                    'id' => $department->id,
                    'code' => $department->code,
                    'name' => $department->name,
                    'description' => $department->description,
                    'head_id' => $department->head_id,
                    'head_teacher_name' => $department->head ? $department->head->name : 'N/A',
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