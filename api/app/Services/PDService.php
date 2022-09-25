<?php


namespace App\Services;


use App\Imports\PDImport;
use App\Models\Attachment;
use App\Models\Client\ClassType;
use App\Models\PD\PD;
use App\Models\Value;
use App\Traits\FilesKit;
use App\Traits\MathKit;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Calculation\Statistical;


class PDService extends Service
{
    use FilesKit, MathKit;

    private $noReg, $noEco;

    public function __construct()
    {
        $this->noReg = ['4', '6', '7'];
        $this->noEco = ['6', '7'];
        $this->exp   = new \PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Exp;
        $this->sqrt  = new \PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Sqrt;
    }

    public function index(array $input)
    {
        $data = PD::query();
        $data->selectIndex();
        if (isset($input['year']) and $input['year']) $data->where('year', $input['year']);
        if (isset($input['quarter']) and $input['quarter']) $data->where('quarter', $input['quarter']);
        if (isset($input['class_type_id']) and $input['class_type_id']) $data->where('p_d_s.class_type_id', $input['class_type_id']);
        $data = $data->paginate($input['page_size']);

        return $this->handlePaginate($data, 'pds');
    }

    public function store($input)
    {
        $checkPd = PD::where('class_type_id', $input['class_type_id'])
                     ->where('year', $input['year'])->where('quarter', $input['quarter'])->first();
        if ($checkPd and isset($input['replace']) and $input['replace'] == 'yes') {
            $checkPd->delete();
        } else if ($checkPd) {
            return -1;
        }
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

        try {
            Excel::import(new PDImport($pd), $input['path']);
        } catch (\Exception $e) {
            $pd->delete();
            return -2;
        }

        return $this->show($pd->id);
    }

    public function show($id)
    {
        return $this->handleShow(PD::where('p_d_s.id', $id)->first());
    }

    private function handleShow(PD $pd)
    {
        //  Get Values
        $values               = $pd->values()->with('row', 'column')->get();
        $pdArray              = [];
        $defaultRate          = [];
        $pdTTCAfterRegression = [];
        $classType            = $pd->classType;
        $grades               = $classType->grades()->orderBy('serial_no')->select('serial_no')->get()->pluck('serial_no')->toArray();

        // Fix the factor: pd for any row after factor is 1
        if ($pd->class_type_id == 4) {
            $factor = 2;
        } else {
            $factor = 7;
        }

        // Convert to 2D array
        foreach ($grades as $key => $item) {
            if ($key >= $factor) {
                unset($grades[$key]);
                continue;
            }
            $grades[$key] = (int)$item + 1;
        }

        if (count($values) <= 0) {
            return -1;
        }

        foreach ($values as $value) {
            $row    = $value->row;
            $column = $value->column;

            $pdArray[$row->serial_no][$column->serial_no] = $value->value;

            if ($column->serial_no >= $factor) {
                if (!isset($defaultRate[$row->serial_no])) $defaultRate[$row->serial_no][0] = 0;
                $defaultRate[$row->serial_no][0] = $value->value + $defaultRate[$row->serial_no][0];
            }

        }

        // PD-TTC this function is written by me, we can use the PHP-office one too
        $pdTTC = $this->matrixMultiplication($pdArray, $defaultRate);
        for ($i = 0; $i < count($defaultRate); $i++) {
            $defaultRate[$i] = $defaultRate[$i][0];
        }

        $newPdTTC = [];
        for ($i = 0; $i < count($pdTTC); $i++) {
            if ($i >= $factor) {
                $pdTTC[$i] = 1;
            } else {

                $pdTTC[$i]    = $pdTTC[$i][0];
                $newPdTTC[$i] = $pdTTC[$i];
            }
        }


        // For Regression (not for all classes: [Retail, Abroad, Investment])
        if (!in_array($pd->class_type_id, $this->noReg)) {
            $trend = new Statistical\Trends();
            $slope = $trend->LINEST($newPdTTC, $grades, TRUE, TRUE)[0][0];
            for ($i = 0; $i < count($pdTTC); $i++) {
                if ($i >= $factor) {
                    $pdTTCAfterRegression[$i] = 1;
                } else {
                    $pdTTCAfterRegression[$i] = 0.0005 + $grades[$i] * $slope;
                }
            }
        } else {
            $pdTTCAfterRegression = $pdTTC;
        }
        $assetCorrelation = [];

        for ($i = 0; $i < count($pdTTCAfterRegression); $i++) {

            if ($i >= $factor) {
                $assetCorrelation[$i] = 1;
            } else {

                // Retail coefficients are different
                if ($pd->class_type_id == 4) {
                    $c1 = 0.03;
                    $c2 = 0.16;
                    $c3 = -35.0;
                } else {
                    $c1 = 0.12;
                    $c2 = 0.24;
                    $c3 = -50;
                }
                $preReq = null;

                $firstPart            = (double)($c1 * (1 - $this->exp->evaluate($c3 * $pdTTCAfterRegression[$i]))) / (1 - $this->exp->evaluate($c3));
                $secondPart           = (double)($c2 * (1 - (1 - $this->exp->evaluate($c3 * $pdTTCAfterRegression[$i])))) / (1 - $this->exp->evaluate($c3));
                $assetCorrelation[$i] = $firstPart + $secondPart;
            }
        }


        $ttc_to_pit = [];
        $norm
                    = new    \PhpOffice\PhpSpreadsheet\Calculation\Statistical\Distributions\StandardNormal();
        $error
                    = new \PhpOffice\PhpSpreadsheet\Calculation\Logical\Conditional();

        // Even the name is regression but the regression is not always applied!
        for ($i = 0; $i < count($pdTTCAfterRegression); $i++) {

            if ($i >= $factor) {
                $ttc_to_pit[$i] = 1;
            } else {
                $value = $pdTTCAfterRegression[$i];
                if (in_array($pd->class_type_id, $this->noReg,) and in_array($pd->class_type_id, $this->noEco)) {
                    $value = $defaultRate[$i];
                }

                $ttc_to_pit[$i] = $error->IFERROR($norm->cumulative($norm->inverse($value) / $this->sqrt->sqrt(1 - $assetCorrelation[$i])), 0);
            }
        }

        $inclusion = [
            'base'  => [],
            'mild'  => [],
            'heavy' => [],
        ];
        for ($i = 0; $i < count($pdTTCAfterRegression); $i++) {
            if ($i >= $factor) {
                $inclusion['base'][$i]  = 1;
                $inclusion['mild'][$i]  = 1;
                $inclusion['heavy'][$i] = 1;
            } else {

//                $n                      = -2.487429559;
//                $sq1                    = 0.454963807;
//                $sq2                    = 0.890509929;
//                $d = $sq1 * (double)$pd->eco_parameter_heavy_value;
//                $inclusion['heavy'][$i] = $error->IFERROR((double)$norm->cumulative(($n - $d ) / $sq2), 0);

                $inclusion['base'][$i]  = $error->IFERROR((double)$norm->cumulative(($norm->inverse($pdTTCAfterRegression[$i]) - SQRT($assetCorrelation[$i]) * (double)$pd->eco_parameter_base_value) / SQRT(1 - $assetCorrelation[$i])), 0);
                $inclusion['mild'][$i]  = $error->IFERROR((double)$norm->cumulative(($norm->inverse($pdTTCAfterRegression[$i]) - SQRT($assetCorrelation[$i]) * (double)$pd->eco_parameter_mild_value) / SQRT(1 - $assetCorrelation[$i])), 0);
                $inclusion['heavy'][$i] = $error->IFERROR((double)$norm->cumulative(($norm->inverse($pdTTCAfterRegression[$i]) - SQRT($assetCorrelation[$i]) * (double)$pd->eco_parameter_heavy_value) / SQRT(1 - $assetCorrelation[$i])), 0);
            }
        }
//        =IFERROR(NORMSDIST((NORMSINV(BU4)-SQRT(BW4)*BZ4)/SQRT(1-BW4))
        $finalCalibratedWeightedPD = [];
        if (in_array($pd->class_type_id, $this->noEco)) {
            for ($i = 0; $i < count($pdTTCAfterRegression); $i++) {
                $finalCalibratedWeightedPD[$i] = $ttc_to_pit[$i];
            }
        } else {
            for ($i = 0; $i < count($pdTTCAfterRegression); $i++) {
                if ($i >= $factor) {
                    $finalCalibratedWeightedPD[$i] = 1;
                } else {
                    $finalCalibratedWeightedPD[$i] = ((double)$inclusion['base'][$i] * (double)$pd->eco_parameter_base_weight)
                                                     + ((double)$inclusion['mild'][$i] * (double)$pd->eco_parameter_mild_weight)
                                                     + ((double)$inclusion['heavy'][$i] * (double)$pd->eco_parameter_heavy_weight);
                }
            }
        }

        $finalCalibratedUsedPD = [];
        // Get the Min value for the pd
        $value = Value::find(2);
        for ($i = 0; $i < count($pdTTCAfterRegression); $i++) {
            $finalCalibratedUsedPD[$i] = max($finalCalibratedWeightedPD[$i], $value->value);
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
        for ($i = 2018; $i <= Date('Y'); $i++) {
            array_push($allYears, $i);
        }

        $years          = PD::where('class_type_id', $id)->select('year')->get()->pluck('year')->toArray();
        $availableYears = array_values(array_diff($allYears, $years));

        $allQuarters = ClassType::$QUARTERS;

        $data = [];
        foreach ($availableYears as $year) {
            $quarters          = PD::where('class_type_id', $id)
                                   ->where('year', $year)
                                   ->select('quarter')->get()->pluck('quarter')->toArray();
            $availableQuarters = array_values(array_diff($allQuarters, $quarters));
            array_push($data, ['year' => $year, 'quarters' => $availableQuarters]);

        }

        return $data;

    }

    public function insertedYears()
    {
        $years = PD::select('year')->get()->pluck('year')->toArray();
        $data  = [];
        foreach ($years as $year) {
            $quarters = PD::where('year', $year)
                          ->select('quarter')->get()->pluck('quarter')->toArray();
            array_push($data, ['year' => $year, 'quarters' => $quarters]);
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

    public function getPdByYearQuarter($year, $quarter, $classTypeId)
    {
        $pd = PD::where('year', $year)->where('quarter', $quarter)->orderBy('id', 'desc')
                ->where('class_type_id', $classTypeId)->first();
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
