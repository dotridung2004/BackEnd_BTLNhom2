<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LecturerController extends Controller
{
    public function index()
    {
       try {
            $lecturers = User::where('role', 'teacher')
                                 ->with('department') // Đã có eager loading, rất tốt
                                 ->orderBy('name', 'asc')
                                 ->get();
            return response()->json($lecturers, 200);
       } catch (\Exception $e) {
            return response()->json(['message' => 'Đã xảy ra lỗi khi truy vấn dữ liệu.'], 500);
       }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            // <<< DÒNG VALIDATE user_code ĐÃ BỊ XÓA
            'password' => 'required|string|min:6',
            'department_id' => 'required|integer|exists:departments,id',
            'phone_number' => 'nullable|string',
            'date_of_birth' => 'nullable|date_format:d/m/Y', // Validate đúng định dạng dd/mm/yyyy
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
             // Chuyển đổi sang Y-m-d để lưu vào DB
             $dob = $request->date_of_birth ? \DateTime::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d') : null;

            $lecturer = User::create([
                'name' => $request->name,
                'first_name' => $request->name, 
                'last_name' => '', 
                'email' => $request->email,
                // <<< DÒNG user_code ĐÃ BỊ XÓA
                'password' => Hash::make($request->password),
                'department_id' => $request->department_id,
                'phone_number' => $request->phone_number,
                'date_of_birth' => $dob, // Lưu định dạng Y-m-d
                'role' => 'teacher', 
            ]);

            $lecturer->load('department'); // Load quan hệ để trả về JSON đầy đủ
            return response()->json($lecturer, 201); 

        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi thêm giảng viên: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $lecturer = User::with('department')->findOrFail($id); // Đã có eager loading
            return response()->json($lecturer, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy giảng viên'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
         $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
             // <<< DÒNG VALIDATE user_code ĐÃ BỊ XÓA
            'department_id' => 'required|integer|exists:departments,id',
            'phone_number' => 'nullable|string',
            'date_of_birth' => 'nullable|date_format:d/m/Y', // Validate đúng định dạng dd/mm/yyyy
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $lecturer = User::findOrFail($id);
            // Chuyển đổi sang Y-m-d để lưu vào DB
            $dob = $request->date_of_birth ? \DateTime::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d') : null;

            $lecturer->update([
                'name' => $request->name,
                'first_name' => $request->name, 
                'email' => $request->email,
                // <<< DÒNG user_code ĐÃ BỊ XÓA
                'department_id' => $request->department_id,
                'phone_number' => $request->phone_number,
                'date_of_birth' => $dob, // Lưu định dạng Y-m-d
            ]);

            $lecturer->load('department'); // Load quan hệ để trả về JSON đầy đủ
            return response()->json($lecturer, 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi cập nhật giảng viên: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $lecturer = User::findOrFail($id);
            $lecturer->delete();
            return response()->json(['message' => 'Xóa giảng viên thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi xóa giảng viên'], 500);
        }
    }
}