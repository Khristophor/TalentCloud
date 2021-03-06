<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassificationCrudRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return boolean
     */
    public function authorize(): bool
    {
        // Only allow updates if the user is a logged in Admin.
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'key' => 'required|between:2,6|alpha_dash|unique:classifications,key'
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return string[]
     */
    public function messages(): array
    {
        return [
            'key.required' => 'Please enter a classification key.',
            'key.between' => 'The classification key must be between 2 and 6 characters long.',
            'key.alpha_dash' => 'The classification key may only use alphabetic characters and dashes.',
            'key.unique' => 'That classification key already exists.'
        ];
    }
}
