<?php namespace App\Http\Controllers;


use App\Field;
use Illuminate\Http\Request;

class AdvancedSearchController extends Controller {

    /**
     * User must be logged in and admin to access views in this controller.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * Advanced search index.
     *
     * @param $pid, project id.
     * @param $fid, form id.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($pid, $fid) {
        if (! FormController::validProjForm($pid, $fid)) {
            return redirect("projects/". $pid ."/forms/". $fid);
        }

        $fields = Field::where("fid", "=", $fid)->get();
        return view("advancedSearch.index", compact("pid", "fid", "fields"));
    }

    public function search($pid, $fid, Request $request) {
        dd($request->all());
    }
}
