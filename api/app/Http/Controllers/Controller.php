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
                                  'message' => $message,
                                  'data'    => $data,
                              ], $code)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function handlePaginate($data, $name = 'data')
    {
        if ($data) {
            return [
                $name       => $data->items(),
                'last_page' => $data->lastPage(),
            ];
        } else return null;
    }

}
