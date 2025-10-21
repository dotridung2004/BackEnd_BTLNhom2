<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;

class AttendanceController extends Controller
{
    // public function index()
    // {
    //     // Sử dụng Cache::remember để lưu cache trong 60 giây
    //     $attendances = Cache::remember('attendances', 60, function () {
    //         return Attendance::with('category')->get();
    //     });

    //     return response()->json($attendances, 200);
    // }

    // /**
    //  * Tạo một sản phẩm mới
    //  */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => ['required', 'string', 'max:255', 'unique:products,name'],
    //         'price' => ['required', 'numeric', 'min:0'],
    //         'description' => ['required', 'string', 'max:500'],
    //         'category_id' => ['required', 'exists:categories,id'],
    //     ]);

    //     $product = Attendance::create($request->only(['name', 'price', 'description', 'category_id']));

    //     // Xóa cache cũ khi có thay đổi
    //     Cache::forget('products');

    //     $product->load('category');
    //     return response()->json($product, 201);
    // }

    // /**
    //  * Lấy thông tin một sản phẩm cụ thể kèm category
    //  */
    // public function show(Attendance $attendance)
    // {
    //     $attendance->load('category');
    //     return response()->json($attendance, 200);
    // }

    // /**
    //  * Cập nhật thông tin sản phẩm
    //  */
    // public function update(Request $request, Attendance $product)
    // {
    //     $request->validate([
    //         'name' => ['sometimes', 'string', 'max:255', Rule::unique('products', 'name')->ignore($product->id)],
    //         'price' => ['sometimes', 'numeric', 'min:0'],
    //         'description' => ['sometimes', 'string', 'max:500'],
    //         'category_id' => ['sometimes', 'exists:categories,id'],
    //     ]);

    //     $product->update($request->only(['name', 'price', 'description', 'category_id']));

    //     // Xóa cache khi có thay đổi
    //     Cache::forget('products');

    //     $product->load('category');
    //     return response()->json($product, 200);
    // }

    // /**
    //  * Xóa một sản phẩm
    //  */
    // public function destroy(Product $product)
    // {
    //     $product->delete();

    //     // Xóa cache khi có thay đổi
    //     Cache::forget('products');

    //     return response()->json(null, 204);
    // }
}
