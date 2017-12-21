<?php namespace App\Http\Controllers;

class TwitterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view("partials.twitter");
    }
}
