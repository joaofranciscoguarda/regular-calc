<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpressionRequest;
use App\Http\Resources\UserCalculationHistory;
use App\Models\Calculation;
use App\Models\UserHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use jcubic\Expression;

class CalculationController extends Controller
{
    public function index(Request $request): Response
    {
        $history = UserHistory::with('calculation')
            ->where('session_id', $request->session()->getId())
            ->latest('created_at')
            ->paginate(10);

        return Inertia::render('Calculator', [
            'history' => UserCalculationHistory::collection($history),
        ]);
    }

    public function store(ExpressionRequest $request): RedirectResponse
    {
        $expression = trim($request->input('expression'));
        $cacheKey   = 'calc:' . $expression;
        $sessionId  = $request->session()->getId();

        try {
            $cached = Cache::rememberForever($cacheKey, function () use ($expression) {
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

            UserHistory::create([
                'session_id'     => $sessionId,
                'calculation_id' => $cached['calculation_id'],
            ]);

            return back()->with('flash.result', $cached['result'])
                         ->with('flash.expression', $expression);
        } catch (\Throwable $e) {
            Cache::forget($cacheKey);

            return back()->with('flash.error', 'Could not evaluate: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, UserHistory $userHistory): RedirectResponse
    {
        if ($userHistory->session_id !== $request->session()->getId()) {
            abort(404);
        }

        $userHistory->delete();

        return back();
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        UserHistory::where('session_id', $request->session()->getId())->delete();

        return back();
    }
}
