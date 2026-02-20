<?php

use App\Models\Calculation;
use App\Models\UserHistory;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

describe('API CalculationController', function () {
    it('can store a calculation and returns success', function () {
        $response = $this->postJson('/api/calculations', [
            'expression' => '2+2',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'expression',
                'result',
                'created_at',
            ]);
    });

    it('can retrieve calculation history', function () {
        $sessionId = 'test-session-123';

        $response = $this->withHeader('X-Calc-Session', $sessionId)
            ->getJson('/api/calculations');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'links', 'meta']);
    });

    it('can delete a calculation from history', function () {
        $sessionId = 'test-session-456';
        $calculation = Calculation::create([
            'expression' => '10-5',
            'result' => '5',
        ]);

        $userHistory = UserHistory::create([
            'session_id' => $sessionId,
            'calculation_id' => $calculation->id,
        ]);

        $response = $this->withHeader('X-Calc-Session', $sessionId)
            ->deleteJson("/api/calculations/{$userHistory->id}");

        $response->assertStatus(200);
    });

    it('cannot delete another session calculation', function () {
        $sessionId = 'session-123';
        $otherSessionId = 'session-456';

        $calculation = Calculation::create([
            'expression' => '10-5',
            'result' => '5',
        ]);

        $userHistory = UserHistory::create([
            'session_id' => $otherSessionId,
            'calculation_id' => $calculation->id,
        ]);

        $response = $this->withHeader('X-Calc-Session', $sessionId)
            ->deleteJson("/api/calculations/{$userHistory->id}");

        $response->assertStatus(404);
    });

    it('can delete all calculations from history', function () {
        $sessionId = 'test-session-789';

        $response = $this->withHeader('X-Calc-Session', $sessionId)
            ->deleteJson('/api/calculations');

        $response->assertStatus(200);
    });

    it('validates session isolation', function () {
        $session1 = 'session-aaa';
        $session2 = 'session-bbb';

        $this->withHeader('X-Calc-Session', $session1)
            ->postJson('/api/calculations', ['expression' => '1+1']);

        $this->withHeader('X-Calc-Session', $session2)
            ->postJson('/api/calculations', ['expression' => '2+2']);

        $history1 = $this->withHeader('X-Calc-Session', $session1)
            ->getJson('/api/calculations');

        $history2 = $this->withHeader('X-Calc-Session', $session2)
            ->getJson('/api/calculations');

        expect($history1->json('data'))->toHaveCount(1);
        expect($history2->json('data'))->toHaveCount(1);
    });
});

describe('API CalculationController - Validation', function () {
    it('requires expression field', function () {
        $response = $this->postJson('/api/calculations', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['expression']);
    });

    it('rejects empty expression', function () {
        $response = $this->postJson('/api/calculations', [
            'expression' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['expression']);
    });

    it('rejects expression longer than 1000 characters', function () {
        $response = $this->postJson('/api/calculations', [
            'expression' => str_repeat('1+', 500) . '1',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['expression']);
    });


});

describe('API CalculationController - Security', function () {
    it('rejects dangerous expressions', function () {
        $maliciousExpressions = [
            '<?php system("ls"); ?>',
            'exec("whoami")',
            'file_get_contents("/etc/passwd")',
            "'; DROP TABLE calculations; --",
            'new Exception("test")',
            '$_GET["cmd"]',
        ];

        foreach ($maliciousExpressions as $expression) {
            $response = $this->postJson('/api/calculations', [
                'expression' => $expression,
            ]);

            expect($response->status())->toBe(422);
        }
    });
});
