@php
    $selected = null;
    $value = array();
    if($editRecord && !is_null($record->{$flid})) {
        $selected = array();
        foreach(json_decode($record->{$flid},true) as $kid) {
            if(\App\Record::isKIDPattern($kid)) {
                $value[$kid] = $kid;
                $selected[] = $kid;
            }
        }
    }
@endphp
<div class="form-group mt-xxxl">
    <label>@if($field['required'])<span class="oval-icon"></span> @endif{{$field['name']}}</label>
</div>
<div class="form-group associator-input">
    <div class="form-group mb-xl">
        {!! Form::label('search','Search Associations') !!}
        <input type="text" class="text-input assoc-search-records-js" placeholder="Enter search term or KID to find associated records (populated below)"
            search-url="{{ action('AssociatorSearchController@assocSearch',['pid' => $form->project_id,'fid'=>$form->id, 'flid'=>$flid]) }}">
        <p class="sub-text">Enter a search term or KID and hit enter to search. Results will then be populated in the "Association Results" field below.</p>
    </div>
    <div class="form-group mt-xs mb-xl">
        {!! Form::label('search','Association Results') !!}
        {!! Form::select('search[]', [], null, ['class' => 'multi-select assoc-select-records-js', 'multiple',
            "data-placeholder" => "Select a record association to add to defaults"]) !!}
        <p class="sub-text">Once records are populated, they will appear in this field's dropdown. Selecting records will then add them to the "Selected Associations" field below.</p>
    </div>
    <div class="form-group mt-xs mb-xl">
        <label>@if($field['required'])<span class="oval-icon"></span> @endif Selected Associations</label>
        <span class="error-message"></span>
        {!! Form::select($flid.'[]', $value, $selected, ['class' => 'multi-select assoc-default-records-js preset-clear-chosen-js',
            'multiple', "data-placeholder" => "Selected Associated Records will appear here.", 'id' => $flid]) !!}
        <p class="sub-text">To add records, start a search for records in the "Search Associations" field above.</p>
    </div>
</div>