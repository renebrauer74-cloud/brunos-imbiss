<?php

namespace App\Actions\Site;

use App\Models\SiteSetting;

/**
 * Adresse, Google-Maps-Link und tel:-Link aus den Einstellungen.
 */
class BuildContactLinksAction
{
    /**
     * @return array{address: string, maps: string, tel: ?string}
     */
    public function execute(SiteSetting $settings): array
    {
        $address = trim(($settings->street ?? '').', '.($settings->zip_city ?? ''), ', ');
        $query = filled($settings->street) ? $address : $settings->site_title.' '.$settings->zip_city;

        return [
            'address' => $address,
            'maps' => 'https://www.google.com/maps/search/?api=1&query='.rawurlencode(trim($query)),
            'tel' => filled($settings->phone) ? $this->telHref($settings->phone) : null,
        ];
    }

    private function telHref(string $phone): string
    {
        $number = (string) preg_replace('/[^\d+]/', '', $phone);

        if (str_starts_with($number, '0') && ! str_starts_with($number, '00')) {
            $number = '+49'.substr($number, 1);
        }

        return 'tel:'.$number;
    }
}
