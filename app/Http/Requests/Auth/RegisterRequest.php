<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'             => ['required', 'string', 'max:255'],
            'surname1'         => ['required', 'string', 'max:255'],
            'surname2'         => ['nullable', 'string', 'max:255'],
            'email'            => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'postal_code'      => ['required', 'digits:5'],
            'dni'              => ['required', 'string', 'max:20', 'unique:users,dni'],
            'address'          => ['required', 'string', 'max:255'],
            'fecha_nacimiento' => ['required', 'date'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
