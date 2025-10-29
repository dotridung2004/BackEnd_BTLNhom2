<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Division;
use Exception; // 👈 Thêm
use Illuminate\Support\Facades\Log; // 👈 Thêm

class DivisionController extends Controller
{
    /**
     * Hiển thị danh sách Bộ môn.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try { // 👈 Bắt đầu try
            // Đảm bảo Model Division có hàm department(), teachers(), courses()
            // Và các bảng liên quan có khóa ngoại đúng (users.division_id, courses.division_id)
            $divisions = Division::with('department')
                                 ->withCount(['teachers', 'courses'])
                                 ->get();
            
            return response()->json($divisions);
        } catch (Exception $e) { // 👈 Bắt lỗi
            Log::error("Lỗi DivisionController@index: " . $e->getMessage());
            return response()->json([], 500); // Trả về mảng rỗng khi lỗi
        } // 👈 Kết thúc catch
    }

    // Các hàm khác (store, show, update, destroy) có thể thêm sau
}
