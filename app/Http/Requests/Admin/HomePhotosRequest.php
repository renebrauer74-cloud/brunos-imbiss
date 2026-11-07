<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;

class HomePhotosRequest extends AdminFormRequest
{
    /**
     * Felder je Platz: photos[1][caption], photos[1][image], photos[1][remove].
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'photos' => ['required', 'array'],
            'photos.*.caption' => ['nullable', 'string', 'max:80'],
            'photos.*.image' => $this->imageRules(),
            'photos.*.remove' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'photos.*.caption' => 'Bildunterschrift',
            'photos.*.image' => 'Das Foto',
        ];
    }
}
