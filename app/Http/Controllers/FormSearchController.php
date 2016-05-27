<?php
/**
 * Created by PhpStorm.
 * User: ian.whalen
 * Date: 4/26/2016
 * Time: 11:16 AM
 */

namespace App\Http\Controllers;

use App\DocumentsField;
use App\Search;
use Illuminate\Support\Facades\Request;
use App\Record;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
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
     * Displays the results of a keyword search.
     *
     * TODO: Make display views (just dumps right now (might be a good task for new person)).
     *
     * @param $pid, project id.
     * @param $fid, form id.
     */
    public function keywordSearch($pid, $fid) {
        $arg = trim((Request::input('query')));
        $method = intval(Request::input('method'));

        dd($this->keywordRoutine($pid, $fid, $arg, $method));
    }

    /**
     *  The actual form search routine, calls App\Search routines with proper parameters.
     *
     *  This will do the following:
     *  1. Do an approximate SQL search to quickly gather records that might match the query.
     *  2. Use the built in keywordSearch methods on the fields of a particular record to narrow the search.
     *  3. Return a view identical to the records index page with the results of this process.
     *
     * @param $pid, project id.
     * @param $fid, form id.
     * @param $arg, arguments of the search.
     * @param $method, method of the search (see search operators in App\Search).
     * @return Collection|null, the results of the search.
     */
    public function keywordRoutine($pid, $fid, $arg, $method) {
        $search = new Search($pid, $fid, $arg, $method);
        $fields = $search->formKeywordSearch(); // The fields in this form that satisfied the search results.

        $records = null;
        if (! $fields->isEmpty()) {
            $records = $search->gatherRecords($fields); // The records that satisfy the search method and search results.
        }

        return $records;
    }
}