<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTugasRequest;
use App\Http\Requests\UpdateTugasRequest;
use App\Models\Tugas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TugasController extends Controller
{
    /**
     * Display a listing of the authenticated user's tugas.
     * Returns HTTP 200.
     */
    public function index(Request $request): JsonResponse
    {
        $tugas = $request->user()->tugas()->latest()->get();

        return response()->json([
            'data' => $tugas,
        ], 200);
    }

    /**
     * Store a newly created tugas.
     * Returns HTTP 201 on success, HTTP 422 on validation failure.
     */
    public function store(StoreTugasRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $tugas = $request->user()->tugas()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'pending',
            'due_date' => $validated['due_date'] ?? null,
        ]);

        return response()->json([
            'message' => 'Tugas created successfully',
            'data' => $tugas,
        ], 201);
    }

    /**
     * Display the specified tugas.
     * Returns HTTP 200 on success, HTTP 404 if not found or unauthorized.
     */
    public function show(Request $request, string|int $id): JsonResponse
    {
        $tugas = $request->user()->tugas()->find($id);

        if (! $tugas) {
            return response()->json([
                'message' => 'Tugas not found',
            ], 404);
        }

        return response()->json([
            'data' => $tugas,
        ], 200);
    }

    /**
     * Update the specified tugas.
     * Returns HTTP 200 on success, HTTP 404 if not found, HTTP 422 on validation failure.
     */
    public function update(UpdateTugasRequest $request, string|int $id): JsonResponse
    {
        $tugas = $request->user()->tugas()->find($id);

        if (! $tugas) {
            return response()->json([
                'message' => 'Tugas not found',
            ], 404);
        }

        $tugas->update($request->validated());

        return response()->json([
            'message' => 'Tugas updated successfully',
            'data' => $tugas,
        ], 200);
    }

    /**
     * Remove the specified tugas.
     * Returns HTTP 200 on success, HTTP 404 if not found.
     */
    public function destroy(Request $request, string|int $id): JsonResponse
    {
        $tugas = $request->user()->tugas()->find($id);

        if (! $tugas) {
            return response()->json([
                'message' => 'Tugas not found',
            ], 404);
        }

        $tugas->delete();

        return response()->json([
            'message' => 'Tugas deleted successfully',
        ], 200);
    }
}
