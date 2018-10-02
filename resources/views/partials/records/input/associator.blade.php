<?php
    if($editRecord && $hasData) {
        $options = array();
        $values = $typedField->records()->get();
        foreach($values as $value){
            $aRec = \App\Http\Controllers\RecordController::getRecord($value->record);
            $options[$aRec->kid] = $aRec->kid;
        }

        $selected = $options;
        $listOpts = $options;
    } else if($editRecord) {
        $selected = null;
        $listOpts = array();
    } else {
        $selected = \App\AssociatorField::getAssociatorList($field);
        $listOpts = \App\AssociatorField::getAssociatorList($field);
    }
?>
<div class="form-group mt-xxxl">
    <label class="associator-label mb-xs">Associations</label>
</div>
<div class="form-group associator">
    <div class="form-group mb-xl">
        {!! Form::label('search','Search Associations') !!}
        <input type="text" class="text-input assoc-search-records-js" placeholder="Enter search term or KID to find associated records (populated below)"
            search-url="{{ action('AssociatorSearchController@assocSearch',['pid' => $field->pid,'fid'=>$field->fid, 'flid'=>$field->flid]) }}">
        <p class="sub-text">Enter a search term or KID and hit enter to search. Results will then be populated in the "Association Results" field below.</p>
    </div>
    <div class="form-group mt-xs mb-xl">
        {!! Form::label('search','Association Results') !!}
        {!! Form::select('search[]', [], null, ['class' => 'multi-select assoc-select-records-js', 'multiple',
            "data-placeholder" => "Select a record association to add to defaults"]) !!}
        <p class="sub-text">Once records are populated, they will appear in this field's dropdown. Selecting records will then add them to the "Default Associations" field below.</p>
    </div>
    <div class="form-group mt-xs mb-xl">
        <label>@if($field->required==1)<span class="oval-icon"></span> @endif Selected Associations</label>
        <span class="error-message"></span>
        {!! Form::select($field->flid.'[]', $listOpts, $selected, ['class' => 'multi-select assoc-default-records-js preset-clear-chosen-js',
            'multiple', "data-placeholder" => "Selected Associated Records will appear here.", 'id' => $field->flid]) !!}
        <p class="sub-text">To add records, start a search for records in the "Search Associations" field above.</p>
    </div>
</div>