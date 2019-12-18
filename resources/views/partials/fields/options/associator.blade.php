@extends('fields.show')

@section('fieldOptions')
    <div class="form-group">
        {!! Form::label('','Default Associations') !!}
    </div>

    <div class="form-group associator-input mt-xl">
        <div>
            {!! Form::label('search','Search Associations') !!}
            <input type="text" class="text-input assoc-search-records-js" placeholder="Enter search term or KID to find associated records (populated below)">

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
                if(!is_null($field['default']) && $field['default']!=''){
                    foreach($field['default'] as $akid) {
                        $defaultArray[$akid] = $akid;
                    }
                }
            @endphp
            {!! Form::label('default','Select Default Associations') !!}
            {!! Form::select('default[]', $defaultArray, $defaultArray, ['class' => 'multi-select assoc-default-records-js', 'multiple',
                "data-placeholder" => "Search below to add associated records"]) !!}

            <p class="sub-text mt-sm">
                To add associated records, Start a search for records in the "Search Associations" field above.
            </p>
        </div>
    </div>

    @include('partials.fields.options.config.associator')

    <input name="flids" type="hidden" value="">

    <div class="form-group mt-sm">
        <p class="sub-text">
            If no forms are available, have a Form Admin request permission to forms by using the Association Permissions page.
        </p>
    </div>

@stop

@section('fieldOptionsJS')
    assocSearchURI = "{{ action('AssociatorSearchController@assocSearch',['pid' => $form->project_id,'fid'=>$form->id, 'flid'=>$flid]) }}";
    csfrToken = "{{ csrf_token() }}";

    Kora.Fields.Options('Associator');
@stop
