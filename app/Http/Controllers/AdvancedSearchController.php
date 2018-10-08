<?php namespace App\Http\Controllers;

use App\Field;
use App\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

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

        //Need these for negative searches
        $notRids = Record::where('fid', '=', $fid)->pluck('rid')->toArray();

        $processed = $this->processRequest($request->all());
        foreach($processed as $flid => $query) {
            $field = FieldController::getField($flid);
            if(array_diff(array_keys($query),array($flid.'_negative',$flid.'_empty')) == [])
                $result = [];
            else
                $result = $field->getTypedField()->advancedSearchTyped($flid, $query);

            //This is a negative search so we want the opposite results of what the search would produce
            if(isset($request[$flid."_negative"]))
                $result = array_diff($notRids,$result);

            if(isset($request[$flid."_empty"])) {
                $empty = $field->getTypedField()->getEmptyFieldRecords($flid);
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

        $recBuilder = Record::whereIn("rid", $rids);
        $total = $recBuilder->count();

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

        $rids = Session::get('advanced_search_recents');
        if(is_null($rids))
            $rids = [];

        sort($rids);

        $recBuilder = Record::whereIn("rid", $rids);
        $total = $recBuilder->count();

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
     * @return array - Record ID search results
     */
    public function apisearch($pid, $fid, Request $request) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $results = [];

        $processed = $this->processRequest($request->all());
        foreach($processed as $flid => $query) {
            $field = FieldController::getField($flid);
            $result = $field->getTypedField()->advancedSearchTyped($flid, $query);

            //NOTE: API handles negative searches

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
     * @return array - Processed array
     */
    private function processRequest(array $request) {
        $processed = [];

        foreach($request as $key => $value) {
            if(is_numeric($key)) {
                $flid = $key;
                $field = Field::where('flid',$flid)->first();

                switch($field->type) {
                    //TODO::Modular?
                    case 'Date':
                    case 'Schedule':
                        if(
                            $request[$flid.'_begin_month'] != '' && $request[$flid.'_begin_day'] != '' && $request[$flid.'_begin_year'] != '' &&
                            $request[$flid.'_end_month'] != '' && $request[$flid.'_end_day'] != '' && $request[$flid.'_end_year'] != ''
                        ) {
                            $processed[$flid][$flid.'_begin_month'] = $request[$flid.'_begin_month'];
                            $processed[$flid][$flid.'_begin_day'] = $request[$flid.'_begin_day'];
                            $processed[$flid][$flid.'_begin_year'] = $request[$flid.'_begin_year'];
                            $processed[$flid][$flid.'_end_month'] = $request[$flid.'_end_month'];
                            $processed[$flid][$flid.'_end_day'] = $request[$flid.'_end_day'];
                            $processed[$flid][$flid.'_end_year'] = $request[$flid.'_end_year'];

                            if(isset($request[$flid.'_begin_era']))
                                $processed[$flid][$flid.'_begin_era'] = $request[$flid.'_begin_era'];
                            if(isset($request[$flid.'_end_era']))
                                $processed[$flid][$flid.'_end_era'] = $request[$flid.'_end_era'];
                        } else {
                            //advanced error
                        }
                        break;
                    case 'Number':
                        if($request[$flid.'_left'] != '' | $request[$flid.'_right'] != '') {
                            $processed[$flid][$flid.'_left'] = $request[$flid.'_left'];
                            $processed[$flid][$flid.'_right'] = $request[$flid.'_right'];
                            if(isset($request[$flid.'_invert']))
                                $processed[$flid][$flid.'_invert'] = $request[$flid.'_invert'];
                        } else {
                            //advanced error
                        }
                        break;
                    default:
                        if(isset($request[$flid.'_input']) && $request[$flid.'_input'] != '')
                            $processed[$flid][$flid.'_input'] = $request[$flid.'_input'];
                        break;
                }

                if(isset($request[$flid.'_negative']))
                    $processed[$flid][$flid.'_negative'] = $request[$flid.'_negative'];

                if(isset($request[$flid.'_empty']))
                    $processed[$flid][$flid.'_empty'] = $request[$flid.'_empty'];
            }
        }

        return $processed;
    }
}
