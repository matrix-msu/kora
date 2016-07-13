<?php
/**
 * Created by PhpStorm.
 * User: Ian Whalen
 * Date: 6/22/2016
 * Time: 3:38 PM
 */

namespace App\Http\Controllers;


class HelpController extends Controller
{
    /**
     * Redirects to the search help page view.
     */
    public function search() {
        return view("help.search");
    }
}