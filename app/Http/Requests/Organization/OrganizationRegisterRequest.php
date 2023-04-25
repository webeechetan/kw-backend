<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class OrganizationRegisterRequest extends FormRequest
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
            'name' => 'required|unique:organizations|max:255',
            'email' => 'required|email|unique:organizations',
            'password' => 'required',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        $response = [
            'success' => false,
            'message' => 'Validation Error',
            'data' => $validator->errors(),
        ];
        throw new HttpResponseException(response()->json($response, 422));
    }



}
