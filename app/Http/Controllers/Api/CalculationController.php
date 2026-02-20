<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpressionRequest;
use App\Http\Resources\UserCalculationHistory;
use App\Models\UserHistory;
use App\Services\CalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalculationController extends Controller
{
    public function __construct(private CalculationService $calculationService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $sessionId = $request->attributes->get('calc_session_id');
        $history = $this->calculationService->getHistory($sessionId);

        return response()->json(UserCalculationHistory::collection($history)->response()->getData());
    }

    public function store(ExpressionRequest $request): JsonResponse
    {
        $expression = $request->input('expression');
        $sessionId = $request->attributes->get('calc_session_id');

        try {
            $result = $this->calculationService->calculate($expression, $sessionId);

            return response()->json([
                'id'         => $result['user_history_id'],
                'expression' => $result['expression'],
                'result'     => $result['result'],
                'created_at' => $result['created_at'],
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Could not evaluate: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Request $request, UserHistory $userHistory): JsonResponse
    {
        $sessionId = $request->attributes->get('calc_session_id');

        if (!$this->calculationService->deleteHistoryEntry($userHistory, $sessionId)) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json(['message' => 'Deleted']);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $sessionId = $request->attributes->get('calc_session_id');
        $this->calculationService->clearHistory($sessionId);

        return response()->json(['message' => 'History cleared']);
    }
}
