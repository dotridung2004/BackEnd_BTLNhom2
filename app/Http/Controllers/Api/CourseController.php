<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     * Tải danh sách tất cả học phần, sắp xếp theo thời gian cập nhật mới nhất.
     */
    public function index(Request $request)
    {
        // Khởi tạo query Builder
        $query = Course::with('department');

        // THÊM: Logic tìm kiếm nếu có tham số 'search' được gửi lên từ client
        if ($request->has('search')) {
            $searchTerm = $request->get('search');
            // Tìm kiếm theo code hoặc name của học phần
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'like', '%' . $searchTerm . '%')
                    ->orWhere('name', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Sắp xếp theo updated_at mới nhất (giảm dần)
        $courses = $query->orderBy('updated_at', 'desc')->get();
        
        return response()->json($courses);
    }

    /**
     * Store a newly created resource in storage.
     * Xử lý thêm mới học phần.
     */
    public function store(Request $request)
    {
        try {
            // 1. Kiểm tra dữ liệu đầu vào (Validation)
            $validatedData = $request->validate([
                'code' => 'required|string|max:20|unique:courses,code', // Mã học phần phải là duy nhất
                'name' => 'required|string|max:255',
                'credits' => 'required|integer|min:1',
                'department_id' => 'required|exists:departments,id', // Đảm bảo Khoa tồn tại
                'subject_type' => 'required|in:Bắt buộc,Tùy chọn', // Chỉ chấp nhận 2 giá trị này
                'description' => 'nullable|string',
            ]);

            // 2. Lưu học phần mới
            $course = Course::create($validatedData);

            // 3. Trả về object đã được tạo, kèm theo thông tin Department
            return response()->json($course->load('department'), 201);

        } catch (ValidationException $e) {
            // Trả về lỗi 422 Unprocessable Entity nếu validation thất bại
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi tạo học phần: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     * Tải chi tiết một học phần.
     */
    public function show(string $id)
    {
        try {
             // Luôn sử dụng with('department') để tải đầy đủ thông tin
             $course = Course::with('department')->findOrFail($id);
             return response()->json($course);
        } catch (\Exception $e) {
             return response()->json(['message' => 'Không tìm thấy học phần.'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     * Xử lý chỉnh sửa thông tin học phần.
     */
    public function update(Request $request, string $id)
    {
        try {
            $course = Course::findOrFail($id);

            // 1. Kiểm tra dữ liệu đầu vào (Validation)
            $validatedData = $request->validate([
                // Mã học phần phải là duy nhất, nhưng loại trừ chính nó
                'code' => 'required|string|max:20|unique:courses,code,' . $id,
                'name' => 'required|string|max:255',
                'credits' => 'required|integer|min:1',
                'department_id' => 'required|exists:departments,id',
                'subject_type' => 'required|in:Bắt buộc,Tùy chọn',
                'description' => 'nullable|string',
            ]);

            // 2. Cập nhật
            $course->update($validatedData);

            // 3. Trả về object đã được cập nhật
            return response()->json($course->load('department'), 200);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi cập nhật học phần: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * Xử lý xóa học phần.
     */
    public function destroy(string $id)
    {
        try {
            $course = Course::findOrFail($id);
            $course->delete();
            
            // Trả về mã 204 No Content cho thao tác xóa thành công
            return response()->json(null, 204);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi xóa học phần: ' . $e->getMessage()], 500);
        }
    }
}
