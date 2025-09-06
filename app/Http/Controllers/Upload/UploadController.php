<?php

namespace App\Http\Controllers\Upload;

use App\Http\Controllers\Controller;
use App\Http\Requests\Upload\UploadRequest;
use App\Services\Upload\UploadService;
use Illuminate\Http\Request;

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
