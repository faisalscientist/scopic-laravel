<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BidRequest extends FormRequest
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
            'auction_item_id' => 'required|exists:auction_items,id',
            'bidder' => 'required|in:user1,user2',
            'amount' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'auction_item_id.required' => 'Please select a valid item',
            'auction_item_id.exists' => 'Item does not exist',
            'amount.required' => 'Please enter a valid amount',
            'amount.numeric' => 'Please enter a valid amount',
            'bidder.required' => 'User can be either user1 or user2 only',
            'bidder.in' => 'User can be either user1 or user2 only',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors()));
    }
}
