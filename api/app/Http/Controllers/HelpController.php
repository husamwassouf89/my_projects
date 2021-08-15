<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attachment\DeleteAttachmentIdsRequest;
use App\Http\Requests\Attachment\UploadRequest;
use App\Models\Attachment;
use App\Models\Client\ClassType;
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
        $pd = PD::orderBy('id', 'desc')->first();

        $values               = $pd->values()->with('row', 'column')->get();
        $pdArray              = [];
        $defaultRate          = [];
        $pdTTCAfterRegression = [];
        $grades               = $pd->classType->grades()->orderBy('serial_no')->select('name')->get()->pluck('name')->toArray();

        foreach ($grades as $key => $item) {
            if ($key >= 7) {
                unset($grades[$key]);
                continue;
            }
            $grades[$key] = (int)$item;
        }


        foreach ($values as $value) {
            $row    = $value->row;
            $column = $value->column;

            $pdArray[$row->serial_no][$column->serial_no] = $value->value;

            if ($column->serial_no >= 7) {
                if (!isset($defaultRate[$row->serial_no])) $defaultRate[$row->serial_no][0] = 0;
                $defaultRate[$row->serial_no][0] = $value->value + $defaultRate[$row->serial_no][0];
            }

        }
        $pdTTC = $this->matrixMultiplication($pdArray, $defaultRate);
        for ($i = 0; $i < count($defaultRate); $i++) {
            $defaultRate[$i] = $defaultRate[$i][0];
        }


        $newPdTTC = [];
        for ($i = 0; $i < count($pdTTC); $i++) {
            if ($i >= 7) {
                $pdTTC[$i] = 1;
            } else {
                $pdTTC[$i]    = $pdTTC[$i][0];
                $newPdTTC[$i] = $pdTTC[$i];
            }
        }

        $trend = new Statistical\Trends();

        $slope = $trend->LINEST($newPdTTC, $grades, TRUE, TRUE)[0][0];

        for ($i = 0; $i < count($pdTTC); $i++) {
            if ($i >= 7) {
                $pdTTCAfterRegression[$i] = 1;
            } else {
                $pdTTCAfterRegression[$i] = 0.0005 + $grades[$i] * $slope;
            }
        }
        $assetCorrelation = [];

        for ($i = 0; $i < count($pdTTCAfterRegression); $i++) {

            if ($i >= 7) {
                $assetCorrelation[$i] = 1;
            } else {
                $firstPart            = (0.12 * (1 - exp(-50 * $pdTTCAfterRegression[$i]))) / (1 - exp(-50));
                $secondPart           = (0.24 * (1 - (1 - exp(-50 * $pdTTCAfterRegression[$i])))) / (1 - exp(-50));
                $assetCorrelation[$i] = $firstPart + $secondPart;
            }
        }

        $ttc_to_pit = [];
        $norm       = new    \PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions\StandardNormal();
        $error      = new \PhpOffice\PhpSpreadsheet\Calculation\Logical\Conditional();

        for ($i = 0; $i < count($pdTTCAfterRegression); $i++) {

            if ($i >= 7) {
                $ttc_to_pit[$i] = 1;
            } else {
                $ttc_to_pit[$i] = $error->IFERROR($norm->cumulative($norm->inverse($pdTTCAfterRegression[$i]) / sqrt(1 - $assetCorrelation[$i])), 0);
            }
        }

        $inclusion = [
            'base'  => [],
            'mild'  => [],
            'heavy' => [],
        ];
        for ($i = 0; $i < count($pdTTCAfterRegression); $i++) {
            if ($i >= 7) {
                $inclusion['base'][$i]  = 1;
                $inclusion['mild'][$i]  = 1;
                $inclusion['heavy'][$i] = 1;
            } else {
                $inclusion['base'][$i]  = $error->IFERROR($norm->cumulative(($norm->inverse($pdTTCAfterRegression[$i]) - SQRT($assetCorrelation[$i]) * $pd->eco_parameter_base_value) / SQRT(1 - $assetCorrelation[$i])), 0);
                $inclusion['mild'][$i]  = $error->IFERROR($norm->cumulative(($norm->inverse($pdTTCAfterRegression[$i]) - SQRT($assetCorrelation[$i]) * $pd->eco_parameter_mild_value) / SQRT(1 - $assetCorrelation[$i])), 0);
                $inclusion['heavy'][$i] = $error->IFERROR($norm->cumulative(($norm->inverse($pdTTCAfterRegression[$i]) - SQRT($assetCorrelation[$i]) * $pd->eco_parameter_heavy_value) / SQRT(1 - $assetCorrelation[$i])), 0);
            }
        }
        $finalCalibratedWeightedPD = [];
        for ($i = 0; $i < count($pdTTCAfterRegression); $i++) {
            if ($i >= 7) {
                $finalCalibratedWeightedPD[$i] = 1;
                $finalCalibratedWeightedPD[$i] = 1;
                $finalCalibratedWeightedPD[$i] = 1;
            } else {
                $finalCalibratedWeightedPD[$i] = ($inclusion['base'][$i] * $pd->eco_parameter_base_weight)
                                                 + ($inclusion['mild'][$i] * $pd->eco_parameter_mild_weight)
                                                 + ($inclusion['heavy'][$i] * $pd->eco_parameter_heavy_weight);
            }
        }


        for ($i = 0; $i < count($pdTTCAfterRegression); $i++) {
            if ($finalCalibratedWeightedPD[$i] >= 0.0005) $finalCalibratedUsedPD[$i] = $finalCalibratedWeightedPD[$i];
            else $finalCalibratedUsedPD[$i] = 0.0005;

        }


        return $finalCalibratedUsedPD;

        return [
            'pd'                      => $pdArray,
            'default_rate'            => $defaultRate,
            'pd_ttc'                  => $pdTTC,
            'pd_ttc_after_regression' => $pdTTCAfterRegression,
        ];
    }

    public function fetchPredefined()
    {
        $data                = [];
        $data['class_types'] = ClassType::all();


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
                        $attachment       = new Attachment();
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

