<?php

namespace App\Http\Requests\Staff\QueueController;

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
            'name' => 'required|string|max:10',
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
            'date' => [
                'required',
                'date_format:Y-m-d H:i:s',
                'after:now',
            ],
        ];
    }
}
