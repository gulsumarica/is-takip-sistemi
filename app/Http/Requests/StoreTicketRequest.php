<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    /**
     * Kullanıcının bu işlemi yapmaya yetkisi var mı?
     */
    public function authorize(): bool
    {
        // Giren müşteri, talep açmaya çalıştığı projede yetkili mi? (Pivot tablo kontrolü)
        return $this->user()->projects()->where('projects.id', $this->project_id)->exists();
    }

    /**
     * Doğrulama kuralları.
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            // Sadece resim formatlarına izin veriyoruz (MIME type güvenlik kontrolü)
            // Zararlı dosyalar (exe, php vb.) sunucu seviyesinde reddedilir.
            'screenshot' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], // Maksimum 5MB
        ];
    }

    /**
     * Kullanıcı dostu hata mesajları.
     */
    public function messages(): array
    {
        return [
            'screenshot.image' => 'Sadece ekran görüntüsü (resim) yükleyebilirsiniz.',
            'screenshot.mimes' => 'Resim formatı sadece JPG, PNG veya WEBP olmalıdır.',
            'screenshot.max' => 'Yüklediğiniz dosya boyutu çok büyük (Maks 5MB).'
        ];
    }
}