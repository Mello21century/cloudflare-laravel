<?php

namespace Space\Cloudflare\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCpanelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'zoneId' => 'required',
            'ip' => 'required|ipv4'
        ];
    }
}
