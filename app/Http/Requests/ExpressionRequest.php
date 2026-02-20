<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpressionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('expression')) {
            $this->merge([
                'expression' => $this->balanceParens(trim($this->input('expression')))
            ]);
        }
    }

    /**
     * Balance parentheses in expression.
     */
    private function balanceParens(string $expr): string
    {
        $depth = 0;
        for ($i = 0; $i < strlen($expr); $i++) {
            if ($expr[$i] === '(') {
                $depth++;
            } elseif ($expr[$i] === ')') {
                $depth--;
            }
        }
        return $depth > 0 ? $expr . str_repeat(')', $depth) : $expr;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'expression' => ['required', 'string', 'max:255']
        ];
    }
}
