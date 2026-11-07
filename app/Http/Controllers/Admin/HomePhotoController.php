<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MediaCollectionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomePhotosRequest;
use App\Models\HomePhoto;
use Illuminate\Http\RedirectResponse;

class HomePhotoController extends Controller
{
    public function update(HomePhotosRequest $request): RedirectResponse
    {
        foreach (HomePhoto::query()->ordered()->get() as $photo) {
            $key = 'photos.'.$photo->slot->value;

            $photo->update(['caption' => $request->input($key.'.caption')]);

            if ($request->hasFile($key.'.image')) {
                $photo->addMedia($request->file($key.'.image'))->toMediaCollection(MediaCollectionEnum::HomePhotos->value);
            } elseif ($request->boolean($key.'.remove')) {
                $photo->clearMediaCollection(MediaCollectionEnum::HomePhotos->value);
            }
        }

        return redirect()->to(route('admin.images.index').'#startseite')->with('status', 'Startseiten-Bilder gespeichert.');
    }
}
