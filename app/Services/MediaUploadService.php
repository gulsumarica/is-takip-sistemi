<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MediaUploadService
{
    /**
     * Sisteme yüklenen resimleri Spatie Media Library kullanarak modele bağlar.
     *
     * @param Model $model (Hangi modele bağlanacak? Örn: Ticket, User)
     * @param UploadedFile $file (Yüklenen fiziksel dosya)
     * @param string $collection (Hangi klasöre/koleksiyona eklenecek? Örn: 'ticket_screenshots')
     * @return void
     */
    public function uploadImage(Model $model, UploadedFile $file, string $collection = 'default'): void
    {
        // Spatie Media Library'nin gücünü kullanıyoruz.
        // Form Request'te (Güvenlik Duvarı) dosyanın resim (.jpg, .png, .webp) olduğunu zaten kanıtlamıştık.
        
        $model->addMedia($file)
            ->sanitizingFileName(function ($fileName) {
                // Güvenlik ve SEO: Dosya adındaki boşlukları, Türkçe karakterleri ve zararlı olabilecek sembolleri temizle
                return Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);
            })
            ->toMediaCollection($collection);
    }
    
    /**
     * İleride dosyayı silmek veya değiştirmek istersek kullanacağımız yardımcı metot.
     */
    public function clearMedia(Model $model, string $collection = 'default'): void
    {
        $model->clearMediaCollection($collection);
    }
}