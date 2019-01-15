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
        $pageArray['fields'] = [];

        $form = FormController::getForm($fid);
        $currPages = $form->layout;

        if(is_null($currPages)) {
            $form->layout = [$pageArray];
        } else {
            if($resize) {
                array_push($currPages, $pageArray);
                $form->layout = $currPages;
            } else {
                $finalArray = [];
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

                $form->layout = $finalArray;
            }
        }

        $form->save();
    }

    /**
     * Gets the layout sequence of the form.
     *
     * @param  int $fid - Form ID
     * @return array - The layout structure
     */
    public static function getFormLayout($fid) {
        $form = FormController::getForm($fid);

        $pages = $form->layout;

        return is_null($pages) ? [] : $pages;
    }

    /**
     * Gets a particular page model.
     *
     * @param  int $pageID - Page ID
     * @return Page - The requested page
     */
//    public static function getPage($pageID) { //TODO::CASTLE
//        $page = Page::where('id','=',$pageID)->first();
//
//        return $page;
//    }

    /**
     * Gets the next field sequence value for a particular page.
     *
     * @param  int $pageID - Page ID
     * @return int - Sequence value
     */
//    public static function getNewPageFieldSequence($pageID) { //TODO::CASTLE
//        $page = self::getPage($pageID);
//
//        $lField = $page->fields()->get()->last();
//
//        if(is_null($lField))
//            return 0;
//        else
//            return $lField->sequence+1;
//    }

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
        $pages = $form->layout;
        $index = $request->pageID;

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
                        //DELETE THE FIELD //TODO::CASTLE
                    }
                }
                $pages = $newLayout;
                break;
            case self::_ADD:
                $name = $request->newPageName;
                if($name=='')
                    response()->json(["status"=>false,"message"=>"page_name_required"],500);

                $pageArray = ['title' => $name, 'fields' => []];
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

        $form->layout = $pages;
        $form->save();

        return response()->json(["status"=>true,"message"=>"page_layout_modified"],200);
    }

    /**
     * Move a field up and down within a page. If at top or bottom, field will move to next page.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @param  Request $request
     * @return JsonResponse
     */
    public function moveField($pid, $fid, $flid, Request $request) { //TODO::CASTLE
        if(!FieldController::checkPermissions($fid, 'edit'))
            return response()->json(["status"=>false,"message"=>"cant_edit_field"],500);

        $direction = $request->direction;
        $field = FieldController::getField($flid);
        $seq = $field->sequence;

        $page = self::getPage($field->page_id);
        $fieldsInPage = Field::where("page_id","=",$page->id)->max("sequence");

        //We will need to see if we can move to a new page so we wan
        $form = FormController::getForm($fid);

        switch($direction) {
            case self::_UP:
                if($seq == 0) {
                    //We need to move to a new page potentially
                    $pageSeq = $page->sequence;
                    if($pageSeq==0) {
                        return response()->json(["status"=>false,"message"=>"no_page_above"],500);
                    } else {
                        $nPage = Page::where('sequence','=',$pageSeq-1)->where('fid','=',$fid)->first();
                        $field->page_id = $nPage->id;
                        $field->sequence = self::getNewPageFieldSequence($nPage->id);
                        $field->save();

                        //get fields from old page ordered by sequence
                        $oldFields = $page->fields()->get();
                        $index = 0;
                        foreach($oldFields as $f) {
                            $f->sequence = $index;
                            $f->save();
                            $index++;
                        }
                    }
                } else {
                    //Move it on up
                    $aFieldSeq = $seq-1;
                    $aField = Field::where('sequence','=',$aFieldSeq)->where('page_id','=',$field->page_id)->first();

                    $field->sequence = $seq-1;
                    $aField->sequence = $seq;
                    $field->save();
                    $aField->save();
                }
                break;
            case self::_DOWN:
                if($seq == $fieldsInPage) {
                    //We need to move to a new page potentially
                    $pageSeq = $page->sequence;
                    $maxPageSeq = Page::where("fid","=",$fid)->max("sequence");;
                    if($pageSeq==$maxPageSeq) {
                        return response()->json(["status"=>false,"message"=>"no_page_below"],500);
                    } else {
                        $nPage = Page::where('sequence','=',$pageSeq+1)->where('fid','=',$fid)->first();
                        $field->page_id = $nPage->id;
                        $field->sequence = self::getNewPageFieldSequence($nPage->id);
                        $field->save();

                        //get fields from old page ordered by sequence
                        $oldFields = $page->fields()->get();
                        $index = 0;
                        foreach($oldFields as $f) {
                            $f->sequence = $index;
                            $f->save();
                            $index++;
                        }
                    }
                } else {
                    //Move it on down
                    $aFieldSeq = $seq+1;
                    $aField = Field::where('sequence','=',$aFieldSeq)->where('page_id','=',$field->page_id)->first();

                    $field->sequence = $seq+1;
                    $aField->sequence = $seq;
                    $field->save();
                    $aField->save();
                }
                break;
            default:
                break;
        }

        return response()->json(["status"=>true,"message"=>"page_moved"],200);
    }

    /**
     * Save entire form layout via array.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return JsonResponse
     */
    public function saveFullFormLayout($pid, $fid, Request $request) { //TODO::CASTLE
        if(!FieldController::checkPermissions($fid, 'edit'))
            return response()->json(["status"=>false,"message"=>"cant_edit_field"],500);

        $formLayout = json_decode($request->layout);
        $pSeq = 0;

        foreach($formLayout as $pageID => $fields) {
            $page = Page::where('id',$pageID)->first();
            $page->sequence = $pSeq;
            $page->save();
            $pSeq++;

            $fSeq = 0;
            foreach($fields as $flid) {
                $field = FieldController::getField($flid);
                $field->page_id = $pageID;
                $field->sequence = $fSeq;
                $field->save();
                $fSeq++;
            }
        }

        return response()->json(["status"=>true,"message"=>"form_layout_saved"],200);
    }
}
