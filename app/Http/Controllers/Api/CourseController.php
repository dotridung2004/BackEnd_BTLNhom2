<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA; // <-- THÊM DÒNG NÀY

/**
 * @OA\Tag(
 * name="Courses",
 * description="Các API liên quan đến quản lý Học Phần"
 * )
 */
class CourseController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/courses",
     * summary="Lấy danh sách Học Phần",
     * description="Tải danh sách tất cả học phần, sắp xếp theo thời gian cập nhật mới nhất. Có thể tìm kiếm.",
     * tags={"Courses"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="search",
     * in="query",
     * description="Tìm kiếm theo Mã (code) hoặc Tên (name) học phần",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công. Trả về một mảng các học phần."
     * )
     * )
     */
    public function index(Request $request)
    {
        $query = Course::with('department');

        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'like', '%' . $searchTerm . '%')
                    ->orWhere('name', 'like', '%' . $searchTerm . '%');
            });
        }
        
        $courses = $query->orderBy('updated_at', 'desc')->get();
        
        return response()->json($courses);
    }

    /**
     * @OA\Post(
     * path="/api/courses",
     * summary="Tạo học phần mới",
     * description="Xử lý thêm mới học phần.",
     * tags={"Courses"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * description="Dữ liệu học phần mới",
     * @OA\JsonContent(
     * required={"code", "name", "credits", "department_id", "subject_type"},
     * @OA\Property(property="code", type="string", example="IT4409"),
     * @OA\Property(property="name", type="string", example="Lập trình Web"),
     * @OA\Property(property="credits", type="integer", example=3),
     * @OA\Property(property="department_id", type="integer", example=1),
     * @OA\Property(property="subject_type", type="string", enum={"Bắt buộc", "Tùy chọn"}, example="Bắt buộc"),
     * @OA\Property(property="description", type="string", nullable=true, example="Mô tả về học phần...")
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
        try {
            $validatedData = $request->validate([
                'code' => 'required|string|max:20|unique:courses,code',
                'name' => 'required|string|max:255',
                'credits' => 'required|integer|min:1',
                'department_id' => 'required|exists:departments,id',
                'subject_type' => 'required|in:Bắt buộc,Tùy chọn',
                'description' => 'nullable|string',
            ]);

            $course = Course::create($validatedData);

            return response()->json($course->load('department'), 201);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi tạo học phần: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     * path="/api/courses/{course}",
     * summary="Lấy chi tiết một học phần",
     * description="Tải chi tiết một học phần.",
     * tags={"Courses"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="course",
     * in="path",
     * description="ID của học phần",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy học phần"
     * )
     * )
     */
    public function show(string $id)
    {
        try {
             $course = Course::with('department')->findOrFail($id);
             return response()->json($course);
        } catch (\Exception $e) {
             return response()->json(['message' => 'Không tìm thấy học phần.'], 404);
        }
    }

    /**
     * @OA\Put(
     * path="/api/courses/{course}",
     * summary="Cập nhật thông tin học phần",
     * description="Xử lý chỉnh sửa thông tin học phần.",
     * tags={"Courses"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="course",
     * in="path",
     * description="ID của học phần",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="Dữ liệu học phần cần cập nhật",
     * @OA\JsonContent(
     * required={"code", "name", "credits", "department_id", "subject_type"},
     * @OA\Property(property="code", type="string", example="IT4409"),
     * @OA\Property(property="name", type="string", example="Lập trình Web nâng cao"),
     * @OA\Property(property="credits", type="integer", example=3),
     * @OA\Property(property="department_id", type="integer", example=1),
     * @OA\Property(property="subject_type", type="string", enum={"Bắt buộc", "Tùy chọn"}, example="Bắt buộc"),
     * @OA\Property(property="description", type="string", nullable=true, example="Mô tả cập nhật...")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Cập nhật thành công"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy học phần"
     * ),
     * @OA\Response(
     * response=422,
     * description="Lỗi validation (dữ liệu không hợp lệ)"
     * )
     * )
     */
    public function update(Request $request, string $id)
    {
        try {
            $course = Course::findOrFail($id);

            $validatedData = $request->validate([
                'code' => 'required|string|max:20|unique:courses,code,' . $id,
                'name' => 'required|string|max:255',
                'credits' => 'required|integer|min:1',
                'department_id' => 'required|exists:departments,id',
                'subject_type' => 'required|in:Bắt buộc,Tùy chọn',
                'description' => 'nullable|string',
            ]);

            $course->update($validatedData);

            return response()->json($course->load('department'), 200);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi cập nhật học phần: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     * path="/api/courses/{course}",
     * summary="Xóa một học phần",
     * description="Xử lý xóa học phần.",
     * tags={"Courses"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="course",
     * in="path",
     * description="ID của học phần",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=204,
     * description="Xóa thành công (No Content)"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy học phần"
     * ),
     * @OA\Response(
     * response=500,
     * description="Lỗi xóa học phần"
     * )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $course = Course::findOrFail($id);
            $course->delete();
            
            return response()->json(null, 204);
            
        } catch (\Exception $e) {
            // Sửa lỗi 404 nếu không tìm thấy
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json(['message' => 'Không tìm thấy học phần.'], 404);
            }
            return response()->json(['message' => 'Lỗi xóa học phần: ' . $e->getMessage()], 500);
        }
    }
}