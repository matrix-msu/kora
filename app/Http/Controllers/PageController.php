<?php

namespace App\Http\Controllers;

use App\Form;
use App\Page;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    const _UP = 0;
    const _DOWN = 1;
    const _DELETE = 2;
    const _ADD = 3;
    const _RENAME = 4;

    const _FORM = "form";
    const _PAGE = "page";

    public static function makePageOnForm($fid,$name,$resize=false,$resizeIndex=0)
    {
        $page = new Page();

        $page->title = $name;
        $page->parent_type = self::_FORM;
        $page->fid = $fid;

        $form = FormController::getForm($fid);
        $currPages = $form->pages()->get();

        //In this case, we are placing a page in between pages
        if($resize){
            $found = false;
            foreach($currPages as $cPage){
                if($found){
                    //Once we've found the page we are placing after, we need to change the sequence of any
                    // pages that follow.
                    $cPage->sequence += 1;
                    $cPage->save();
                }

                if($cPage->id == $resizeIndex) {
                    $page->sequence = $cPage->sequence + 1;
                }
            }
        }else{ //Here we just add it to the end
            $page->sequence = $currPages->count();
        }

        $page->save();
    }

    public static function getFormLayout($fid){
        $form = FormController::getForm($fid);

        $pages = $form->pages()->get();
        $layout = array();

        foreach($pages as $page){
            $pArr = array();

            $pArr["fields"] = $page->fields()->get();

            $pArr["title"] = $page->title;
            $pArr["id"] = $page->id;
            $seq = $page->sequence;

            $layout[$seq] = $pArr;
        }

        return $layout;
    }

    public static function restructurePageSequence($pageID){
        $page = self::getPage($pageID);

        $fields = $page->fields()->get();
        $index = 0;

        foreach($fields as $field){
            $field->sequence = $index;
            $field->save();
            $index++;
        }
    }

    /**
     * Get form object for use in controller.
     *
     * @param $fid
     * @return Form | null.
     */
    public static function getPage($page_id)
    {
        $page = Page::where('id','=',$page_id)->first();

        return $page;
    }

    public static function getNewPageFieldSequence($pageID){
        $page = self::getPage($pageID);

        $lField = $page->fields()->get()->last();

        if(is_null($lField)){
            return 0;
        }else
            return $lField->sequence+1;
    }


    public function modifyFormPage($pid, $fid, Request $request){
        $method = $request->method;
        $form = FormController::getForm($fid);
        $pages = $form->pages()->get();

        switch($method){
            case self::_UP:
                $id = $request->pageID;
                $page = self::getPage($id);
                $currSeq = $page->sequence;

                if($currSeq != 0){
                    $aPage = Page::where('sequence','=',$currSeq-1)->get()->first();

                    $page->sequence = $currSeq-1;
                    $aPage->sequence = $currSeq;

                    $page->save();
                    $aPage->save();
                }

                break;
            case self::_DOWN:
                $id = $request->pageID;
                $page = self::getPage($id);
                $currSeq = $page->sequence;

                if($currSeq != ($pages->count()-1)){
                    $aPage = Page::where('sequence','=',$currSeq+1)->get()->first();

                    $page->sequence = $currSeq+1;
                    $aPage->sequence = $currSeq;

                    $page->save();
                    $aPage->save();
                }

                break;
            case self::_DELETE:
                $id = $request->pageID;

                $found = false;
                $delPage = null;
                foreach($pages as $page){
                    if($found){
                        //Once we've found the page we are deleting, we need to change the sequence of any
                        // pages that follow.
                        $page->sequence -= 1;
                        $page->save();
                    }

                    if($page->id == $id) {
                        $found = true;
                        $delPage = $page;
                        $page->sequence = 1337;
                        $page->save();
                    }
                }

                if(!is_null($delPage))
                    $delPage->delete();
                break;
            case self::_ADD:
                $name = $request->newPageName;
                $aboveID = $request->aboveID;

                $found = false;
                foreach($pages as $page){
                    if($found){
                        //Once we've found the page we are placing after, we need to change the sequence of any
                        // pages that follow.
                        $page->sequence += 1;
                        $page->save();
                    }

                    if($page->id == $aboveID) {
                        $found = true;
                        $nPage = new Page();

                        $nPage->title = $name;
                        $nPage->parent_type = self::_FORM;
                        $nPage->fid = $fid;
                        $nPage->sequence = $page->sequence + 1;

                        $nPage->save();
                    }
                }
                break;
            case self::_RENAME:
                $id = $request->pageID;
                $name = $request->updatedName;
                $page = self::getPage($id);

                $page->title = $name;
                $page->save();
                break;
            default:
                return "Illegal Method Provided";
                break;
        }

        return "success";
    }
}
