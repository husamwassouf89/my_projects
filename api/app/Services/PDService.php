<?php


namespace App\Services;


use App\Imports\PDImport;
use App\Models\Attachment;
use App\Models\Client\ClassType;
use App\Models\PD\PD;
use App\Traits\FilesKit;
use App\Traits\MathKit;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical;


class PDService extends Service
{
    use FilesKit, MathKit;

    public function index(array $input)
    {
        $data = PD::selectIndex()->paginate($input['page_size']);
        return $this->handlePaginate($data, 'pds');
    }

    public function store($input)
    {
        $pd                            = new PD();
        $pd->class_type_id             = $input['class_type_id'];
        $pd->year                      = $input['year'];
        $pd->quarter                   = $input['quarter'];
        $pd->eco_parameter_base_value  = $input['eco_parameter_base_value'];
        $pd->eco_parameter_mild_value  = $input['eco_parameter_mild_value'];
        $pd->eco_parameter_heavy_value = $input['eco_parameter_heavy_value'];

        $pd->eco_parameter_base_weight  = $input['eco_parameter_base_weight'];
        $pd->eco_parameter_mild_weight  = $input['eco_parameter_mild_weight'];
        $pd->eco_parameter_heavy_weight = $input['eco_parameter_heavy_weight'];
        $pd->path                       = $input['path'];
        $pd->save();

        $attachmentIds = $input['attachment_ids'] ?? null;

        if ($attachmentIds) {
            foreach ($attachmentIds as $id) {
                $attachment                      = Attachment::find($id);
                $attachment->attachmentable_id   = $pd->id;
                $attachment->attachmentable_type = 'App\Models\PD\PD';
                $attachment->save();
            }
        }

        Excel::import(new PDImport($pd), $input['path']);

        return $this->show($pd->id);
    }

    public function show($id)
    {
        return $this->handleShow(PD::where('p_d_s.id', $id)->first());
    }

    private function handleShow(PD $pd)
    {
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

        if (count($values) <= 0) return [];


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
        $finalCalibratedUsedPD = [];


        for ($i = 0; $i < count($pdTTCAfterRegression); $i++) {
            if ($finalCalibratedWeightedPD[$i] >= 0.0005) $finalCalibratedUsedPD[$i] = $finalCalibratedWeightedPD[$i];
            else $finalCalibratedUsedPD[$i] = 0.0005;

        }


        return [

            "id"                           => $pd->id,
            "eco_parameter_base_value"     => $pd->eco_parameter_base_value,
            "eco_parameter_mild_value"     => $pd->eco_parameter_mild_value,
            "eco_parameter_heavy_value"    => $pd->eco_parameter_heavy_value,
            "eco_parameter_base_weight"    => $pd->eco_parameter_base_weight,
            "eco_parameter_mild_weight"    => $pd->eco_parameter_mild_weight,
            "eco_parameter_heavy_weight"   => $pd->eco_parameter_heavy_weight,
            "created_at"                   => $pd->created_at,
            'pd'                           => $pdArray,
            'default_rate'                 => $defaultRate,
            'pd_ttc'                       => $pdTTC,
            'pd_ttc_after_regression'      => $pdTTCAfterRegression,
            'asset_correlation'            => $assetCorrelation,
            'ttc_to_pit'                   => $ttc_to_pit,
            'inclusion'                    => $inclusion,
            'final_calibrated_weighted_pd' => $finalCalibratedWeightedPD,
            'final_calibrated_used_PD'     => $finalCalibratedUsedPD,
            'attachments'                  => $pd->attachments
        ];


    }

    public function classTypeYears($id)
    {
        $allYears = [];
        for ($i = 1990; $i <= Date('Y'); $i++) {
            array_push($allYears, $i);
        }

        $years          = PD::where('class_type_id', $id)->select('year')->get()->pluck('year')->toArray();
        $availableYears = array_values(array_diff($allYears, $years));

        $allQuarters = ClassType::$QUARTERS;

        $data = [];
        foreach ($availableYears as $year) {
            $quarters          = PD::where('class_type_id', $id)
                                   ->where('year', $year)
                                   ->select('year')->get()->pluck('quarter')->toArray();
            $availableQuarters = array_values(array_diff($allQuarters, $quarters));
            array_push($data, ['year' => $year, 'quarters' => $availableQuarters]);

        }

        return $data;

    }

    public function destory($id)
    {
        if (PD::where('id', $id)->delete()) {
            return true;
        }
        return false;
    }

    public function getPdByYearQuarter($year, $quarter)
    {
        $pd = PD::where('year', $year)->where('quarter', $quarter)->orderBy('id', 'desc')->first();
        if ($pd) {
            return $this->handleShow($pd);
        } else {
            return -1;
        }
    }

    public function showRaw($id)
    {
        return PD::where('id', $id)->with('values')->first();
    }


}
