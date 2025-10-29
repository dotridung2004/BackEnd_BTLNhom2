<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Major;
use Exception; // 👈 Thêm
use Illuminate\Support\Facades\Log; // 👈 Thêm

class MajorController extends Controller
{
    /**
     * Hiển thị danh sách Ngành học.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try { // 👈 Bắt đầu try
             // Đảm bảo Model Major có hàm department(), teachers()
             // Và bảng users có khóa ngoại major_id
            $majors = Major::with('department')
                           ->withCount('teachers') 
                           ->get();

            return response()->json($majors);
        } catch (Exception $e) { // 👈 Bắt lỗi
            Log::error("Lỗi MajorController@index: " . $e->getMessage());
            return response()->json([], 500); // Trả về mảng rỗng khi lỗi
        } // 👈 Kết thúc catch
    }

    // Các hàm khác (store, show, update, destroy) có thể thêm sau
}
