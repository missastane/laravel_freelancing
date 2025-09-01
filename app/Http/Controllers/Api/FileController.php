<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\FileManagemant\FileManagementService;
use App\Models\Market\File;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;

class FileController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected FileManagementService $fileManagementService)
    {
    }

    public function delete(File $file)
    {
        try {
            $this->fileManagementService->deleteFile($file);
            return $this->success(null, 'فایل با موفقیت حذف شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }
}
