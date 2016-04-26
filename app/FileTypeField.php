<?php
/**
 * Created by PhpStorm.
 * User: ian.whalen
 * Date: 4/21/2016
 * Time: 12:55 PM
 */

namespace App;

/**
 * Class FileTypeField, abstract class for the fields that have files associated with them.
 * @package App
 */
abstract class FileTypeField extends BaseField
{
    /**
     * Keyword search for a file type field.
     *
     * @param array $args
     * @param bool $partial
     * @return bool
     */
    public function keywordSearch(array $args, $partial) {
        $fileNames = $this->getFileNames();

        foreach($fileNames as $fileName) {
            // File type search will always be partial no matter what the user enters.
            if(self::keywordRoutine($args, true, $fileName)) return true;
        }

        return false;
    }

    /**
     * Parses the string representing all the files that a field has and returns an array of the file names.
     *
     * @return array, empty if there was an error, else it will have the names of the files associated with the field.
     */
    public function getFileNames() {
        $type = Field::where("flid", '=', $this->flid)->first()->type;

        $infoString = null;

        switch($type) {
            case 'Documents':
                $infoString = $this->documents;
                break;

            case 'Gallery':
                $infoString = $this->images;
                break;

            case 'Playlist':
                $infoString = $this->audio;
                break;

            case 'Video':
                $infoString = $this->video;
                break;

            case '3D-Model':
                $infoString = $this->model;
                break;
        }

        if ($infoString == null) {
            return []; // Something went wrong!
        }

        $fileNames = [];
        foreach(explode('[!]', $infoString) as $file) {
            $fileNames[] = explode('[Name]', $file)[1];
        }

        return $fileNames;
    }
}