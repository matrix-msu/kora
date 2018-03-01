<div class="form-group mt-xxxl">
    <div class="form-group">
        <label>@if($field->required==1)<span class="oval-icon"></span> @endif{{$field->name}}: </label>
        {!! Form::select($field->flid.'[]', \App\AssociatorField::getAssociatorList($field), \App\AssociatorField::getAssociatorList($field),
            ['class' => 'multi-select assoc-default-records-js', 'multiple', "data-placeholder" => "Search below to add associated records"]) !!}
    </div>

    <div class="form-group mt-xs">
        {!! Form::label('search','Search Associations: ') !!}
        <input type="text" class="text-input assoc-search-records-js" placeholder="Enter search term or KID to find associated records (populated below)"
            search-url="{{ action('AssociatorSearchController@assocSearch',['pid' => $field->pid,'fid'=>$field->fid, 'flid'=>$field->flid]) }}">
    </div>
    <div class="form-group mt-xs">
        {!! Form::label('search','Association Results: ') !!}
        {!! Form::select('search[]', [], null, ['class' => 'multi-select assoc-select-records-js', 'multiple',
            "data-placeholder" => "Select a record association to add to defaults"]) !!}
    </div>
</div>