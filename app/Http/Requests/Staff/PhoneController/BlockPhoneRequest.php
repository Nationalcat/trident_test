<?php

namespace App\Http\Requests\Staff\PhoneController;

use Illuminate\Foundation\Http\FormRequest;

class BlockPhoneRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer'],
        ];
    }
}
