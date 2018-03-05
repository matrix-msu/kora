@if($type=='Text')
    <div class="form-group
        @if($fnum=='two')
            mt-xxxl
        @else
            mt-xl
        @endif
        ">
        {!! Form::label('default_'.$fnum, $cfName.': ') !!}
        {!! Form::text('default_'.$fnum, null, ['id' => 'default_'.$fnum, 'class' => 'text-input', 'placeholder' => 'Enter text value here']) !!}
    </div>
@elseif($type=='Number')
    <div class="form-group
        @if($fnum=='two')
            mt-xxxl
        @else
            mt-xl
        @endif
            ">
        {!! Form::label('default_'.$fnum, $cfName.' ('.\App\ComboListField::getComboFieldOption($field, "Unit", $fnum).'): ') !!}
        <input type="number" id="default_{{$fnum}}" name="default_{{$fnum}}" class="text-input" value="" placeholder="Enter number here"
               step="{{ \App\ComboListField::getComboFieldOption($field, "Increment", $fnum) }}"
               min="{{ \App\ComboListField::getComboFieldOption($field, "Min", $fnum) }}"
               max="{{ \App\ComboListField::getComboFieldOption($field, "Max", $fnum) }}">
    </div>
@elseif($type=='List')
    <div class="form-group
        @if($fnum=='two')
            mt-xxxl
        @else
            mt-xl
        @endif
            ">
        {!! Form::label('default_'.$fnum, $cfName.': ') !!}
        {!! Form::select('default_'.$fnum,\App\ComboListField::getComboList($field,false,$fnum), null,
            ['id' => 'default_'.$fnum, 'class' => 'single-select']) !!}
    </div>
@elseif($type=='Multi-Select List')
    <div class="form-group
        @if($fnum=='two')
            mt-xxxl
        @else
            mt-xl
        @endif
            ">
        {!! Form::label('default_'.$fnum, $cfName.': ') !!}
        {!! Form::select('default_'.$fnum.'[]',\App\ComboListField::getComboList($field,false,$fnum), null,
        ['id' => 'default_'.$fnum, 'class' => 'multi-select', 'multiple']) !!}
    </div>
@elseif($type=='Generated List')
    <div class="form-group
        @if($fnum=='two')
            mt-xxxl
        @else
            mt-xl
        @endif
            ">
        {!! Form::label('default_'.$fnum, $cfName.': ') !!}
        {!! Form::select('default_'.$fnum.'[]',\App\ComboListField::getComboList($field,false,$fnum), null,
        ['id' => 'default_'.$fnum, 'class' => 'multi-select modify-select', 'multiple']) !!}
    </div>
@elseif($type=='Associator')
    <div class="form-group
        @if($fnum=='two')
            mt-xxxl
        @else
            mt-xl
        @endif
            ">
        {!! Form::label('default_'.$fnum, $cfName.': ') !!}
        {!! Form::select('default_'.$fnum.'[]', [], null, ['id' => 'default_'.$fnum, 'class' => 'multi-select assoc-default-records-js',
            'multiple', "data-placeholder" => "Search below to add associated records"]) !!}
    </div>

    <div class="form-group mt-xs">
        {!! Form::label('search','Search Associations: ') !!}
        <input type="text" class="text-input assoc-search-records-js" placeholder="Enter search term or KID to find associated records (populated below)">
    </div>

    <div class="form-group mt-xs">
        {!! Form::label('search','Association Results: ') !!}
        {!! Form::select('search[]', [], null, ['class' => 'multi-select assoc-select-records-js', 'multiple',
            "data-placeholder" => "Select a record association to add to defaults"]) !!}
    </div>
@endif