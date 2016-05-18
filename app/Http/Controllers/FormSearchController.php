<?php
/**
 * Created by PhpStorm.
 * User: ian.whalen
 * Date: 4/26/2016
 * Time: 11:16 AM
 */

namespace App\Http\Controllers;

use App\Search;
use Illuminate\Support\Facades\Request;
use App\Record;

class FormSearchController extends Controller
{
    /**
     * FormSearchController constructor.
     * User must be logged in and active to access methods here.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * Executes a keyword search on a particular form.
     *
     * This will do the following:
     *  1. Do an approximate SQL search to quickly gather records that might match the query.
     *  2. Use the built in keywordSearch methods on the fields of a particular record to narrow the search.
     *  3. Return a view identical to the records index page with the results of this process.
     *
     * @param $pid, project id.
     * @param $fid, form id.
     */
    public function keywordSearch($pid, $fid) {
        $arg = Request::input('query');
        $method = intval(Request::input('method'));

        $results = []; // Results of the search.

        $arg = Search::convertCloseChars($arg);

// Only testing one query right now.
//        if ($method == Search::SEARCH_EXACT)
//            $query_arr = [$query]; // We only want to search for the exact phrase, so there is only one element here.
//        else
//            $query_arr = explode(" ", $query); // We want to search for each element separately so we explode on spaces.

        $seeker = new Search($pid, $fid, $arg, $method);
        $results = $seeker->formKeywordSearch();

        dd($results, Record::where('rid', '=', $results[0]['rid'])->get());
    }
}