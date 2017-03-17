
{!! Form::hidden('default_type_'.$fnum.'_'.$flid,$type) !!}
{!! Form::label('default_'.$fnum.'_'.$flid,\App\ComboListField::getComboFieldName($field,$fnum).': ') !!}
@if($type=='Text')
    @if(\App\ComboListField::getComboFieldOption($field,'MultiLine',$fnum)==0)
        {!! Form::text('default_'.$fnum.'_'.$flid, null, ['class' => 'form-control']) !!}
    @elseif(\App\ComboListField::getComboFieldOption($field,'MultiLine',$fnum)==1)
        {!! Form::textarea('default_'.$fnum.'_'.$flid, null, ['class' => 'form-control']) !!}
    @endif
@elseif($type=='Number')
    <input
            type="number" id="default_{{$fnum}}_{{$flid}}" name="default_{{$fnum}}_{{$flid}}" class="form-control" value=""
            step="{{ \App\ComboListField::getComboFieldOption($field, "Increment", $fnum) }}"
            min="{{ \App\ComboListField::getComboFieldOption($field, "Min", $fnum) }}"
            max="{{ \App\ComboListField::getComboFieldOption($field, "Max", $fnum) }}">
@elseif($type=='List')
    {!! Form::select('default_'.$fnum.'_'.$flid,\App\ComboListField::getComboList($field,true,$fnum), null,['class' => 'form-control', 'id' => 'default_'.$fnum.'_'.$flid]) !!}
    <script>
        $('#default_{{$fnum}}_{{$flid}}').select2();
    </script>
@elseif($type=='Multi-Select List')
    {!! Form::select('default_'.$fnum.'_'.$flid.'[]',\App\ComboListField::getComboList($field,false,$fnum), null,['class' => 'form-control', 'multiple', 'id' => 'default_'.$fnum.'_'.$flid]) !!}
    <script>
        $('#default_{{$fnum}}_{{$flid}}').select2();
    </script>
@elseif($type=='Generated List')
    {!! Form::select('default_'.$fnum.'_'.$flid.'[]',\App\ComboListField::getComboList($field,false,$fnum), null,['class' => 'form-control', 'multiple', 'id' => 'default_'.$fnum.'_'.$flid]) !!}
    <script>
        $('#default_{{$fnum}}_{{$flid}}').select2({
            tags: true
        });
    </script>
@endif