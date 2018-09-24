<?php namespace App\Http\Controllers;

class TwitterController extends Controller //TODO:: do we use this?
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
