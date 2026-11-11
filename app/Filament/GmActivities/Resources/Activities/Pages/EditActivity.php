<?php

namespace App\Filament\GmActivities\Resources\Activities\Pages;

use App\Filament\GmActivities\Resources\Activities\ActivityResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditActivity extends EditRecord
{
    protected static string $resource = ActivityResource::class;

    protected array $uploadedAttachments = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pdf')
                ->label('تصدير بطاقة PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn (): string => route('activities.single.pdf', ['activity' => $this->record]))
                ->openUrlInNewTab(),
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['uploaded_attachments'] = $this->record->attachments()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn ($att) => [(string) \Illuminate\Support\Str::uuid() => $att->file_path])
            ->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->uploadedAttachments = (array) ($data['uploaded_attachments'] ?? []);
        unset($data['uploaded_attachments']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncUploadedAttachments();
    }

    protected function syncUploadedAttachments(): void
    {
        $existingAttachments = $this->record->attachments()->get()->keyBy('file_path');
        $newPaths = array_values(array_filter(
            $this->uploadedAttachments,
            fn ($path) => is_string($path) && filled($path)
        ));

        // 1. حذف المرفقات التي تم استبعادها أو إزالتها من النموذج
        foreach ($existingAttachments as $path => $attachment) {
            if (! in_array($path, $newPaths)) {
                if (Storage::disk('private')->exists($path)) {
                    Storage::disk('private')->delete($path);
                }
                $attachment->delete();
            }
        }

        // 2. تحديث الترتيب للمرفقات الحالية أو إنشاء سجلات جديدة للمرفقات المضافة
        foreach ($newPaths as $index => $filePath) {
            $disk = Storage::disk('private')->exists($filePath) ? Storage::disk('private') : Storage::disk('local');
            $fullPath = $disk->path($filePath);
            $fileName = basename($filePath);
            $fileType = file_exists($fullPath) ? (mime_content_type($fullPath) ?: 'image/jpeg') : 'image/jpeg';
            $fileSize = file_exists($fullPath) ? (filesize($fullPath) ?: 0) : 0;

            if (isset($existingAttachments[$filePath])) {
                $existingAttachments[$filePath]->updateQuietly([
                    'sort_order' => $index,
                    'is_primary' => ($index === 0),
                    'file_name' => $existingAttachments[$filePath]->file_name ?: $fileName,
                    'file_type' => $existingAttachments[$filePath]->file_type ?: $fileType,
                    'file_size' => $existingAttachments[$filePath]->file_size ?: $fileSize,
                ]);
            } else {
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
}
