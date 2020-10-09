<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IncomeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $user = $this->user('api');
        return [
            'quantity' => 'required|numeric|min:1|max:99999999.99',
            'date' => 'required|date',
            'comment' => 'nullable|string',
            'activity_id' => [
                'nullable',
                Rule::exists('activities', 'id')->where('owner_id', $user->id),
            ],
            'labels' => 'nullable|array',
            'labels.*' => [Rule::exists('labels', 'id')->where('owner_id', $user->id)],
        ];
    }
}
