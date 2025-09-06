<?php

namespace App\Http\Controllers\Upload;

use App\Http\Controllers\Controller;
use App\Imports\MMLImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MMLController extends Controller
{
    public function uploadFile(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls']);
        Excel::queueImport(new MMLImport(), $request->file('file'));
        return success();
    }
}
