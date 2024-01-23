<?php

namespace App\Http\Requests\Frontend\QueueController;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:10'],
            'phone' => [
                'required',
                'regex:/^09\d{8}$/',
            ],
            'seat' => [
                'required',
                'integer',
                'min:1',
                'max:8',
            ],
        ];
    }
}
