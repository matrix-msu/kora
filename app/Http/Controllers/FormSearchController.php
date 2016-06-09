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

        $search = new Search($pid, $fid, $arg, $method);

        dd($search->formKeywordSearch(), $search::showIgnoredArguments($arg));
    }
}