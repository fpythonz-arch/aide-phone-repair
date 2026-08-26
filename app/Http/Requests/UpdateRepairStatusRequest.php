<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRepairStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:new,received,diagnosing,waiting_quote,quote_accepted,in_progress,waiting_parts,testing,ready,delivered,cancelled',
        ];
    }
}
