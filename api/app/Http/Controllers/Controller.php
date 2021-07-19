<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $service;

    public function response($message, $data = null, $code = 200)
    {
        return Response::json([
                                  'message' => __('messages.' . $message),
                                  'data'    => $data,
                              ], $code)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

}
