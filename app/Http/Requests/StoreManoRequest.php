<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreManoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:usuarios,id'],   
            'sala_id' => ['required', 'exists:salas,id'],     
            'creditos_jugados' => ['required', 'integer', 'min:0'],
            'creditos_ganados' => ['required', 'integer', 'min:0'],
        ];
    }
}
