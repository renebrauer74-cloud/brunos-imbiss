<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Menu\MoveInOrderAction;
use App\Enums\MediaCollectionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GalleryCaptionsRequest;
use App\Http\Requests\Admin\GalleryUploadRequest;
use App\Models\GalleryImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class GalleryImageController extends Controller
{
    public function store(GalleryUploadRequest $request): RedirectResponse
    {
        /** @var list<UploadedFile> $files */
        $files = $request->file('images');
        $sortOrder = (int) GalleryImage::query()->max('sort_order');

        foreach ($files as $file) {
            $image = GalleryImage::query()->create(['sort_order' => $sortOrder += 10]);
            $image->addMedia($file)->toMediaCollection(MediaCollectionEnum::Gallery->value);
        }

        return $this->backToGallery()
            ->with('status', count($files) === 1 ? '1 Bild hochgeladen.' : count($files).' Bilder hochgeladen.');
    }

    public function updateCaptions(GalleryCaptionsRequest $request): RedirectResponse
    {
        $captions = $request->validated('captions');

        GalleryImage::query()->whereKey(array_keys($captions))->get()
            ->each(fn (GalleryImage $image) => $image->update(['caption' => $captions[$image->id] ?? null]));

        return $this->backToGallery()->with('status', 'Bildunterschriften gespeichert.');
    }

    public function toggle(GalleryImage $galleryImage): RedirectResponse
    {
        $galleryImage->update(['visible' => ! $galleryImage->visible]);

        return $this->backToGallery();
    }

    public function move(Request $request, GalleryImage $galleryImage, MoveInOrderAction $moveInOrder): RedirectResponse
    {
        $moveInOrder->execute(GalleryImage::query()->ordered()->get(), $galleryImage, $request->integer('direction'));

        return $this->backToGallery();
    }

    public function destroy(GalleryImage $galleryImage): RedirectResponse
    {
        $galleryImage->delete();

        return $this->backToGallery()->with('status', 'Bild gelöscht.');
    }

    private function backToGallery(): RedirectResponse
    {
        return redirect()->to(route('admin.images.index').'#galerie');
    }
}
