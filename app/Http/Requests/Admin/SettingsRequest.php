<?php

namespace App\Http\Requests\Admin;

use App\Enums\WeekdayEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Validator;

class SettingsRequest extends AdminFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'site_title' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'slogan' => ['nullable', 'string', 'max:160'],
            'notice' => ['nullable', 'string', 'max:240'],
            'street' => ['nullable', 'string', 'max:120'],
            'zip_city' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:120'],
            'hours_note' => ['nullable', 'string', 'max:300'],
            'about' => ['nullable', 'string', 'max:2000'],
            'imprint' => ['nullable', 'string', 'max:6000'],
            'privacy' => ['nullable', 'string', 'max:20000'],
            'hours' => ['array'],
            'hours.*.from' => ['nullable', 'date_format:H:i'],
            'hours.*.to' => ['nullable', 'date_format:H:i'],
            'hours.*.closed' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [];

        foreach (WeekdayEnum::cases() as $day) {
            $attributes["hours.{$day->value}.from"] = "Beginn am {$day->label()}";
            $attributes["hours.{$day->value}.to"] = "Ende am {$day->label()}";
        }

        return $attributes;
    }

    /**
     * Ohne Ruhetag braucht ein Tag Beginn und Ende – oder beides leer.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach (WeekdayEnum::cases() as $day) {
                    $from = filled($this->input("hours.{$day->value}.from"));
                    $to = filled($this->input("hours.{$day->value}.to"));

                    if (! $this->boolean("hours.{$day->value}.closed") && $from !== $to) {
                        $validator->errors()->add("hours.{$day->value}", "Bitte am {$day->label()} Beginn und Ende eintragen (oder Ruhetag wählen).");
                    }
                }
            },
        ];
    }
}
