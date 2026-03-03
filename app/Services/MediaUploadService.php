<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;

/**
 * Spatie Media Library tabanlı medya yükleme servisi.
 * Sadece HasMedia (InteractsWithMedia trait'li) modeller ile kullanılır.
 */
class MediaUploadService
{
    /**
     * Resmi modele Spatie Media Library ile bağlar (hasMedia).
     *
     * @param HasMedia $model InteractsWithMedia kullanan model (örn: Ticket)
     * @param UploadedFile $file Yüklenen dosya (Form Request'te image/mimes doğrulanmış olmalı)
     * @param string $collection Medya koleksiyonu adı (örn: 'tickets', 'default')
     */
    public function uploadImage(HasMedia $model, UploadedFile $file, string $collection = 'default'): void
    {
        $model->addMedia($file)
            ->sanitizingFileName(function (string $fileName): string {
                return Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
            })
            ->toMediaCollection($collection);
    }

    /**
     * Belirtilen koleksiyondaki tüm medyayı temizler.
     */
    public function clearMedia(HasMedia $model, string $collection = 'default'): void
    {
        $model->clearMediaCollection($collection);
    }
}