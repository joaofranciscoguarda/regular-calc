<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpressionRequest;
use App\Models\Calculation;
use Illuminate\Http\JsonResponse;
use jcubic\Expression;

class CalculationController extends Controller
{
    public function index(): JsonResponse
    {
        $calculations = Calculation::latest()->get();

        return response()->json($calculations);
    }

    public function store(ExpressionRequest $request): JsonResponse
    {
        $expression = trim($request->input('expression'));

        try {
            $expr = new Expression();
            $evaluated = $expr->evaluate($expression);

            if ($evaluated === false || $evaluated === null) {
                throw new \RuntimeException('Invalid expression');
            }

            // Strip unnecessary trailing zeros from whole-number floats
            if (is_float($evaluated) && floor($evaluated) == $evaluated && abs($evaluated) < PHP_INT_MAX) {
                $result = (string) (int) $evaluated;
            } else {
                $result = (string) $evaluated;
            }

            $calculation = Calculation::create([
                'expression' => $expression,
                'result' => $result,
            ]);

            return response()->json([
                'id' => $calculation->id,
                'expression' => $calculation->expression,
                'result' => $result,
                'created_at' => $calculation->created_at,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Could not evaluate expression: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(Calculation $calculation): JsonResponse
    {
        $calculation->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function destroyAll(): JsonResponse
    {
        Calculation::truncate();

        return response()->json(['message' => 'All calculations cleared']);
    }
}
