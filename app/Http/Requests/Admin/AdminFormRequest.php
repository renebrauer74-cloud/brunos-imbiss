<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Basis für die Admin-Formulare. Der Zugriff ist bereits per auth-Middleware geschützt.
 */
abstract class AdminFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regeln für ein hochgeladenes Foto.
     *
     * @return list<string>
     */
    protected function imageRules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:'.config('bruno.uploads.max_kilobytes'),
        ];
    }
}
