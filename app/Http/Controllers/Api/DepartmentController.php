<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use Exception;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            // Chỉ đếm divisions, KHÔNG đếm teachers trực tiếp vì không có users.department_id
            $departments = Department::withCount(['divisions'])->get();
            return response()->json($departments);
        } catch (Exception $e) {
            Log::error("Lỗi DepartmentController@index: " . $e->getMessage());
            return response()->json([], 500);
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

