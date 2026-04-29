<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id'       => ['required', 'integer', 'exists:branches,id'],
            'first_name'      => ['required', 'string', 'max:255'],
            'last_name'       => ['required', 'string', 'max:255'],
            'employee_number' => ['required', 'string', 'max:255', 'unique:employees,employee_number,' . $this->route('employee')->id],
            'avatar'          => ['nullable', 'url', 'max:2048'],
        ];
    }
}
