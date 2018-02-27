<?php
echo $typedField->number + 0;
if($typedField->number!='')
    echo ' '.\App\Http\Controllers\FieldController::getFieldOption($field,'Unit');
?>