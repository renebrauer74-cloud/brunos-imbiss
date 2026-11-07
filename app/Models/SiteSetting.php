<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Einstellungen der Website – genau eine Zeile (legt die Migration an).
 * Pro Request einmal geladen, siehe AppServiceProvider (per Dependency Injection holen).
 */
#[Fillable([
    'site_title', 'tagline', 'slogan', 'notice', 'street', 'zip_city', 'phone', 'email',
    'opening_hours', 'hours_note', 'about', 'imprint', 'privacy',
])]
class SiteSetting extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'opening_hours' => 'array',
        ];
    }
}
