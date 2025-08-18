<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->isAdmin() || auth()->user()->isSupervisor();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $customerId = $this->route('customer')?->id;
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2'
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customerId)
            ],
            'phone' => [
                'required',
                'string',
                'max:20',
                'min:10',
                'regex:/^[0-9+\-\s()]+$/',
                Rule::unique('customers', 'phone')->ignore($customerId)
            ],
            'address' => [
                'nullable',
                'string',
                'max:500'
            ],
            'birth_date' => [
                'nullable',
                'date',
                'before:today',
                'after:1900-01-01'
            ],
            'gender' => [
                'nullable',
                'in:male,female'
            ],
            'is_active' => [
                'nullable',
                'boolean'
            ]
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama customer wajib diisi.',
            'name.min' => 'Nama customer minimal 2 karakter.',
            'name.max' => 'Nama customer maksimal 255 karakter.',
            
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan customer lain.',
            'email.max' => 'Email maksimal 255 karakter.',
            
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.min' => 'Nomor telepon minimal 10 digit.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'phone.unique' => 'Nomor telepon sudah digunakan customer lain.',
            
            'address.max' => 'Alamat maksimal 500 karakter.',
            
            'birth_date.date' => 'Format tanggal lahir tidak valid.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
            'birth_date.after' => 'Tanggal lahir tidak valid.',
            
            'gender.in' => 'Jenis kelamin harus laki-laki atau perempuan.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'email',
            'phone' => 'nomor telepon',
            'address' => 'alamat',
            'birth_date' => 'tanggal lahir',
            'gender' => 'jenis kelamin',
            'is_active' => 'status aktif'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean up phone number
        if ($this->has('phone')) {
            $phone = preg_replace('/[^\d+]/', '', $this->phone);
            $this->merge(['phone' => $phone]);
        }

        // Convert is_active checkbox to boolean
        $this->merge([
            'is_active' => $this->boolean('is_active', false)
        ]);

        // Clean up name
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name)
            ]);
        }

        // Clean up email
        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim($this->email))
            ]);
        }
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->expectsJson()) {
            $response = response()->json([
                'success' => false,
                'message' => 'Data yang dimasukkan tidak valid.',
                'errors' => $validator->errors()
            ], 422);
            
            throw new \Illuminate\Validation\ValidationException($validator, $response);
        }

        parent::failedValidation($validator);
    }
}