<?php

namespace Database\Seeders;

use App\Enums\MenuItemTagEnum;
use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Startdaten: Preisliste vom 08.06.2026.
 * Artikel ohne Preis (0 €) werden ausgeblendet angelegt und können im Backend mit Preis versehen werden.
 */
class MenuSeeder extends Seeder
{
    /**
     * Je Artikel: [Name, Preis in €, Tag, Liebling?]
     *
     * @var array<string, list<array{0: string, 1: float, 2?: ?MenuItemTagEnum, 3?: bool}>>
     */
    private const MENU = [
        'Wurst & Frikadellen' => [
            ['Bratwurst im Brötchen', 3.50],
            ['Bratwurst ohne Brötchen', 3.00],
            ['Krakauer', 0],
            ['Brötchen', 0.50],
            ['Frikadelle', 3.20],
            ["Bruno's Spezial Frikadelle", 5.30, null, true],
            ['Chili Cheese Frikadelle', 5.20, MenuItemTagEnum::Spicy],
            ['Curry Frikadelle', 3.90],
            ['Texaswurst', 4.50],
            ['Currywurst mit Toast', 3.80],
            ['Currywurst Pommes', 6.30, null, true],
            ['Currywurst mit Jalapeños', 4.50, MenuItemTagEnum::Spicy],
            ['Chili Cheese Bratwurst', 5.10, MenuItemTagEnum::Spicy, true],
            ['Frikandel Spezial', 3.50],
            ['Ruhr Pott', 7.50, null, true],
            ['Wildschwein Bratwurst im Brötchen', 0],
        ],
        'Pommes & Beilagen' => [
            ['Pommes – kleine Portion', 2.70],
            ['Pommes – große Portion', 3.70],
            ['Pommes – doppelte Portion', 5.40],
            ['Pommes Spezial', 4.50],
            ['Pommes Spezial – groß', 5.50],
            ['Bratkartoffeln', 3.20],
            ['Kartoffel Wedges', 0],
            ['Kroketten (6 Stück)', 2.70],
        ],
        'Schnitzel' => [
            ['Zigeunerschnitzel mit Pommes & Mayo', 9.50],
            ['Jägerschnitzel mit Pommes & Mayo', 9.50],
            ['Schnitzel mit Champignons & Pommes', 10.50],
            ['Schnitzel mit Käsesoße', 9.90],
            ['Schnitzel mit Käsesoße & Jalapeños', 10.50, MenuItemTagEnum::Spicy],
            ['Schnitzel mit Currysoße & geschmorten Zwiebeln', 9.90],
            ['Schnitzel mit Sauce Hollandaise', 9.90],
            ['Holsteiner Schnitzel', 10.50],
            ['Schnitzel natur', 4.90],
            ['Hähnchenschnitzel natur', 4.90],
        ],
        'Spezial' => [
            ['Fleischkroketten (3 Stück)', 4.50],
            ['Käsekroketten (3 Stück)', 4.50],
            ['Fleisch- oder Käsekrokette (1 Stück)', 1.60],
            ['Frühlingsrollen (6 Stück)', 1.50],
            ['Holland Teller', 8.50],
            ['Hot Dog', 4.50],
            ['Chicken Nuggets (6 Stück)', 3.50],
        ],
        'Soßen & Extras' => [
            ['Jäger- oder Zigeunersoße', 1.00],
            ['Currysoße', 0.70],
            ['Joppie Soße', 1.00],
            ['Knoblauchsoße (Mayo)', 0.70],
            ['Käsesoße', 1.50],
            ['Ketchup / Mayo', 0.70],
            ['Sauce Hollandaise', 1.50],
            ['Geschmorte Zwiebeln', 0.70],
            ['Jalapeños', 0.70, MenuItemTagEnum::Spicy],
            ['Spiegelei', 0.70],
        ],
        'Getränke' => [
            ['Coca-Cola / Cola Zero / Sprite / Fanta', 2.20],
            ['Durstlöscher', 1.50],
            ['Wasser', 1.50],
            ['Kaffee', 1.50],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $featuredOrder = 0;

        foreach (array_keys(self::MENU) as $categoryIndex => $categoryName) {
            $category = Category::query()->create([
                'name' => $categoryName,
                'sort_order' => ($categoryIndex + 1) * 10,
            ]);

            foreach (self::MENU[$categoryName] as $itemIndex => $row) {
                $cents = (int) round($row[1] * 100);
                $featured = $row[3] ?? false;

                $category->menuItems()->create([
                    'name' => $row[0],
                    'price_cents' => $cents,
                    'tags' => isset($row[2]) ? [$row[2]] : [],
                    'visible' => $cents > 0,
                    'sort_order' => ($itemIndex + 1) * 10,
                    'featured' => $featured,
                    'featured_order' => $featured ? $featuredOrder += 10 : 0,
                ]);
            }
        }
    }
}
