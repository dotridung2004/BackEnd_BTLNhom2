<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Major;
use App\Models\Department;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Majors",
 *     description="Các API liên quan đến quản lý Ngành học"
 * )
 */
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
            'updated_at' => optional($major->updated_at)->toIso8601String(),
        ];
    }

    /**
     * @OA\Get(
     *     path="/api/majors",
     *     summary="Lấy danh sách Ngành học",
     *     description="Hiển thị danh sách Ngành học (cho Bảng dữ liệu), đã sắp xếp.",
     *     tags={"Majors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Thành công. Trả về một mảng các ngành học."
     *     )
     * )
     */
    public function index()
    {
        try {
            $majors = Major::with('department')
                ->withCount('teachers')
                ->orderByDesc('updated_at')
                ->get();

            $mappedMajors = $majors->map(function ($major) {
                return [
                    'id' => $major->id,
                    'code' => $major->code,
                    'name' => $major->name,
                    'departmentName' => $major->department?->name ?? 'N/A',
                    'teachers_count' => $major->teachers_count,
                    'updated_at' => optional($major->updated_at)->toIso8601String(),
                ];
            });

            return response()->json($mappedMajors);
        } catch (Exception $e) {
            Log::error("Lỗi MajorController@index: " . $e->getMessage());
            return response()->json(['message' => 'Lỗi server'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/majors",
     *     summary="Tạo Ngành học mới",
     *     description="Lưu một Ngành học mới vào database.",
     *     tags={"Majors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dữ liệu của ngành học mới",
     *         @OA\JsonContent(
     *             required={"ma_nganh", "ten_nganh", "khoa_id"},
     *             @OA\Property(property="ma_nganh", type="string", example="7480201"),
     *             @OA\Property(property="ten_nganh", type="string", example="Công nghệ Thông tin"),
     *             @OA\Property(property="khoa_id", type="integer", example=1),
     *             @OA\Property(property="mo_ta", type="string", nullable=true, example="Mô tả về ngành học...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tạo thành công"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ (Validation error)"
     *     )
     * )
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
                'description' => $validatedData['mo_ta'] ?? null,
            ]);

            return response()->json($this->mapMajorToJson($major), 201);
        } catch (Exception $e) {
            Log::error("Lỗi MajorController@store: " . $e->getMessage());
            return response()->json(['message' => 'Không thể tạo ngành học: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/majors/{major}",
     *     summary="Lấy chi tiết 1 Ngành học",
     *     description="Hiển thị chi tiết một Ngành học, bao gồm cả danh sách giảng viên.",
     *     tags={"Majors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="major",
     *         in="path",
     *         description="ID của ngành học",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Thành công"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy ngành học"
     *     )
     * )
     */
    public function show(Major $major)
    {
        try {
            $major->load(['department', 'teachers']);

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
                    'ten_khoa' => $major->department->name,
                ] : null,
                'teachers_count' => $major->teachers->count(),
                'teachers' => $teachersList,
            ]);
        } catch (Exception $e) {
            Log::error("Lỗi MajorController@show: " . $e->getMessage());
            return response()->json(['message' => 'Không tìm thấy dữ liệu.'], 404);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/majors/{major}",
     *     summary="Cập nhật Ngành học",
     *     description="Cập nhật thông tin một Ngành học đã tồn tại.",
     *     tags={"Majors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="major",
     *         in="path",
     *         description="ID của ngành học",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dữ liệu cập nhật của ngành học",
     *         @OA\JsonContent(
     *             required={"ma_nganh", "ten_nganh", "khoa_id"},
     *             @OA\Property(property="ma_nganh", type="string", example="7480201"),
     *             @OA\Property(property="ten_nganh", type="string", example="Công nghệ Thông tin (Updated)"),
     *             @OA\Property(property="khoa_id", type="integer", example=1),
     *             @OA\Property(property="mo_ta", type="string", nullable=true, example="Mô tả đã cập nhật...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thành công"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy ngành học"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ (Validation error)"
     *     )
     * )
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
                'description' => $validatedData['mo_ta'] ?? null,
            ]);

            return response()->json($this->mapMajorToJson($major));
        } catch (Exception $e) {
            Log::error("Lỗi MajorController@update: " . $e->getMessage());
            return response()->json(['message' => 'Không thể cập nhật: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/majors/{major}",
     *     summary="Xóa Ngành học",
     *     description="Xóa một Ngành học.",
     *     tags={"Majors"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="major",
     *         in="path",
     *         description="ID của ngành học",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Xóa thành công (No Content)"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy ngành học"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Không thể xóa (còn ràng buộc dữ liệu)"
     *     )
     * )
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
