<?php

namespace App\Http\Requests\Staff\TableController;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'seat' => ['required', 'integer', 'min:1', 'max:8'],
        ];
    }
}
