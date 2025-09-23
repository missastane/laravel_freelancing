<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Services\FileManagemant\FileManagementService;
use App\Models\Market\File;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    use ApiResponseTrait;
    public function __construct(protected FileManagementService $fileManagementService)
    {
    }

    /**
     * @OA\Delete(
     *     path="/api/file/delete/{file}",
     *     summary="Delete a File of a Model",
     *     description="This endpoint allows the user to `delete an existing File of a Model`.",
     *     tags={"File"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="file",
     *         in="path",
     *         description="The ID of the File to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Model's File deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="فایل با موفقیت حذف شد"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *     @OA\Response(
     *         response=403,
     *         description="You are not authorized to do this action.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما مجاز به انجام این عملیات نیستید")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function delete(File $file)
    {
        try {
            $this->fileManagementService->deleteFile($file);
            return $this->success(null, 'فایل با موفقیت حذف شد');
        } catch (Exception $e) {
            return $this->error();
        }
    }

    /**
     * @OA\Get(
     *     path="/api/final-file/download/{file}",
     *     summary="Download a file",
     *     description="This endpoint allows the user to download a file if they have the proper permissions. If the user does not have permission, a 403 error will be returned.",
     *     operationId="download",
     *     tags={"File"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="file",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The ID of the file to be downloaded."
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The file is being downloaded.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="null"),
     *             @OA\Property(property="data", type="string", example="path/file.format"),
     * 
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="You do not have permission to download this file.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="شما اجازه دسترسی به این فایل را ندارید")
     *         )
     *     ),
     *       @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="جهت انجام عملیات ابتدا وارد حساب کاربری خود شوید")
     *     )),
     *   @OA\Response(
     *         response=404,
     *         description="route not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="bool", example="false"),
     *             @OA\Property(property="message", type="string", example="مسیر مورد نظر پیدا نشد")
     *     )),
     * @OA\Response(
     *         response=500,
     *         description="Unexpected server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="خطای غیرمنتظره در سرور رخ داده است. لطفاً دوباره تلاش کنید")
     *         )
     *     )
     * )
     */
    public function download(File $file)
    {

        if(Gate::denies('canDownload',$file)){
            return $this->error('عملیات غیرمجاز',403);
        }
        return response()->download(
            storage_path('app' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . $file->file_path),
            $file->file_name
        );

    }
}
