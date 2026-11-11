<?php

namespace App\Filament\GmActivities\Resources\Activities\Pages;

use App\Filament\GmActivities\Resources\Activities\ActivityResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;

class CreateActivity extends CreateRecord
{
    protected static string $resource = ActivityResource::class;

    protected array $uploadedAttachments = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        // حفظ مسارات الصور المرفوعة وإزالتها من بيانات جدول النشاطات الأساسي
        $this->uploadedAttachments = (array) ($data['uploaded_attachments'] ?? []);
        unset($data['uploaded_attachments']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncUploadedAttachments();
    }

    protected function syncUploadedAttachments(): void
    {
        $filePaths = array_values(array_filter(
            $this->uploadedAttachments,
            fn ($path) => is_string($path) && filled($path)
        ));

        foreach ($filePaths as $index => $filePath) {
            $disk = Storage::disk('private')->exists($filePath) ? Storage::disk('private') : Storage::disk('local');
            $fullPath = $disk->path($filePath);
            $fileName = basename($filePath);
            $fileType = file_exists($fullPath) ? (mime_content_type($fullPath) ?: 'image/jpeg') : 'image/jpeg';
            $fileSize = file_exists($fullPath) ? (filesize($fullPath) ?: 0) : 0;

            $this->record->attachments()->create([
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'sort_order' => $index,
                'is_primary' => ($index === 0),
            ]);
        }
    }
}
