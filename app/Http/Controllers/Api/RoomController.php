<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA; // <-- THÊM DÒNG NÀY

/**
 * @OA\Tag(
 * name="Rooms",
 * description="Các API liên quan đến quản lý Phòng học"
 * )
 */
class RoomController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/rooms",
     * summary="Lấy danh sách phòng học (phân trang)",
     * description="Lấy danh sách tất cả phòng học, có phân trang (10 phòng/trang).",
     * tags={"Rooms"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="page",
     * in="query",
     * description="Số trang cần lấy",
     * required=false,
     * @OA\Schema(type="integer", example=1)
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công. Trả về đối tượng phân trang."
     * )
     * )
     */
    public function index(Request $request)
    {
        try {
            $rooms = Room::orderBy('updated_at', 'desc')->paginate(10);

            return response()->json($rooms);
        } catch (Exception $e) {
            Log::error("Lỗi RoomController@index: " . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/rooms",
     * summary="Thêm một phòng học mới",
     * tags={"Rooms"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * description="Dữ liệu phòng học mới",
     * @OA\JsonContent(
     * required={"name", "building", "floor", "capacity", "room_type"},
     * @OA\Property(property="name", type="string", example="Phòng 701"),
     * @OA\Property(property="building", type="string", example="Nhà A5"),
     * @OA\Property(property="floor", type="integer", example=7),
     * @OA\Property(property="capacity", type="integer", example=100),
     * @OA\Property(property="room_type", type="string", example="Phòng học lý thuyết"),
     * @OA\Property(property="status", type="string", nullable=true, example="Hoạt động"),
     * @OA\Property(property="description", type="string", nullable=true, example="Có máy chiếu, 2 điều hòa")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Tạo thành công"
     * ),
     * @OA\Response(
     * response=422,
     * description="Lỗi validation (dữ liệu không hợp lệ)"
     * )
     * )
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
     * @OA\Get(
     * path="/api/rooms/{room}",
     * summary="Lấy thông tin chi tiết một phòng học",
     * tags={"Rooms"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="room",
     * in="path",
     * description="ID của phòng học",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy phòng học"
     * )
     * )
     */
    public function show(string $id)
    {
        try {
            $room = Room::find($id);

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
     * @OA\Put(
     * path="/api/rooms/{room}",
     * summary="Cập nhật thông tin phòng học",
     * tags={"Rooms"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="room",
     * in="path",
     * description="ID của phòng học",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="Dữ liệu cập nhật của phòng học",
     * @OA\JsonContent(
     * required={"name", "building", "floor", "capacity", "room_type"},
     * @OA\Property(property="name", type="string", example="Phòng 701 (Đã sửa)"),
     * @OA\Property(property="building", type="string", example="Nhà A5"),
     * @OA\Property(property="floor", type="integer", example=7),
     * @OA\Property(property="capacity", type="integer", example=100),
     * @OA\Property(property="room_type", type="string", example="Phòng học lý thuyết"),
     * @OA\Property(property="status", type="string", nullable=true, example="Hoạt động"),
     * @OA\Property(property="description", type="string", nullable=true, example="Có máy chiếu, 2 điều hòa")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Cập nhật thành công"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy phòng học"
     * ),
     * @OA\Response(
     * response=422,
     * description="Lỗi validation (dữ liệu không hợp lệ)"
     * )
     * )
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
     * @OA\Delete(
     * path="/api/rooms/{room}",
     * summary="Xóa một phòng học",
     * tags={"Rooms"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="room",
     * in="path",
     * description="ID của phòng học",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=204,
     * description="Xóa thành công (No Content)"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy phòng học"
     * )
     * )
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