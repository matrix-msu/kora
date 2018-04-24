<?php namespace App\Http\Controllers;

use App\Record;
use App\Search;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class FormSearchController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Form Search Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles form searches in Kora3
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
     * Performs a keyword search within a form and returns the results view. //TODO::ADD PAGINATION
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return View
     */
    public function keywordSearch($pid, $fid, Request $request) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $args = $request->keywords;
        $argArray = explode(' ',$args);
        $method = intval($request->method);

        // Inform the user about arguments that will be ignored.
        if($method==Search::SEARCH_EXACT) {
            //Here we treat the argument as one single value
            $ignored = Search::showIgnoredArguments($argArray,true);
            $arg = $args;
        } else {
            $ignored = Search::showIgnoredArguments($argArray);
            $args = array_diff($argArray, $ignored);
            $arg = implode(" ", $args);
        }

        $ignored = implode(" ", $ignored);

        //TODO:: flash("The following arguments were ignored by the search: " . $ignored . '. ');

        $search = new Search($pid, $fid, $arg, $method);

        $rids = $search->formKeywordSearch();

        if(empty($rids))
            $rids = [];

        //store these for later, primarily subset operations like delete, mass assign, etc
        Session::put('form_rid_search_subset', $rids);

        sort($rids);

        $recBuilder = Record::whereIn("rid", $rids);
        $total = $recBuilder->count();

        $pagination = app('request')->input('page-count') === null ? 10 : app('request')->input('page-count');
        $order = app('request')->input('order') === null ? 'lmd' : app('request')->input('order');
        $order_type = substr($order, 0, 2) === "lm" ? "updated_at" : "rid";
        $order_direction = substr($order, 2, 3) === "a" ? "asc" : "desc";
        $records = $recBuilder->orderBy($order_type, $order_direction)->paginate($pagination);

        $form = FormController::getForm($fid);

        return view('records.results', compact("form", "records", "total", "ignored"));
    }

    /**
     * Deletes a subset of records based upon a search.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
    public function deleteSubset($pid, $fid) {
        $rids = Session::get("form_rid_search_subset");

        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        Record::whereIn("rid", "=", $rids)->delete();

        Session::forget("form_rid_search_subset");

        $controller = new RecordController();
        return $controller->index($pid, $fid);
    }
}