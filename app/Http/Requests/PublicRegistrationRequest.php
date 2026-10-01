<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'nb_participants' => 'required|integer|min:1',
            'consent' => 'accepted',
        ];
    }
}
