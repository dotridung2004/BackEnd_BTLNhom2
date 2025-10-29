<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// 👇 1. Thêm các dòng Use cần thiết
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
        // 👇 2. Thêm khối try-catch và logic lấy dữ liệu
        try {
            $rooms = Room::all(); // Lấy tất cả phòng học
            // Hoặc bạn có thể thêm ->with(...) nếu Room có quan hệ cần tải
            // Ví dụ: $rooms = Room::with('buildingInfo')->get();

            return response()->json($rooms); // Trả về dữ liệu JSON
        } catch (Exception $e) {
            Log::error("Lỗi RoomController@index: " . $e->getMessage()); // Ghi log lỗi
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

