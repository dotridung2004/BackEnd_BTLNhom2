<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassCourseAssignment;
use App\Models\ClassModel;
use App\Models\User;
use App\Models\Course;
use App\Models\Department;
use App\Models\Division;
use App\Models\Room;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 * name="Class Course Assignments",
 * description="Các API liên quan đến quản lý Lớp Học Phần (Quản trị viên)"
 * )
 */
class ClassCourseAssignmentController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/class-courses",
     * summary="Lấy danh sách Lớp Học Phần (cho Admin Panel)",
     * tags={"Class Course Assignments"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Thành công. Trả về một mảng các lớp học phần."
     * )
     * )
     */
    public function index()
    {
        $classCourses = ClassCourseAssignment::with([
            'classModel:id,name,semester',
            'teacher:id,name',
            'course:id,name,code,department_id',
            'course.department:id,name',
            'division:id,name',
            'room:id,name'
        ])
            ->orderBy('updated_at', 'desc')
            ->get();

        $data = $classCourses->map(fn ($cca) => $this->formatClassCourse($cca));

        return response()->json($data);
    }

    /**
     * @OA\Get(
     * path="/api/class-courses/form-data",
     * summary="Lấy dữ liệu cho Form Thêm/Sửa LHP",
     * description="Lấy danh sách teachers, courses, departments, divisions, rooms, semesters để điền vào các dropdown/select.",
     * tags={"Class Course Assignments"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Thành công. Trả về 1 object chứa các mảng dữ liệu."
     * )
     * )
     */
    public function getFormData()
    {
        $teachers = User::where('role', 'teacher')->get();

        $courses = Course::with('department:id,name')->get()->map(function ($course) {
            return [
                'id' => $course->id,
                'name' => $course->name,
                'code' => $course->code ?? 'N/A',
                'credits' => $course->credits ?? 3,
                'type' => $course->type ?? 'N/A',
                'departmentName' => $course->department->name ?? 'N/A'
            ];
        });

        $departments = Department::withCount(['teachers', 'majors'])->get()->map(function ($dept) {
            return [
                'id' => $dept->id,
                'code' => $dept->code,
                'name' => $dept->name,
                'teacherCount' => $dept->teachers_count,
                'majorsCount' => $dept->majors_count,
                'courseCount' => 0,
            ];
        });

        $divisions = Division::with('department:id,name')
            ->withCount(['teachers', 'courses'])
            ->get()
            ->map(function ($div) {
                return [
                    'id' => $div->id,
                    'code' => $div->code,
                    'name' => $div->name,
                    'departmentName' => $div->department->name ?? 'N/A',
                    'teacherCount' => $div->teachers_count,
                    'courseCount' => $div->courses_count,
                ];
            });

        $rooms = Room::select('id', 'name')->get();

        $semesterData = DB::table('classes')
            ->select('semester')
            ->distinct()
            ->whereNotNull('semester')
            ->get();

        $semesters = $semesterData->map(function ($item) {
            $parts = explode('_', $item->semester, 2);
            return count($parts) === 2
                ? "HK{$parts[0]} {$parts[1]}"
                : $item->semester;
        })->unique()->values();

        return response()->json([
            'teachers' => $teachers,
            'courses' => $courses,
            'departments' => $departments,
            'divisions' => $divisions,
            'semesters' => $semesters,
            'rooms' => $rooms,
        ]);
    }

    /**
     * @OA\Get(
     * path="/api/class-courses/{id}/details",
     * summary="Lấy chi tiết một lớp học phần (cho màn hình Chi tiết)",
     * description="Trò về thông tin LHP, danh sách sinh viên, và lịch học.",
     * tags={"Class Course Assignments"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * description="ID của ClassCourseAssignment (LHP)",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy LHP"
     * )
     * )
     */
    public function showDetails(string $id)
    {
        try {
            $classCourse = ClassCourseAssignment::with([
                'teacher:id,name',
                'course:id,name,code,department_id',
                'course.department:id,name',
                'classModel:id,name,semester',
                'division:id,name',
                'room:id,name',
                'students',
                'schedules',
                'schedules.room:id,name',
            ])->findOrFail($id);

            $formattedData = [
                'class_course' => $this->formatClassCourse($classCourse),
                'students' => $classCourse->students->map(fn ($student) => [
                    'id' => $student->id,
                    'name' => $student->name ?? 'N/A',
                    'email' => $student->email ?? 'N/A',
                ]),
                'schedules' => $classCourse->schedules,
            ];

            return response()->json($formattedData);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy lớp học phần'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi máy chủ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/class-courses",
     * summary="Tạo Lớp học phần MỚI",
     * tags={"Class Course Assignments"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * description="Dữ liệu để tạo LHP mới",
     * @OA\JsonContent(
     * required={"name", "department_id", "semester", "course_id", "teacher_id"},
     * @OA\Property(property="name", type="string", example="Lớp Lập trình Web - Nhóm 1"),
     * @OA\Property(property="department_id", type="integer", example=1),
     * @OA\Property(property="semester", type="string", example="HK1 2024-2025"),
     * @OA\Property(property="course_id", type="integer", example=1),
     * @OA\Property(property="teacher_id", type="integer", example=1),
     * @OA\Property(property="division_id", type="integer", example=1, nullable=true),
     * @OA\Property(property="room_id", type="integer", example=1, nullable=true)
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Tạo thành công"
     * ),
     * @OA\Response(
     * response=422,
     * description="Lỗi validation (ví dụ: Tên lớp đã tồn tại)"
     * )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'semester' => 'required|string',
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => 'required|exists:users,id',
            'division_id' => 'nullable|exists:divisions,id',
            'room_id' => 'nullable|exists:rooms,id',
        ]);

        $semesterString = $validated['semester'];
        $semesterDbFormat = Str::replaceFirst(' ', '_', Str::replaceFirst('HK', '', $semesterString));

        $existingClass = ClassModel::where('name', $validated['name'])
            ->where('semester', $semesterDbFormat)
            ->exists();

        if ($existingClass) {
            return response()->json([
                'message' => 'Tên lớp học phần đã tồn tại trong học kỳ này. Vui lòng nhập lại.'
            ], 422);
        }

        return DB::transaction(function () use ($validated, $semesterDbFormat) {
            $classModel = ClassModel::create([
                'name' => $validated['name'],
                'semester' => $semesterDbFormat,
                'department_id' => $validated['department_id']
            ]);

            $classCourse = ClassCourseAssignment::create([
                'class_id' => $classModel->id,
                'course_id' => $validated['course_id'],
                'teacher_id' => $validated['teacher_id'],
                'division_id' => $validated['division_id'] ?? null,
                'room_id' => $validated['room_id'] ?? null,
            ]);

            $newClassCourse = $this->formatClassCourse($classCourse->load([
                'classModel',
                'teacher',
                'course:id,name,code,department_id',
                'course.department',
                'division',
                'room'
            ]));

            return response()->json($newClassCourse, 201);
        });
    }

    /**
     * @OA\Put(
     * path="/api/class-courses/{class_course}",
     * summary="Cập nhật Lớp học phần",
     * tags={"Class Course Assignments"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="class_course",
     * in="path",
     * description="ID của ClassCourseAssignment (LHP)",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="Dữ liệu để cập nhật LHP",
     * @OA\JsonContent(
     * required={"name", "department_id", "semester", "course_id", "teacher_id"},
     * @OA\Property(property="name", type="string", example="Lớp Lập trình Web - Nhóm 1 (đã sửa)"),
     * @OA\Property(property="department_id", type="integer", example=1),
     * @OA\Property(property="semester", type="string", example="HK1 2024-2025"),
     * @OA\Property(property="course_id", type="integer", example=1),
     * @OA\Property(property="teacher_id", type="integer", example=1),
     * @OA\Property(property="division_id", type="integer", example=1, nullable=true),
     * @OA\Property(property="room_id", type="integer", example=1, nullable=true)
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Cập nhật thành công"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy LHP"
     * ),
     * @OA\Response(
     * response=422,
     * description="Lỗi validation (ví dụ: Tên lớp đã tồn tại)"
     * )
     * )
     */
    public function update(Request $request, string $id)
    {
        // QUAN TRỌNG: Dù route là {class_course}, Laravel vẫn đủ thông minh
        // để truyền $id vào đây. Code của bạn VẪN ĐÚNG.
        $classCourse = ClassCourseAssignment::findOrFail($id);
        $classModel = $classCourse->classModel;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'semester' => 'required|string',
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => 'required|exists:users,id',
            'division_id' => 'nullable|exists:divisions,id',
            'room_id' => 'nullable|exists:rooms,id',
        ]);

        $semesterString = $validated['semester'];
        $semesterDbFormat = Str::replaceFirst(' ', '_', Str::replaceFirst('HK', '', $semesterString));

        $existingClass = ClassModel::where('name', $validated['name'])
            ->where('semester', $semesterDbFormat)
            ->where('id', '!=', $classModel->id)
            ->exists();

        if ($existingClass) {
            return response()->json([
                'message' => 'Tên lớp học phần đã tồn tại trong học kỳ này. Vui lòng nhập lại.'
            ], 422);
        }

        return DB::transaction(function () use ($validated, $classCourse, $classModel, $semesterDbFormat) {
            $classModel->update([
                'name' => $validated['name'],
                'department_id' => $validated['department_id'],
                'semester' => $semesterDbFormat,
            ]);

            $classCourse->update([
                'course_id' => $validated['course_id'],
                'teacher_id' => $validated['teacher_id'],
                'division_id' => $validated['division_id'] ?? null,
                'room_id' => $validated['room_id'] ?? null,
            ]);

            $updatedClassCourse = $this->formatClassCourse($classCourse->load([
                'classModel',
                'teacher',
                'course:id,name,code,department_id',
                'course.department',
                'division',
                'room'
            ]));

            return response()->json($updatedClassCourse);
        });
    }

    /**
     * @OA\Delete(
     * path="/api/class-courses/{class_course}",
     * summary="Xóa lớp học phần",
     * tags={"Class Course Assignments"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="class_course",
     * in="path",
     * description="ID của ClassCourseAssignment (LHP)",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=204,
     * description="Xóa thành công (No Content)"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy LHP"
     * )
     * )
     */
    public function destroy(string $id)
    {
        // Tương tự hàm update, code của bạn VẪN ĐÚNG
        try {
            $classCourse = ClassCourseAssignment::findOrFail($id);
            $classCourse->delete();
            return response()->json(null, 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy'], 404);
        }
    }

    /**
     * @OA\Get(
     * path="/api/class-courses/{class_course}",
     * summary="Lấy 1 LHP (dữ liệu cơ bản - RESTful)",
     * description="Hàm show() gốc của apiResource, trả về dữ liệu thô.",
     * tags={"Class Course Assignments"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="class_course",
     * in="path",
     * description="ID của ClassCourseAssignment (LHP)",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy"
     * )
     * )
     */
    public function show(string $id)
    {
        // Tương tự hàm update, code của bạn VẪN ĐÚNG
        return response()->json(ClassCourseAssignment::find($id));
    }

    /**
     * @OA\Get(
     * path="/api/registered-courses",
     * summary="Lấy DS LHP + số lượng sinh viên",
     * description="Dùng cho route /registered-courses, lấy LHP và đếm số SV đã đăng ký.",
     * tags={"Class Course Assignments"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Thành công"
     * )
     * )
     */
    public function indexWithStudentCount()
    {
        $assignments = ClassCourseAssignment::with([
            'teacher:id,name',
            'course:id,name,code,department_id',
            'course.department:id,name',
            'classModel:id,name,semester',
            'division:id,name',
            'room:id,name'
        ])
            ->withCount('students')
            ->orderBy('updated_at', 'desc')
            ->get();

        $data = $assignments->map(function ($cca) {
            $formatted = $this->formatClassCourse($cca);
            $formatted['students_count'] = $cca->students_count;
            return $formatted;
        });

        return response()->json($data);
    }

    /**
     * Hàm private: Định dạng dữ liệu ClassCourse
     */
    private function formatClassCourse($cca)
    {
        $semesterDbFormat = $cca->classModel->semester ?? 'N/A_N/A';
        $parts = explode('_', $semesterDbFormat, 2);

        $semesterString = count($parts) === 2
            ? "HK{$parts[0]} {$parts[1]}"
            : 'HK1 1970-1971';

        return [
            'id' => $cca->id,
            'name' => $cca->classModel->name ?? 'N/A',
            'teacher' => ['name' => $cca->teacher->name ?? 'N/A'],
            'course' => [
                'name' => $cca->course->name ?? 'N/A',
                'code' => $cca->course->code ?? 'N/A',
                'department' => ['name' => $cca->course->department->name ?? 'N/A'],
            ],
            'semester' => $semesterString,
            'division' => ['name' => $cca->division->name ?? 'N/A'],
            'room' => ['name' => $cca->room->name ?? 'N/A']
        ];
    }
}