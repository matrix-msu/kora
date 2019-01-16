<?php namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Page Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the page layout of forms
    |
    */

    /**
     * @var int - The type of page modification to perform
     */
    const _UP = 0;
    const _DOWN = 1;
    const _DELETE = 2;
    const _ADD = 3;
    const _RENAME = 4;

    /**
     * Constructs controller and makes sure user is authenticated.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * Creates a page on the form.
     *
     * @param  int $fid - Form ID
     * @param  string $name - Name of page
     * @param  bool $resize - Determines if we need to reindex pages
     * @param  int $resizeIndex - What index new page will take
     */
    public static function makePageOnForm($fid,$name,$resize=false,$resizeIndex=0) {
        $pageArray = [];
        $pageArray['title'] = $name;
        $pageArray['flids'] = [];

        $form = FormController::getForm($fid);
        $layout = $form->layout;

        if(is_null($layout)) {
            $form->layout = ['pages' => [$pageArray], 'fields' => []];
        } else {
            if($resize) {
                array_push($layout['pages'], $pageArray);
                $form->layout = $layout;
            } else {
                $finalArray = [];
                $currPages = $layout['pages'];
                $done = false;
                for($i=0;$i<sizeof($currPages);$i++) {
                    if($i==$resizeIndex) {
                        //Add the new page
                        array_push($finalArray, $pageArray);
                        $done = true;
                    }

                    array_push($finalArray, $currPages[$i]);
                }
                //Case where the requested index was for it to be the last page
                if(!$done)
                    array_push($finalArray, $pageArray);

                $layout['pages'] = $finalArray;
                $form->layout = $layout;
            }
        }

        $form->save();
    }

    /**
     * Modify a form by adding, removing, and moving pages.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return JsonResponse
     */
    public function modifyFormPage($pid, $fid, Request $request) {
        if(!FieldController::checkPermissions($fid, 'edit'))
            return response()->json(["status"=>false,"message"=>"cant_edit_field"],500);

        $method = $request->method;
        $form = FormController::getForm($fid);
        $layout = $form->layout;
        $pages = $layout['pages'];
        $index = $request->pageID;
        $deleteFields = null;

        switch($method) {
            case self::_RENAME:
                $name = $request->updatedName;
                $pages[$index]['title'] = $name;
                break;
            case self::_UP:
                if($index != 0) {
                    $currPage = $pages[$index];
                    $prevPage = $pages[$index-1];

                    $pages[$index] = $prevPage;
                    $pages[$index-1] = $currPage;
                }
                break;
            case self::_DOWN:
                if($index != sizeof($pages)-1) {
                    $currPage = $pages[$index];
                    $nextPage = $pages[$index+1];

                    $pages[$index] = $nextPage;
                    $pages[$index+1] = $currPage;
                }
                break;
            case self::_DELETE:
                $newLayout = [];
                foreach($pages as $i => $page) {
                    if($i != $index)
                        array_push($newLayout,$page);
                    else {
                        $deleteFields = $page['flids'];
                    }
                }
                $pages = $newLayout;
                break;
            case self::_ADD:
                $name = $request->newPageName;
                if($name=='')
                    response()->json(["status"=>false,"message"=>"page_name_required"],500);

                $pageArray = ['title' => $name, 'flids' => []];
                $newLayout = [];
                $done = false;
                for($i=0;$i<sizeof($pages);$i++) {
                    if($i==$index) {
                        //Add the new page
                        array_push($newLayout, $pageArray);
                        $done = true;
                    }

                    array_push($newLayout, $pages[$i]);
                }
                //Case where the requested index was for it to be the last page
                if(!$done)
                    array_push($newLayout, $pageArray);
                $pages = $newLayout;
                break;
            default:
                return response()->json(["status"=>false,"message"=>"illegal_page_method"],500);
                break;
        }

        $layout['pages'] = $pages;
        $form->layout = $layout;
        $form->save();

        //See if there are fields to be deleted
        if(!is_null($deleteFields)) {
            foreach($deleteFields as $df) {
                $form->deleteField($df);
            }
        }

        return response()->json(["status"=>true,"message"=>"page_layout_modified"],200);
    }

    /**
     * Save entire form layout via array.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return JsonResponse
     */
    public function saveFullFormLayout($pid, $fid, Request $request) {
        if(!FieldController::checkPermissions($fid, 'edit'))
            return response()->json(["status"=>false,"message"=>"cant_edit_field"],500);

        $form = FormController::getForm($fid);
        $newStructure = json_decode($request->layout,true);
        $formLayout = $form->layout;

        foreach($newStructure as $nPage => $flids) {
            $formLayout['pages'][$nPage]['flids'] = $flids;
        }

        $form->layout = $formLayout;
        $form->save();

        return response()->json(["status"=>true,"message"=>"form_layout_saved"],200);
    }
}
