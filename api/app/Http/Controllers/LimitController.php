<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginateRequest;
use App\Imports\LimitsImport;
use App\Models\Client\ClassType;
use App\Models\Client\Limit;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LimitController extends Controller
{
    public function import(Request $request)
    {
        $data = $request->validate(['path'    => 'required|string',
                                    'year'    => 'required|string|in:' . implode(',', ClassType::getYears()),
                                    'quarter' => 'required|string|in:' . implode(',', ClassType::$QUARTERS),]);

        $checkLimit = Limit::where('year', $data['year'])->where('quarter', $data['quarter'])->get();
        if (count($checkLimit) > 0 and isset($data['replace'])) {
            Limit::where('year', $data['year'])->where('quarter', $data['quarter'])->delete();
        } else if (count($checkLimit) > 0) {
            return $this->response('Failed, you have already added limits for the wanted specification', null);
        }


        Excel::import(new LimitsImport($data['year'], $data['quarter']), $data['path']);

        return $this->response('success');
    }

    public function show(PaginateRequest $request)
    {
        $input = $request->validated();
        $data  = Limit::query();
        $data->join('clients', 'clients.id', '=', 'limits.client_id');
        $data->join('currencies as dc', 'limits.direct_limit_currency_id', '=', 'dc.id');
        $data->join('currencies as udc', 'limits.un_direct_limit_currency_id', '=', 'udc.id');
        $data->select('limits.*', 'clients.name as client_name', 'clients.cif', 'dc.name as direct_limit_currency',
                      'udc.name as un_direct_limit_currency');

        if (isset($input['class_type_id'])) $data->where('clients.class_type_id', $input['class_type_id']);
        if (isset($input['year']) and $input['year']) $data->where('limits.year', $input['year']);
        if (isset($input['quarter']) and $input['quarter']) $data->where('limits.quarter', $input['quarter']);
        $data = $data->paginate($input['page_size']);
        $data = $this->handlePaginate($data, 'limits');


        return $this->response('success', $data);
    }
}
