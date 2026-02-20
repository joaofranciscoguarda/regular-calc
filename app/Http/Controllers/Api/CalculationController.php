<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserCalculationHistory;
use App\Models\Calculation;
use App\Models\UserHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use jcubic\Expression;

class CalculationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $sessionId = $request->attributes->get('calc_session_id');

        $history = UserHistory::with('calculation')
            ->where('session_id', $sessionId)
            ->latest('created_at')
            ->paginate(10);

        return response()->json(UserCalculationHistory::collection($history)->response()->getData());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'expression' => ['required', 'string', 'max:1000'],
        ]);

        $expression = trim($request->input('expression'));
        $cacheKey   = 'calc:' . $expression;
        $sessionId  = $request->attributes->get('calc_session_id');

        try {
            $cached = Cache::store('redis')->rememberForever($cacheKey, function () use ($expression) {
                $evaluated = (new Expression())->evaluate($expression);

                if ($evaluated === false || $evaluated === null) {
                    throw new \RuntimeException('Invalid expression');
                }

                $result = (is_float($evaluated) && floor($evaluated) == $evaluated && abs($evaluated) < PHP_INT_MAX)
                    ? (string) (int) $evaluated
                    : (string) $evaluated;

                $calculation = Calculation::create([
                    'expression' => $expression,
                    'result'     => $result,
                ]);

                return ['result' => $result, 'calculation_id' => $calculation->id];
            });

            $entry = UserHistory::create([
                'session_id'     => $sessionId,
                'calculation_id' => $cached['calculation_id'],
            ]);

            return response()->json([
                'id'         => $entry->id,
                'expression' => $expression,
                'result'     => $cached['result'],
                'created_at' => $entry->created_at,
            ], 201);
        } catch (\Throwable $e) {
            Cache::store('redis')->forget($cacheKey);

            return response()->json([
                'error' => 'Could not evaluate: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Request $request, UserHistory $userHistory): JsonResponse
    {
        if ($userHistory->session_id !== $request->attributes->get('calc_session_id')) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $userHistory->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        UserHistory::where('session_id', $request->attributes->get('calc_session_id'))->delete();

        return response()->json(['message' => 'History cleared']);
    }
}
