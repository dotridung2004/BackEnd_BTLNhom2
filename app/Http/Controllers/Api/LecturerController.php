<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Đảm bảo bạn đã import User model

class LecturerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            // Giả định "Giảng viên" là User có role = 'teacher'
            $lecturers = User::where('role', 'teacher')
                             ->with('department') // 'department' là tên relationship trong User Model
                             ->orderBy('name', 'asc')
                             ->get();

            return response()->json($lecturers, 200);

        } catch (\Exception $e) {
            // Ghi log lỗi để debug nếu có vấn đề với database
            // Log::error('Lỗi khi lấy danh sách giảng viên: ' . $e->getMessage());
            return response()->json(['message' => 'Đã xảy ra lỗi khi truy vấn dữ liệu.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Chưa triển khai
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Chưa triển khai
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Chưa triển khai
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Chưa triển khai
    }
}
