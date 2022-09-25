<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginateRequest;
use App\Models\Client\DocumentType;
use App\Models\Client\GuaranteeLGD;
use App\Models\Client\Predefined;
use App\Models\Setting;
use App\Models\Value;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function showPredefined(PaginateRequest $request)
    {
        $input = $request->validated();
        $data  = Predefined::query();
        $data->whereIn('class_types.category',['financial']);
        $data->join('class_types', 'class_types.id', '=', 'predefineds.class_type_id');
        $data->join('grades', 'grades.id', '=', 'predefineds.grade_id');
        $data->join('stages', 'stages.id', '=', 'predefineds.stage_id');
        $data->select('predefineds.*', 'class_types.name as class_type_name',
                      'grades.name  as grade_name', 'stages.name  as stage_name');

        if (isset($input['class_type_id'])) $data->where('predefineds.class_type_id', $input['class_type_id']);
        $data = $data->paginate($input['page_size']);
        $data = $this->handlePaginate($data, 'pre-defined');

        return $this->response('success', $data);
    }

    public function showGuaranteeLGD(PaginateRequest $request)
    {
        $input = $request->validated();
        $data  = GuaranteeLGD::query();
//        $data->whereIn('class_types.sub_category');
        $data->join('class_types', 'class_types.id', '=', 'guarantee_l_g_d_s.class_type_id');
        $data->join('guarantees', 'guarantees.id', '=', 'guarantee_l_g_d_s.guarantee_id');
        $data->join('stages', 'stages.id', '=', 'guarantee_l_g_d_s.stage_id');
        $data->select('guarantee_l_g_d_s.id','guarantee_l_g_d_s.ratio as value', 'class_types.name as class_type_name', 'stages.name  as stage_name', 'guarantees.name as guarantee_name');

        if (isset($input['class_type_id'])) $data->where('guarantee_l_g_d_s.class_type_id', $input['class_type_id']);
        $data = $data->paginate($input['page_size']);
        $data = $this->handlePaginate($data, 'lgd-guarantee');

        return $this->response('success', $data);
    }

    public function updatePredefined($id, Request $request)
    {
        $pre = Predefined::findOrFail($id);
        if ($pre->classType->sub_category == 'central bank' and $request->pd != -1) {
            $pre->pd = $request->pd;
        } //* It's a Central Banks *//

        if ($request->lgd) {
            $pre->lgd = $request->lgd;
        }
        $pre->save();

        return $this->response('success', null);

    }

    public function updateGuaranteeLGD($id, Request $request)
    {
        $pre = GuaranteeLGD::findOrFail($id);
        $pre->ratio = min(1,max(0,$request->value ?? 0));
        $pre->save();
        return $this->response('success', null);
    }

    public function showDocumentTypes(PaginateRequest $request)
    {
        $input = $request->validated();
        $data  = DocumentType::query();
        $data  = $data->paginate($input['page_size']);
        $data  = $this->handlePaginate($data, 'document_types');

        return $this->response('success', $data);
    }

    public function updateDocumentType($id, Request $request)
    {
        $type = DocumentType::findOrFail($id);
        if ($request->ccf) {
            $type->ccf = $request->ccf;
        }
        $type->save();

        return $this->response('success', null);
    }

    public function getLocked()
    {
        $locked = Setting::all();
        return $this->response('Success', $locked);
    }

    public function togglePeriodLock($quarter, $year)
    {
        $period = Setting::where('quarter', $quarter)->where('year', $year)->first();

        if ($period) {
            $period->delete();
            $msg = 'This period has been unlocked';
        } else {
            Setting::create(['quarter' => $quarter, 'year' => $year]);
            $msg = 'This period has been locked';
        }

        return $this->response('Success, ' . $msg, null);

    }

    public function getValues()
    {
        $values = Value::all();
        return $this->response('Success', $values);
    }

    public function updateValue($id, $value)
    {
        $v        = Value::findOrFail($id);
        $v->value = $value;
        $v->save();
        return $this->response('Success', null);
    }
}
