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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // Thêm Str helper để xử lý chuỗi

class ClassCourseAssignmentController extends Controller
{
    /**
     * Lấy danh sách Lớp Học Phần
     */
    public function index()
    {
        $classCourses = ClassCourseAssignment::with([
            'classModel:id,name,semester', // Chỉ lấy cột semester
            'teacher:id,name', 
            'course:id,name,department_id',
            'course.department:id,name',
            'division:id,name' 
        ])
        ->orderBy('updated_at', 'desc') 
        ->get();

        $data = $classCourses->map(function ($cca) {
            return $this->formatClassCourse($cca);
        });

        return response()->json($data);
    }

    /**
     * Lấy dữ liệu cho Form Thêm/Sửa
     */
    public function getFormData()
    {
        // 1. Teachers
        $teachers = User::where('role', 'teacher')->get();

        // 2. Courses
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

        // 3. Departments
        $departments = Department::withCount(['teachers', 'majors'])->get()->map(function ($dept) {
            return [
                'id' => $dept->id,
                'code' => $dept->code,
                'name' => $dept->name,
                'teacherCount' => $dept->teachers_count,
                'majorsCount' => $dept->majors_count,
                'courseCount' => 0, // Tạm thời
            ];
        });
        
        // 4. Divisions
        $divisions = Division::with('department:id,name')->withCount(['teachers', 'courses'])->get()->map(function ($div) {
             return [
                'id' => $div->id,
                'code' => $div->code,
                'name' => $div->name,
                'departmentName' => $div->department->name ?? 'N/A', 
                'teacherCount' => $div->teachers_count,
                'courseCount' => $div->courses_count,
             ];
        });

        // ==========================================================
        // ✅ ĐÂY LÀ PHẦN ĐÚNG:
        // Lấy học kỳ động từ CSDL (bảng 'classes')
        // ==========================================================
        $semesterData = DB::table('classes')
                          ->select('semester')
                          ->distinct()
                          ->whereNotNull('semester')
                          ->get();
        
        $semesters = $semesterData->map(function ($item) {
            // Chuyển đổi '1_2025-2026' -> 'HK1 2025-2026'
            $parts = explode('_', $item->semester, 2);
            if (count($parts) == 2) {
                return "HK{$parts[0]} {$parts[1]}";
            }
            return $item->semester; // Trả về nguyên bản nếu không đúng format
        })->unique()->values();
        // ==========================================================


        return response()->json([
            'teachers' => $teachers,
            'courses' => $courses,
            'departments' => $departments,
            'divisions' => $divisions,
            'semesters' => $semesters,
        ]);
    }

    /**
     * Lấy chi tiết một lớp học phần
     */
    public function showDetails(string $id)
    {
        try {
            $classCourse = ClassCourseAssignment::with([
                'teacher:id,name',
                'course:id,name,department_id',
                'course.department:id,name',
                'classModel:id,name,semester', // Chỉ lấy cột semester
                'division:id,name', 
                'students',
                'schedules', 
                'schedules.room:id,name',
                'schedules.classCourseAssignment.teacher:id,name',
                'schedules.classCourseAssignment.classModel:id,name',
                'schedules.classCourseAssignment.course:id,name',
            ])->findOrFail($id);
            $formattedData = [
                'class_course' => $this->formatClassCourse($classCourse),
                'students' => $classCourse->students->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->name ?? 'N/A',
                        'first_name' => $student->first_name ?? '',
                        'last_name' => $student->last_name ?? '',
                        'email' => $student->email ?? 'N/A',
                        'status' => $student->status ?? 'inactive',
                        'role' => $student->role ?? 'student',
                        'phone_number' => $student->phone_number ?? 'N/A',
                    ];
                }),
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
     * Store: Tạo Lớp học phần MỚI
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'semester' => 'required|string', // "HK1 2025-2026"
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => 'required|exists:users,id',
            'division_id' => 'nullable|exists:divisions,id',
        ]);
        
        return DB::transaction(function () use ($validated) {
            
            // Chuyển "HK1 2025-2026" -> "1_2025-2026"
            $semesterString = $validated['semester'];
            $semesterDbFormat = Str::replaceFirst(' ', '_', Str::replaceFirst('HK', '', $semesterString)); // "1_2025-2026"

            $classModel = ClassModel::firstOrCreate(
                [
                    'name' => $validated['name'],
                    'semester' => $semesterDbFormat, // <-- Sửa
                ],
                [
                    'department_id' => $validated['department_id']
                ]
            );
            $classCourse = ClassCourseAssignment::create([
                'class_id' => $classModel->id,
                'course_id' => $validated['course_id'],
                'teacher_id' => $validated['teacher_id'],
                'division_id' => $validated['division_id'],
            ]);

            $newClassCourse = $this->formatClassCourse($classCourse->load(['classModel', 'teacher', 'course.department', 'division']));

            return response()->json($newClassCourse, 201);
        });
    }

    /**
     * Update: Cập nhật Lớp học phần
     */
    public function update(Request $request, string $id)
    {
        $classCourse = ClassCourseAssignment::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'semester' => 'required|string', // "HK1 2025-2026"
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => 'required|exists:users,id',
            'division_id' => 'nullable|exists:divisions,id',
        ]);

        return DB::transaction(function () use ($validated, $classCourse) {

            // Chuyển "HK1 2025-2026" -> "1_2025-2026"
            $semesterString = $validated['semester'];
            $semesterDbFormat = Str::replaceFirst(' ', '_', Str::replaceFirst('HK', '', $semesterString)); // "1_2025-2026"

            $classModel = $classCourse->classModel;
            $classModel->update([
                'name' => $validated['name'],
                'department_id' => $validated['department_id'],
                'semester' => $semesterDbFormat, // <-- Sửa
            ]);
            $classCourse->update([
                'course_id' => $validated['course_id'],
                'teacher_id' => $validated['teacher_id'],
                'division_id' => $validated['division_id'],
            ]);
            
            $updatedClassCourse = $this->formatClassCourse($classCourse->load(['classModel', 'teacher', 'course.department', 'division']));

            return response()->json($updatedClassCourse);
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $classCourse = ClassCourseAssignment::findOrFail($id);
            $classCourse->delete();
            return response()->json(null, 204); 
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy'], 404);
        }
    }

    /**
     * Hàm helper để định dạng ClassCourse trả về
     */
    private function formatClassCourse($cca)
    {
        // Chuyển '1_2025-2026' -> 'HK1 2025-2026'
        $semesterDbFormat = $cca->classModel->semester ?? 'N/A_N/A';
        $parts = explode('_', $semesterDbFormat, 2);
        
        $semesterString = "HK1 1970-1971"; // Giá trị mặc định nếu lỗi
        if (count($parts) == 2) {
             $semesterString = "HK{$parts[0]} {$parts[1]}"; // (Kết quả: "HK1 2025-2026")
        }


        return [
            'id'        => $cca->id,
            'name'      => $cca->classModel->name ?? 'N/A',
            'teacher'   => ['name' => $cca->teacher->name ?? 'N/A'],
            'course'    => [
                'name'       => $cca->course->name ?? 'N/A', 
                'department' => ['name' => $cca->course->department->name ?? 'N/A'],
            ],
            'semester'   => $semesterString, 
            'division'   => ['name' => $cca->division->name ?? 'N/A'] 
        ];
    }

     /**
     * Hàm show(string $id) gốc - không dùng cho 'showDetails'
     */
    public function show(string $id)
    {
        return response()->json(ClassCourseAssignment::find($id));
    }

    /**
     * Hàm indexWithStudentCount - Đã được map() cho Flutter
     */
    public function indexWithStudentCount()
    {
        $assignments = ClassCourseAssignment::with([
            'teacher:id,name',
            'course:id,name,department_id',
            'course.department:id,name',
            'classModel:id,name,semester', // Chỉ lấy cột semester
            'division:id,name' 
        ])
        ->withCount('students') 
        ->orderBy('updated_at', 'desc') 
        ->get();

        $data = $assignments->map(function ($cca) {
            $formatted = $this->formatClassCourse($cca);
            $formatted['students_count'] = $cca->students_count; // Thêm số lượng SV
            return $formatted;
        });

        return response()->json($data);
    }
}