<?php namespace App\Http\Controllers;

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
        //dd($request->all());
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
            if(array_diff(array_keys($query),array($flid.'_negative',$flid.'_empty')) == [])
                $result = [];
            else
                $result = $form->getFieldModel($field['type'])->advancedSearchTyped($flid, $query, $recModel);

            //This is a negative search so we want the opposite results of what the search would produce
            if(isset($request[$flid."_negative"]))
                $result = array_diff($notRids,$result);

            if(isset($request[$flid."_empty"])) {
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
    public function apisearch($pid, $fid, Request $request, $negative) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $results = [];
        $form = FormController::getForm($fid);

        //Need these for negative searches
        $recModel = new Record(array(),$fid);

        $processed = $this->processRequest($request->all(), $form->layout);
        foreach($processed as $flid => $query) {
            $field = $form->layout['fields'][$flid];
            $result = $form->getFieldModel($field['type'])->advancedSearchTyped($flid, $query, $recModel, $negative);

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
            if(array_key_exists($key,$layout['fields'])) {
                $flid = $key;
                $field = $layout['fields'][$flid];

                switch($field['type']) {
                    case 'Date':
                    case 'Schedule':
                        if(isset($request[$flid.'_begin_month']))
                            $processed[$flid][$flid.'_begin_month'] = $request[$flid.'_begin_month'];
                        if(isset($request[$flid.'_begin_day']))
                            $processed[$flid][$flid.'_begin_day'] = $request[$flid.'_begin_day'];
                        if(isset($request[$flid.'_begin_year']))
                            $processed[$flid][$flid.'_begin_year'] = $request[$flid.'_begin_year'];
                        if(isset($request[$flid.'_end_month']))
                            $processed[$flid][$flid.'_end_month'] = $request[$flid.'_end_month'];
                        if(isset($request[$flid.'_end_day']))
                            $processed[$flid][$flid.'_end_day'] = $request[$flid.'_end_day'];
                        if(isset($request[$flid.'_end_year']))
                            $processed[$flid][$flid.'_end_year'] = $request[$flid.'_end_year'];

                        if(isset($request[$flid.'_begin_era']))
                            $processed[$flid][$flid.'_begin_era'] = $request[$flid.'_begin_era'];
                        if(isset($request[$flid.'_end_era']))
                            $processed[$flid][$flid.'_end_era'] = $request[$flid.'_end_era'];
                        break;
                    case 'Number':
                        if($request[$flid.'_left'] != '' | $request[$flid.'_right'] != '') {
                            $processed[$flid][$flid . '_left'] = isset($request[$flid . '_left']) ? $request[$flid . '_left'] : '';
                            $processed[$flid][$flid . '_right'] = isset($request[$flid . '_right']) ? $request[$flid . '_right'] : '';
                            if(isset($request[$flid . '_invert']))
                                $processed[$flid][$flid . '_invert'] = $request[$flid . '_invert'];
                        }
                        break;
                    case 'Combo List':
                        //Main
                        if(isset($request[$flid.'_one_input']) && $request[$flid.'_one_input'] != '')
                            $processed[$flid][$flid.'_one_input'] = $request[$flid.'_one_input'];
                        if(isset($request[$flid.'_one_input[]']) && $request[$flid.'_one_input[]'] != '')
                            $processed[$flid][$flid.'_one_input[]'] = $request[$flid.'_one_input[]'];

                        //Number
                        if(isset($request[$flid.'_one_left']) && $request[$flid.'_one_left'] != '')
                            $processed[$flid][$flid.'_one_left'] = $request[$flid.'_one_left'];
                        if(isset($request[$flid.'_one_right']) && $request[$flid.'_one_right'] != '')
                            $processed[$flid][$flid.'_one_right'] = $request[$flid.'_one_right'];
                        if(isset($request[$flid.'_one_invert']))
                            $processed[$flid][$flid.'_one_invert'] = $request[$flid.'_one_invert'];

                        //Date
                        if(isset($request[$flid.'_one_month']) && $request[$flid.'_one_month'] != '')
                            $processed[$flid][$flid.'_one_month'] = $request[$flid.'_one_month'];
                        if(isset($request[$flid.'_one_day']) && $request[$flid.'_one_day'] != '')
                            $processed[$flid][$flid.'_one_day'] = $request[$flid.'_one_day'];
                        if(isset($request[$flid.'_one_year']) && $request[$flid.'_one_year'] != '')
                            $processed[$flid][$flid.'_one_year'] = $request[$flid.'_one_year'];
                        break;
                    default:
                        if(isset($request[$flid.'_input']) && $request[$flid.'_input'] != '')
                            $processed[$flid][$flid.'_input'] = $request[$flid.'_input'];

                        if(isset($request[$flid.'_input[]']))
                            $processed[$flid][$flid.'_input[]'] = $request[$flid.'_input[]'];
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
