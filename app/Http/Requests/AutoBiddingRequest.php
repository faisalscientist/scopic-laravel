<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AutoBiddingRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'maximum_amount' => 'required|numeric',
            'user' => 'required|in:user1,user2',
        ];
    }

    public function messages()
    {
        return [
            'maximum_amount.required' => 'Please enter a valid amount',
            'maximum_amount.numeric' => 'Please enter a valid amount',
            'user.required' => 'User can be either user1 or user2 only',
            'user.in' => 'User can be either user1 or user2 only',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors()));
    }
}
