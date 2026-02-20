<?php

use App\Models\Calculation;
use App\Models\UserHistory;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Cache::flush();
});

describe('Inertia CalculationController', function () {
    it('renders the calculator page', function () {
        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) =>
                $page->component('Calculator')
                    ->has('history')
            );
    });

    it('displays calculation history', function () {
        $sessionId = session()->getId();

        $calculation = Calculation::create([
            'expression' => '5*5',
            'result' => '25',
        ]);

        UserHistory::create([
            'session_id' => $sessionId,
            'calculation_id' => $calculation->id,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) =>
                $page->component('Calculator')
                    ->has('history.data')
            );
    });
});
