<?php

namespace App\Http\Services\Public;

use App\Http\Services\File\FileService;
use App\Http\Services\FileManagemant\FileManagementService;
use App\Http\Services\Image\ImageService;
use App\Models\Market\File;
use App\Repositories\Contracts\Market\FileRepositoryInterface;
use Exception;
use Illuminate\Http\UploadedFile;


class MediaStorageService
{
    public function __construct(
        protected ImageService $imageService,
        protected FileService $fileService,
        protected FileManagementService $fileManagementService,
        protected FileRepositoryInterface $fileRepository
    ) {

    }
    protected function prepareDirectory(object $service, string $directory): void
    {
        $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $directory);
        $service->setExclusiveDirectory($normalized);
    }

    public function storeSingleImage(UploadedFile $file, string $directory, string|null $name): string|bool
    {
        if (empty($file)) {
            return null;
        }
        $this->prepareDirectory($this->imageService, $directory);
        $this->imageService->setImageName($name);
        $result = $this->imageService->save($file);

        if ($result === false) {
            throw new Exception('بارگذاری عکس با خطا مواجه شد', 422);
        }
        return $result;
    }

    public function storeMultipleImages(UploadedFile $file, string $directory): array|bool
    {
        if (empty($file)) {
            return [];
        }
        $this->prepareDirectory($this->imageService, $directory);
        $result = $this->imageService->createIndexAndSave($file);
        if ($result === false) {
            throw new Exception('بارگذاری عکس با خطا مواجه شد', 422);
        }
        return $result;
    }
    public function storeFile(UploadedFile $file, string $context, int $contextId, string $directory, string $disk = 'private'): ?File
    {
        if (empty($file)) {
            return null;
        }
        // preparing file path
        $this->prepareDirectory($this->fileService, $directory);

        $this->fileService->setFileSize($file);
        $fileSize = $this->fileService->getFileSize();

        $fileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $fileName = $fileName . '.' . $extension;

        $this->fileService->setFileFormat($file);
        $fileFormat = $this->fileService->getFileFormat();
        $mimeType = $file->getMimeType();

        $filePath = $disk === 'private'
            ? $this->fileService->moveToStorage($file, $fileName)
            : $this->fileService->moveToPublic($file, $fileName);

        return $this->fileRepository->create([
            'filable_type' => $context,
            'filable_id' => $contextId,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_type' => $fileFormat,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'uploaded_by' => auth()->id()
        ]);
    }

    public function storeMultipleFiles(array $files, string $context, int $contextId, string $directory, string $disk = 'private'): array
    {
        if (empty($files)) {
            return [];
        }
        $storedFiles = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $storedFiles[] = $this->storeFile($file, $context, $contextId, $directory, $disk);
            }
        }
        return $storedFiles;
    }

    public function updateImageIfExists(?UploadedFile $newFile, array|string $oldImage, string $directory, string|null $name): array|string|null
    {
        if (empty($newFile)) {
            return $oldImage;
        }

        if (is_array($oldImage)) {
            if (!empty($oldImage['directory'])) {
                // remove old image
                $this->imageService->deleteDirectoryAndFiles($oldImage['directory']);
            }
            // store new image
            return $this->storeMultipleImages($newFile, $directory);
        }

        if (is_string($oldImage)) {
            // remove old image
            $this->imageService->deleteImage($oldImage);
            // store new image
            return $this->storeSingleImage($newFile, $directory, $name);
        }
        return null;
    }

}