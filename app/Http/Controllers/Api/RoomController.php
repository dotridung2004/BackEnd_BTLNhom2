<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RoomController extends Controller
{
    /**
     * Lấy danh sách tất cả phòng học.
     */
    public function index(Request $request)
    {
        try {
            // (SỬA) Sắp xếp theo 'updated_at' (mới nhất lên đầu)
            $rooms = Room::orderBy('updated_at', 'desc')->get(); 
            
            return response()->json($rooms);
        } catch (Exception $e) {
            Log::error("Lỗi RoomController@index: " . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Thêm một phòng học mới.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:rooms',
            'building' => 'required|string|max:50',
            'floor' => 'required|integer',
            'capacity' => 'required|integer',
            'room_type' => 'required|string|max:100',
            'status' => 'sometimes|string|max:50',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $room = Room::create($request->all());
            return response()->json($room, 201);
        } catch (Exception $e) {
            Log::error("Lỗi RoomController@store: " . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Lấy thông tin chi tiết một phòng học.
     */
    public function show(string $id)
    {
        try {
            $room = Room::with('schedules')->find($id);

            if (!$room) {
                return response()->json(['message' => 'Không tìm thấy phòng học'], 404);
            }

            return response()->json($room);
        } catch (Exception $e) {
            Log::error("Lỗi RoomController@show: " . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Cập nhật thông tin phòng học.
     */
    public function update(Request $request, string $id)
    {
        $room = Room::find($id);
        if (!$room) {
            return response()->json(['message' => 'Không tìm thấy phòng học'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:rooms,name,' . $room->id,
            'building' => 'required|string|max:50',
            'floor' => 'required|integer',
            'capacity' => 'required|integer',
            'room_type' => 'required|string|max:100',
            'status' => 'sometimes|string|max:50',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $room->update($request->all());
            return response()->json($room); 
        } catch (Exception $e) {
            Log::error("Lỗi RoomController@update: " . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Xóa một phòng học.
     */
    public function destroy(string $id)
    {
        try {
            $room = Room::find($id);

            if (!$room) {
                return response()->json(['message' => 'Không tìm thấy phòng học'], 404);
            }
            
            $room->delete();
            return response()->json(null, 204);

        } catch (Exception $e) {
            Log::error("Lỗi RoomController@destroy: " . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}