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
}
