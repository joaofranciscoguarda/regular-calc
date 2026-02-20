<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpressionRequest;
use App\Http\Resources\UserCalculationHistory;
use App\Models\UserHistory;
use App\Services\CalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalculationController extends Controller
{
    public function __construct(private CalculationService $calculationService)
    {
    }

    public function index(Request $request): Response
    {
        $sessionId = $request->session()->getId();
        $history = $this->calculationService->getHistory($sessionId);

        return Inertia::render('Calculator', [
            'history' => UserCalculationHistory::collection($history),
        ]);
    }

    public function store(ExpressionRequest $request): RedirectResponse
    {
        $expression = $request->input('expression');
        $sessionId = $request->session()->getId();

        try {
            $result = $this->calculationService->calculate($expression, $sessionId);

            return back()
                ->with('flash.result', $result['result'])
                ->with('flash.expression', $result['expression']);
        } catch (\Throwable $e) {
            return back()->with('flash.error', 'Could not evaluate: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, UserHistory $userHistory): RedirectResponse
    {
        $sessionId = $request->session()->getId();

        if (!$this->calculationService->deleteHistoryEntry($userHistory, $sessionId)) {
            abort(404);
        }

        return back();
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $sessionId = $request->session()->getId();
        $this->calculationService->clearHistory($sessionId);

        return back();
    }
}
