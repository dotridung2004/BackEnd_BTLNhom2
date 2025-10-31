<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// 👇 1. Giữ lại tất cả các 'use' cần thiết
use App\Models\Room;
use Exception;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 👇 2. Gộp logic: Dùng try-catch (từ file 1) 
        //    và logic orderBy (từ file 2)
        try {
            // Lấy tất cả phòng học và sắp xếp theo tên (từ file 2)
            $rooms = Room::orderBy('name', 'asc')->get(); 
            
            return response()->json($rooms); // Trả về dữ liệu JSON

        } catch (Exception $e) {
            // Giữ lại việc ghi log lỗi (từ file 1)
            Log::error("Lỗi RoomController@index: " . $e->getMessage()); 
            return response()->json([], 500); // Trả về mảng rỗng khi có lỗi
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // (Sẽ làm sau)
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // (Sẽ làm sau)
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // (Sẽ làm sau)
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // (Sẽ làm sau)
    }
}