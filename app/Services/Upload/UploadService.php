<?php

namespace App\Services\Upload;

use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Models\Upload;

class UploadService
{
    public Upload|null $file = null;

    public function __construct($id = null, $url = null)
    {
        if (!is_null($id)) $this->file = Upload::query()->find($id);
        if (!is_null($url)) $this->file = Upload::query()->where('url', $url)->first();
    }

    public function set($url): static
    {
        $this->file = Upload::query()->where('url', $url)->first();
        return $this;
    }

    public function uploadFile($request_file, $type): Upload
    {
        $file = $this->upload($request_file);

        $upload = new Upload();
        $upload->hash_name = $file['hash_name'];
        $upload->name = $file['name'];
        $upload->mime_type = $file['mime_type'];
        $upload->type = $type;  // 1-rasm, 2-video, 3-excel
        $upload->size = $file['size'];
        $upload->path = $file['path'];
        $upload->url = $file['url'];
        $upload->status = 0;
        $upload->save();

        return $this->file = $upload;
    }

    public function deleteFileByPath(): JsonResponse
    {
        if (is_null($this->file)) return response_errors(__('errors.not_found', ['name' => __('app.file')]));

        $deleted = $this->unlinkFile($this->file->path);
        if ($deleted) {
            try {
                $this->file->delete();
                return success();
            } catch (QueryException $e) {
                return response_errors(__('errors.file_delete') . ' Exception: ' . $e->getMessage());
            }
        } else {
            return response_errors(__('errors.file_delete'));
        }
    }

    public function confirmAction($id): JsonResponse
    {
        $this->file = Upload::query()->find($id);
        if (is_null($this->file)) return response_errors(__('errors.not_found', ['name' => __('app.file')]));
        $confirm = $this->confirmFile('uploads');
        if ($confirm === false) {
            return success(['item' => $this->file]);
        }
        return response_errors($confirm['error']);
    }

    /**
     * @param UploadedFile $file
     * @return array|void
     */
    public function upload(UploadedFile $file)
    {
        if ($file->getError() === 0) {
            try {

                $path = $file->store('public/temp');
                $hash_name = $file->hashName();

                return [
                    'hash_name' => $hash_name,
                    'name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'path' => Storage::path($path),
                    'url' => Storage::url($path)
                ];
            } catch (FileException $e) {
                throwError(__('errors.file_upload') . ' ' . $e->getMessage());
            }
        } else {
            throwError(__('errors.file_upload'));
        }
    }

    public function confirm($result = false, ?string $directory = 'uploads'): bool|array|static
    {
        if (is_null($this->file)) return ['error' => __('errors.not_found', ['name' => __('app.file')])];
        $result_confirm = $this->confirmFile($directory);
        return $result ? $result_confirm : $this;
    }

    public function confirmFile(string $directory): bool|array
    {
        if ($this->file->status == 0) {
            $hash_name = $this->file->hash_name;
            $r_path = $this->randomPath();
            $new_path = "public/{$directory}/{$r_path}/$hash_name";
            $is_moved = Storage::move('public/temp/' . $hash_name, $new_path);
            if ($is_moved) {
                $this->file->path = Storage::path($new_path);
                $this->file->url = Storage::url($new_path);
                $this->file->status = 1;
                $this->file->save();
                return false;
            }
            return ['error' => __('errors.file_move')];
        }
        return ['error' => __('errors.file_confirmed')];
    }

    public function unlinkFile($path): bool
    {
        if (file_exists($path)) {
            unlink($path);
            return true;
        }
        return false;
    }

    public static function checkExists(string $path): bool
    {
        return (bool) Upload::query()->where('path', $path)->first();
    }

    public function randomPath(): string
    {
        $file_name = md5(microtime(true));
        $first_dir = substr($file_name, 0, 1);
        $second_dir = substr($file_name, 1, 1);

        return "{$first_dir}/{$second_dir}";
    }
}
