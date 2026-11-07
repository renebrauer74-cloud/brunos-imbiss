<?php

namespace App\Http\Requests\Admin;

use App\Actions\Menu\ParsePriceAction;
use App\Enums\MenuItemTagEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MenuItemRequest extends AdminFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'string', 'max:20'],
            'tags' => ['array'],
            'tags.*' => [Rule::enum(MenuItemTagEnum::class)],
            'visible' => ['boolean'],
            'featured' => ['boolean'],
            'image' => $this->imageRules(),
            'remove_image' => ['boolean'],
        ];
    }

    /**
     * Der Preis muss sich als Euro-Betrag lesen lassen ("3,50").
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('price')) {
                    return;
                }

                if (app(ParsePriceAction::class)->execute($this->string('price')->toString()) === null) {
                    $validator->errors()->add('price', 'Bitte einen gültigen Preis eingeben, z. B. 3,50.');
                }
            },
        ];
    }
}
