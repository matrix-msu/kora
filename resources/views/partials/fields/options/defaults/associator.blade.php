@php
    if(isset($seq)) { //Combo List
        $assocNum = $seq;
        $seq = '_' . $seq;
        $title = $cfName.' ';
        $default = null;
        $defClass = 'default-input-js';
    } else {
        $assocNum = '';
        $seq = '';
        $title = '';
        $default = $field['default'];
        $defClass = '';
    }
@endphp
<div class="form-group">
    {!! Form::label('',$title.'Default Associations') !!}
</div>

<div class="form-group associator-input mt-xl">
    <div>
        {!! Form::label('search','Search Associations') !!}
        <input type="text" class="text-input assoc-search-records-js" placeholder="Enter search term or KID to find associated records (populated below)" {{$assocNum != '' ? "combo=$assocNum" : ''}}>

        <p class="sub-text mt-sm">
            Enter a search term or KID and hit enter to search. Results will then be populated in the "Association Results" field below.
        </p>
    </div>

    <div class="mt-xl">
        {!! Form::label('search','Association Results') !!}
        {!! Form::select('search[]', [], null, ['class' => 'multi-select assoc-select-records-js', 'multiple',
            "data-placeholder" => "Select a record association to add to defaults"]) !!}

        <p class="sub-text mt-sm">
            Once records are populated, they will appear in this fields dropdown. Selecting records will then add them to the "Default Associations" field below.
        </p>
    </div>

    <div class="mt-xl">
        @php
            $defaultArray = [];
            if(!is_null($default) && $default!=''){
                foreach($default as $akid) {
                    $defaultArray[$akid] = $akid;
                }
            }
        @endphp
        {!! Form::label('default'.$seq,'Select Default Associations') !!}
        {!! Form::select('default'.$seq.'[]', $defaultArray, $defaultArray, ['class' => 'multi-select assoc-default-records-js '.$defClass, 'multiple',
            "data-placeholder" => "Search below to add associated records", 'id'=>'default'.$seq]) !!}

        <p class="sub-text mt-sm">
            To add associated records, Start a search for records in the "Search Associations" field above.
        </p>
    </div>
</div>