<?php

namespace App\Enums;

enum MediaConversionEnum: string
{
    /** Auf max. 1600 px verkleinertes WebP für die Website und die Lightbox */
    case Web = 'web';

    /** Quadratisches WebP (400 px) für Speisekarte und Admin-Listen */
    case Thumb = 'thumb';
}
