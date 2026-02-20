<?php

namespace App\Services;

use App\Models\Calculation;
use App\Models\UserHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use jcubic\Expression;

class CalculationService
{
    /**
     * Get calculation history for a session
     */
    public function getHistory(string $sessionId, int $perPage = 10): LengthAwarePaginator
    {
        return UserHistory::with('calculation')
            ->where('session_id', $sessionId)
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Evaluate an expression and store the result
     *
     * @return array{result: string, calculation_id: int, user_history_id: int, expression: string}
     * @throws \RuntimeException
     */
    public function calculate(string $expression, string $sessionId): array
    {
        $cacheKey = 'calc:' . $expression;

        try {
            $cached = Cache::store('redis')->rememberForever($cacheKey, function () use ($expression) {
                $evaluated = (new Expression())->evaluate($expression);

                if ($evaluated === false || $evaluated === null) {
                    throw new \RuntimeException('Invalid expression');
                }

                $result = $this->formatResult($evaluated);

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

            return [
                'result'          => $cached['result'],
                'calculation_id'  => $cached['calculation_id'],
                'user_history_id' => $entry->id,
                'expression'      => $expression,
                'created_at'      => $entry->created_at,
            ];
        } catch (\Throwable $e) {
            Cache::store('redis')->forget($cacheKey);
            throw $e;
        }
    }

    /**
     * Delete a specific history entry for a session
     */
    public function deleteHistoryEntry(UserHistory $userHistory, string $sessionId): bool
    {
        if ($userHistory->session_id !== $sessionId) {
            return false;
        }

        return $userHistory->delete();
    }

    /**
     * Delete all history for a session
     */
    public function clearHistory(string $sessionId): int
    {
        return UserHistory::where('session_id', $sessionId)->delete();
    }

    /**
     * Format evaluation result as string
     */
    private function formatResult(float|int $evaluated): string
    {
        return (is_float($evaluated) && floor($evaluated) == $evaluated && abs($evaluated) < PHP_INT_MAX)
            ? (string) (int) $evaluated
            : (string) $evaluated;
    }
}
