<?php

namespace App\Http\Controllers;

use App\Exports\CifExport;
use App\Exports\CifGuaranteeExport;
use App\Exports\DisclosuresExport;
use App\Exports\EadGuaranteeExport;
use App\Exports\EclExport;
use App\Exports\FacilityDisclosureExport;
use App\Http\Requests\ReportFilterRequest;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function cif(ReportFilterRequest $request)
    {
        return Excel::download(new CifExport($request->quarter1, $request->year1,$request->type,$request->limits, $request->class_type_category), 'cif.xlsx');
    }

    public function cifGuarantee(ReportFilterRequest $request)
    {
        return Excel::download(new CifGuaranteeExport($request->quarter1, $request->year1, $request->type,$request->limits, $request->class_type_category), 'cif-guarantee.xlsx');
    }

    public function disclosure(ReportFilterRequest $request)
    {
        return Excel::download(new DisclosuresExport($request->quarter1, $request->year1, $request->quarter2, $request->year2, $request->type,$request->limits, $request->class_type_category), 'disclosure.xlsx');
    }

    public function eadGuarantee(ReportFilterRequest $request)
    {
        return Excel::download(new EadGuaranteeExport($request->quarter1, $request->year1, $request->type,$request->limits, $request->class_type_category), 'ead-guarantee.xlsx');
    }

    public function ecl(ReportFilterRequest $request)
    {
        return Excel::download(new EclExport($request->quarter1, $request->year1, $request->type,$request->limits, $request->class_type_category), 'ecl.xlsx');
    }

    public function facilityDisclosure(ReportFilterRequest $request)
    {
        return Excel::download(new FacilityDisclosureExport($request->quarter1, $request->year1, $request->quarter2, $request->year2,$request->type,$request->limits, $request->class_type_category), 'facility-disclosure.xlsx');
    }
}
