<?php
/**
 * Created by PhpStorm.
 * User: ian.whalen
 * Date: 4/26/2016
 * Time: 11:16 AM
 */

namespace App\Http\Controllers;

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

    public function search($pid, $fid, $query, $method) {
        dd($pid, $fid, $query, $method);

        return view('search.results');
    }
}