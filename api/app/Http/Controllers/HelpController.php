<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attachment\DeleteAttachmentIdsRequest;
use App\Http\Requests\Attachment\UploadRequest;
use App\Models\Attachment;
use App\Models\Client\ClassType;
use App\Models\IRS\Category;
use App\Models\PD\PD;
use App\Traits\FilesKit;
use App\Traits\MathKit;
use App\Traits\PDKit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical;

class HelpController extends Controller
{

    use PDKit, FilesKit;

    public function clearCache()
    {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');

        return 'Done! 🌠🌠';

    }

    public function test()
    {

    }

    public function fetchPredefined()
    {
        $data = [];
        $data['class_types'] = ClassType::all();
        $data['categories'] = Category::all();


        return $this->response('success', $data, 200);
    }

    public function uploadAttachments(UploadRequest $request)
    {
        if (isset($_FILES) && count($_FILES) > 0) {
            $data = [];
            foreach ($_FILES as $key => $value) {
                if ($request->hasFile($key)) {
                    $file = $request->file($key);
                    if ($request->type == 'attachments') {
                        $attachment = new Attachment();
                        $attachment->path = $this->saveFile($file);
                        $attachment->save();
                        array_push($data, $attachment->id);
                    } else {
                        $path = $this->saveFile($file, $request->type);
                        array_push($data, $path);
                    }

                }
            }
            return $this->response('success', $data, 200);
        }
        return $this->response('failed', null, 404);
    }

    public function deleteAttachments(DeleteAttachmentIdsRequest $request)
    {
        if (Attachment::whereIn('id', $request->ids)->delete()) {
            return $this->response('success');

        }
        return $this->response('failed', null, 500);
    }

}

