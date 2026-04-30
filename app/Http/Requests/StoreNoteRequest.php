<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id'  => ['required', 'integer', 'exists:companies,id'],
            'branch_id'   => ['required', 'integer', 'exists:branches,id'],
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'description' => ['required', 'string', 'max:1000'],
            'amount'      => ['required', 'numeric', 'min:0'],
            'date'        => ['nullable', 'date'],
            'reference'   => ['nullable', 'string', 'max:255'],
        ];
    }
}
