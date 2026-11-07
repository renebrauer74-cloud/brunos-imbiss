<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;

class PricesRequest extends AdminFormRequest
{
    /**
     * Ungültige Preise prüft UpdatePricesAction und meldet sie gesammelt zurück.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'price' => ['required', 'array'],
        ];
    }
}
