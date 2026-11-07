<?php

namespace App\Actions\Site;

use App\Enums\WeekdayEnum;
use App\Models\SiteSetting;

/**
 * Strukturierte Daten für Google (schema.org FastFoodRestaurant) auf der Startseite.
 */
class BuildRestaurantSchemaAction
{
    public function __construct(private ListOpeningHoursAction $listOpeningHours) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SiteSetting $settings): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'FastFoodRestaurant',
            'name' => $settings->site_title,
            'slogan' => $settings->slogan,
            'url' => route('home'),
            'image' => asset('assets/img/logo.jpg'),
            'servesCuisine' => ['Currywurst', 'Pommes', 'Schnitzel', 'Imbiss'],
            'priceRange' => '€',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $settings->street,
                'addressLocality' => $settings->zip_city,
                'addressCountry' => 'DE',
            ],
        ];

        if (filled($settings->phone)) {
            $schema['telephone'] = $settings->phone;
        }

        $specification = [];
        foreach ($this->listOpeningHours->execute($settings) as $key => $day) {
            if (! $day['closed'] && $day['from'] !== '' && $day['to'] !== '') {
                $specification[] = [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => WeekdayEnum::from($key)->schemaName(),
                    'opens' => $day['from'],
                    'closes' => $day['to'],
                ];
            }
        }

        if ($specification !== []) {
            $schema['openingHoursSpecification'] = $specification;
        }

        return $schema;
    }
}
