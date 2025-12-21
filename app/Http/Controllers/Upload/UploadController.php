<?php

namespace App\Http\Controllers\Upload;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Upload\UploadService;
use App\Http\Requests\Upload\UploadRequest;

class UploadController extends Controller
{
    public function __construct(
        protected UploadService $service
    )
    {
    }

    public function upload(UploadRequest $request)
    {
        $data = $this->service->uploadFile($request->file('file'), 1);
        return success($data);
    }
}
