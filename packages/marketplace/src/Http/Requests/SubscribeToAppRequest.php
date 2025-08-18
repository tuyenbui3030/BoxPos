<?php

namespace Packages\Marketplace\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeToAppRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plan_type' => 'required|in:basic,professional,enterprise',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'plan_type.required' => 'Please select a subscription plan.',
            'plan_type.in' => 'Invalid subscription plan selected.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'plan_type' => 'subscription plan',
        ];
    }
}