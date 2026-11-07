<?php

namespace App\Enums;

/**
 * Media-Collections – der Wert ist zugleich der Unterordner auf der Disk "uploads" (siehe CollectionPathGenerator).
 */
enum MediaCollectionEnum: string
{
    /** Foto eines Gerichts (Speisekarte und "Unsere Lieblinge") */
    case MenuItems = 'menu-items';

    /** Bildergalerie */
    case Gallery = 'gallery';

    /** Die drei gedrehten Fotos unter der Speisekarte */
    case HomePhotos = 'home-photos';
}
