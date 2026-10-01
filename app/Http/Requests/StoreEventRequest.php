<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_dates' => 'required|array|min:1',
            'event_dates.*' => 'date|after_or_equal:today',
            'location' => 'nullable|string|max:200',
            'rdv_point' => 'nullable|string|max:200',
            'event_duration' => 'nullable|string|max:100',
            'audience_type' => 'nullable|string|max:100',
            'max_participants' => 'nullable|integer|min:1',
            'min_participants' => 'nullable|integer|min:0',
            'is_paid' => 'boolean',
            'price_details' => 'nullable|string',
            'price_amount' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,webp|max:2048',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if ($this->filled('min_participants') && $this->filled('max_participants')
                && $this->integer('min_participants') > $this->integer('max_participants')) {
                $v->errors()->add('min_participants', 'Le minimum ne peut pas dépasser le maximum.');
            }

            if ($this->boolean('is_paid') && $this->float('price_amount') <= 0) {
                $v->errors()->add('price_amount', 'Le montant doit être supérieur à 0 pour un événement payant.');
            }
        });
    }
}
