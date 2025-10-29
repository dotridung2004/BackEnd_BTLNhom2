<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LecturerController extends Controller
{
    // ... hàm index() giữ nguyên ...
    public function index()
    {
       try {
            $lecturers = User::where('role', 'teacher')
                                ->with('department')
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
            'user_code' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',
            'department_id' => 'required|integer|exists:departments,id',
            'phone_number' => 'nullable|string',
            // <<< SỬA Ở ĐÂY
            'date_of_birth' => 'nullable|date_format:d/m/Y', 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            // <<< SỬA Ở ĐÂY
             $dob = $request->date_of_birth ? \DateTime::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d') : null;

            $lecturer = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'user_code' => $request->user_code,
                'password' => Hash::make($request->password),
                'department_id' => $request->department_id,
                'phone_number' => $request->phone_number,
                'date_of_birth' => $dob, // <<< SỬA Ở ĐÂY
                'role' => 'teacher', 
            ]);

            $lecturer->load('department');
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
            $lecturer = User::with('department')->findOrFail($id);
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
            'user_code' => 'required|string|max:255|unique:users,user_code,' . $id,
            'department_id' => 'required|integer|exists:departments,id',
            'phone_number' => 'nullable|string',
            // <<< SỬA Ở ĐÂY
            'date_of_birth' => 'nullable|date_format:d/m/Y',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $lecturer = User::findOrFail($id);
            // <<< SỬA Ở ĐÂY
            $dob = $request->date_of_birth ? \DateTime::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d') : null;

            $lecturer->update([
                'name' => $request->name,
                'email' => $request->email,
                'user_code' => $request->user_code,
                'department_id' => $request->department_id,
                'phone_number' => $request->phone_number,
                'date_of_birth' => $dob, // <<< SỬA Ở ĐÂY
            ]);

            $lecturer->load('department');
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