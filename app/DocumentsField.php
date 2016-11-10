<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class DocumentsField extends FileTypeField {

    protected $fillable = [
        'rid',
        'flid',
        'documents'
    ];

    public static function getMimeTypes(){
        $types=array();
        foreach(@explode("\n",@file_get_contents('http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types'))as $x)
            if(isset($x[0])&&$x[0]!=='#'&&preg_match_all('#([^\s]+)#',$x,$out)&&isset($out[1])&&($c=count($out[1]))>1)
                for($i=1;$i<$c;$i++)
                    $types[$out[1][$i]]=$out[1][0];
        return $types;
    }

    /**
     * Pass the fields file array to the files to metadata method.
     *
     * @param Field $field, unneeded.
     * @return array
     */
    public function toMetadata(Field $field) {
        return self::filesToMetadata(explode("[!]", $this->documents));
    }

    public static function getAdvancedSearchQuery($flid, $query) {
        return FileTypeField::getAdvancedSearchQuery($flid, $query, "documents", isset($query[$flid."_extension"]));
    }
}
