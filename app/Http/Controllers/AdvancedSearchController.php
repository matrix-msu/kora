<?php namespace App\Http\Controllers;

use App\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use Illuminate\Support\Str;

class AdvancedSearchController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Advanced Search Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles advanced searches for a form
    |
    */

    /**
     * Constructs controller and makes sure user is authenticated.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * The advanced search home page.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
    public function index($pid, $fid) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $form = FormController::getForm($fid);

        return view('advancedSearch.index', compact("form"));
    }

    /**
     * Performs the advanced search and stores results in the session.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return View
     */
    public function search($pid, $fid, Request $request) {

        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $results = [];
        $form = FormController::getForm($fid);

        //Need these for negative searches
        $recModel = new Record(array(),$fid);
        $notRids = $recModel->newQuery()->pluck('id')->toArray();

        $processed = $this->processRequest($request->all(), $form->layout);
        foreach($processed as $flid => $query) {
            $field = $form->layout['fields'][$flid];
            if(array_diff(array_keys($query),array('negative','empty')) == [])
                $result = [];
            else
                $result = $form->getFieldModel($field['type'])->advancedSearchTyped($flid, $query, $recModel, $form);

            //This is a negative search so we want the opposite results of what the search would produce
            if(isset($query['negative']))
                $result = array_diff($notRids,$result);

            if(isset($query['empty'])) {
                $empty = $form->getFieldModel($field['type'])->getEmptyFieldRecords($flid, $recModel);
                $this->imitateMerge($result, $empty);
            }

            $results[] = $result;
        }

        $rids = array_pop($results);

        // This functions to make sure that a record satisfies all search parameters.
        foreach($results as $result) {
            $rids = $this->imitateIntersect($rids, $result);
        }

        if(empty($rids))
            $rids = [];

        Session::put('advanced_search_recents', $rids);

        sort($rids);

        $recBuilder = $recModel->newQuery()->whereIn("id", $rids);
        $total = $recBuilder->newQuery()->count();

        $pagination = 10;
        $order_type = "updated_at";
        $order_direction = "desc";
        $records = $recBuilder->orderBy($order_type, $order_direction)->paginate($pagination);

        $form = FormController::getForm($fid);

        return view('advancedSearch.results', compact("form", "records", "total"));
    }

    private function imitateMerge(&$array1, &$array2) {
        foreach($array2 as $i) {
            $array1[] = $i;
        }
    }

    private function imitateIntersect($s1,$s2) {
        sort($s1);
        sort($s2);
        $i=0;
        $j=0;
        $N = count($s1);
        $M = count($s2);
        $intersection = array();

        while($i<$N && $j<$M) {
            if($s1[$i]<$s2[$j]) $i++;
            else if($s1[$i]>$s2[$j]) $j++;
            else {
                $intersection[] = $s1[$i];
                $i++;
                $j++;
            }
        }

        return $intersection;
    }

    /**
     * Gets the most recent advanced search.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
    public function recent($pid, $fid) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $recModel = new Record(array(),$fid);

        $rids = Session::get('advanced_search_recents');
        if(is_null($rids))
            $rids = [];

        sort($rids);

        $recBuilder = $recModel->newQuery()->whereIn("id", $rids);
        $total = $recBuilder->newQuery()->count();

        $pagination = app('request')->input('page-count') === null ? 10 : app('request')->input('page-count');
        $order = app('request')->input('order') === null ? 'lmd' : app('request')->input('order');
        $order_type = substr($order, 0, 2) === "lm" ? "updated_at" : "rid";
        $order_direction = substr($order, 2, 3) === "a" ? "asc" : "desc";
        $records = $recBuilder->orderBy($order_type, $order_direction)->paginate($pagination);

        $form = FormController::getForm($fid);

        return view('advancedSearch.results', compact("form", "records", "total"));
    }

    /**
     * Handles an advanced search from the API. We need the results back directly, rather than a view to display them.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @param  boolean $negative - Get opposite results of the search
     * @return array - Record ID search results
     */
    public function apisearch($pid, $fid, $data, $negative) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $results = [];
        $form = FormController::getForm($fid);

        //Need these for negative searches
        $recModel = new Record(array(),$fid);

        //Process data
        foreach($data as $fieldName => $query) {
            $flid = fieldMapper($fieldName,$pid,$fid);
            $field = $form->layout['fields'][$flid];
            $result = $form->getFieldModel($field['type'])->advancedSearchTyped($flid, $query, $recModel, $form, $negative);

            $results[] = $result;
        }

        $rids = array_pop($results);

        // This functions to make sure that a record satisfies all search parameters.
        foreach($results as $result) {
            $rids = $this->imitateIntersect($rids, $result);
        }

        if(empty($rids))
            $rids = [];

        sort($rids);

        return $rids;
    }

    /**
     * Takes the request variables for an advanced search an processed them for use.
     *
     * @param  array $request - Variables from the request
     * @param  array $layout - Layout of form
     * @return array - Processed array
     */
    private function processRequest(array $request, $layout) {
        $processed = [];

        foreach($request as $key => $value) {
            $fields = [];
            if(array_key_exists($key,$layout['fields'])) {
                $field = $layout['fields'][$key];
                if($field['type'] == 'Combo List') {
                    foreach (array_keys($request) as $tmpKey) {
                        foreach(['one', 'two'] as $seq) {
                            if (Str::contains($tmpKey, '_' . $seq)) {
                                array_push($fields,
                                    [
                                        'flid' => $field[$seq]['flid'] . '_' . $seq,
                                        'type' => $field[$seq]['type']
                                    ]
                                );
                            }
                        }
                    }
                } else {
                    array_push($fields,
                        [
                            'flid' => $key,
                            'type' => $field['type']
                        ]
                    );
                }

                foreach ($fields as $tmpField) {
                    $flid = $tmpField['flid'];
                    switch($tmpField['type']) {
                        case 'Integer':
                        case 'Float':
                            if($request[$flid.'_left'] != '' | $request[$flid.'_right'] != '') {
                                $processed[$flid]['left'] = $request[$flid . '_left'];
                                $processed[$flid]['right'] = $request[$flid . '_right'];
                                $processed[$flid]['invert'] = isset($request[$flid . '_invert']) ? (bool)$request[$flid . '_invert'] : false;
                            }
                            break;
                        case 'Date':
                        case 'DateTime':
                            if(
                                isset($request[$flid.'_begin_month']) && $request[$flid.'_begin_month'] != '' &&
                                isset($request[$flid.'_begin_day']) && $request[$flid.'_begin_day'] != '' &&
                                isset($request[$flid.'_begin_year']) && $request[$flid.'_begin_year'] != '' &&
                                isset($request[$flid.'_end_month']) && $request[$flid.'_end_month'] != '' &&
                                isset($request[$flid.'_end_day']) && $request[$flid.'_end_day'] != '' &&
                                isset($request[$flid.'_end_year']) && $request[$flid.'_end_year'] != ''
                            ) {
                                $processed[$flid]['begin_month'] = $request[$flid.'_begin_month'];
                                $processed[$flid]['begin_day'] = $request[$flid.'_begin_day'];
                                $processed[$flid]['begin_year'] = $request[$flid.'_begin_year'];
                                $processed[$flid]['end_month'] = $request[$flid.'_end_month'];
                                $processed[$flid]['end_day'] = $request[$flid.'_end_day'];
                                $processed[$flid]['end_year'] = $request[$flid.'_end_year'];

                                if(isset($request[$flid.'_begin_hour']))
                                    $processed[$flid]['begin_hour'] = $request[$flid.'_begin_hour'];
                                if(isset($request[$flid.'_begin_minute']))
                                    $processed[$flid]['begin_minute'] = $request[$flid.'_begin_minute'];
                                if(isset($request[$flid.'_begin_second']))
                                    $processed[$flid]['begin_second'] = $request[$flid.'_begin_second'];

                                if(isset($request[$flid.'_end_hour']))
                                    $processed[$flid]['end_hour'] = $request[$flid.'_end_hour'];
                                if(isset($request[$flid.'_end_minute']))
                                    $processed[$flid]['end_minute'] = $request[$flid.'_end_minute'];
                                if(isset($request[$flid.'_end_second']))
                                    $processed[$flid]['end_second'] = $request[$flid.'_end_second'];
                            }
                            break;
                        case 'Historical Date':
                            $beginEra = isset($request[$flid.'_begin_era']) ? $request[$flid.'_begin_era'] : 'CE';
                            $endEra = isset($request[$flid.'_end_era']) ? $request[$flid.'_end_era'] : 'CE';

                            //Check for valid ERA combos, year must be set either way
                            if(
                                (
                                    ($beginEra == 'CE' && $endEra == 'CE') |
                                    ($beginEra == 'BCE' && ($endEra == 'BCE' | $endEra == 'CE')) |
                                    ($beginEra == 'BP' && $endEra == 'BP') |
                                    ($beginEra == 'KYA BP' && $endEra == 'KYA BP')
                                ) &&
                                (
                                    isset($request[$flid.'_begin_year']) && $request[$flid.'_begin_year'] != '' &&
                                    isset($request[$flid.'_end_year']) && $request[$flid.'_end_year'] != ''
                                )
                            ) {
                                $processed[$flid]['begin_era'] = $beginEra;
                                $processed[$flid]['end_era'] = $endEra;

                                if(isset($request[$flid.'_begin_month']) && $request[$flid.'_begin_month'] != '')
                                    $processed[$flid]['begin_month'] = $request[$flid.'_begin_month'];
                                if(isset($request[$flid.'_begin_day']) && $request[$flid.'_begin_day'] != '')
                                    $processed[$flid]['begin_day'] = $request[$flid.'_begin_day'];

                                $processed[$flid]['begin_year'] = $request[$flid.'_begin_year'];

                                if(isset($request[$flid.'_end_month']) && $request[$flid.'_end_month'] != '')
                                    $processed[$flid]['end_month'] = $request[$flid.'_end_month'];
                                if(isset($request[$flid.'_end_day']) && $request[$flid.'_end_day'] != '')
                                    $processed[$flid]['end_day'] = $request[$flid.'_end_day'];

                                $processed[$flid]['end_year'] = $request[$flid.'_end_year'];
                            }
                            break;
                        case 'Geolocator':
                            if($request[$flid.'_lat'] != '' && $request[$flid.'_lng'] != '' && $request[$flid.'_range'] != '') {
                                $processed[$flid]['lat'] = $request[$flid . '_lat'];
                                $processed[$flid]['lng'] = $request[$flid . '_lng'];
                                $processed[$flid]['range'] = $request[$flid . '_range'];
                            }
                            break;
                        case 'Boolean':
                            if(isset($request[$flid.'_input']) && $request[$flid.'_input'] == '1')
                                $processed[$flid]['input'] = true;
                            break;
                        default:
                            if(isset($request[$flid.'_input']) && $request[$flid.'_input'] != '')
                                $processed[$flid]['input'] = $request[$flid.'_input'];

                            if(isset($request[$flid.'_input[]']) && !empty($request[$flid.'_input[]']))
                                $processed[$flid]['input'] = $request[$flid.'_input[]'];
                            break;
                    }

                    if(isset($request[$flid.'_negative']))
                        $processed[$flid]['negative'] = $request[$flid.'_negative'];

                    if(isset($request[$flid.'_empty']))
                        $processed[$flid]['empty'] = $request[$flid.'_empty'];

                    if($field['type'] == 'Combo List') {
                        $processed[$key] = $processed;
                        unset($processed[$flid]);
                    }
                }
            }
        }

        return $processed;
    }
}
