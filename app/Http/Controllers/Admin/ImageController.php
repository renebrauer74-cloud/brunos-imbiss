<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryImage;
use App\Models\HomePhoto;
use Illuminate\View\View;

/**
 * Bilder: die drei Fotos unter der Speisekarte und die Bildergalerie.
 */
class ImageController extends Controller
{
    public function index(): View
    {
        return view('admin.images', [
            'homePhotos' => HomePhoto::query()->ordered()->with('media')->get(),
            'gallery' => GalleryImage::query()->ordered()->with('media')->get(),
        ]);
    }
}
