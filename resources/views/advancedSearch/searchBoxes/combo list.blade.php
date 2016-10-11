<?php
$oneType = \App\ComboListField::getComboFieldType($field,'one');
$twoType = \App\ComboListField::getComboFieldType($field,'two');

$types = [
    1 => $oneType,
    2 => $twoType
];

$names = [
    1 => \App\ComboListField::getComboFieldName($field,'one'),
    2 =>\App\ComboListField::getComboFieldName($field,'two')
];

$options = [
    1 => \App\ComboListField::getComboList($field, true, "one"),
    2 => \App\ComboListField::getComboList($field, true, "two"),
];

?>

<div class="panel panel-default">
    <div class="panel-heading">
        <div class="checkbox">
            <label style="font-size:1.25em;"><input type="checkbox" name="{{$field->flid}}_dropdown"> {{$field->name}}</label>
        </div>
    </div>
    <div id="input_collapse_{{$field->flid}}" style="display: none;">
        <div class="panel-body">
            <?php $field_num = 1; ?>
            @foreach([$oneType, $twoType] as $type)
                @if($type == "Text")
                    <label for="{{$field->flid}}_{{$field_num}}_input">Search text for {{$names[$field_num]}}:</label>
                    <input class="form-control" type="text" name="{{$field->flid}}_{{$field_num}}_input">
                @elseif($type == "Number")
                    <label>Search range for {{$names[$field_num]}}:</label>
                    <div class="form-inline">
                        <input class="form-control" type="number" id="{{$field->flid}}_{{$field_num}}_left" name="{{$field->flid}}_{{$field_num}}_left" placeholder="Left Index"> :
                        <input class="form-control" type="number" id="{{$field->flid}}_{{$field_num}}_right" name="{{$field->flid}}_{{$field_num}}_right" placeholder="Right Index">
                        Invert: <input id="{{$field->flid}}_{{$field_num}}_invert" type="checkbox" name="{{$field->flid}}_{{$field_num}}_invert">

                        <div style="margin-top: 1em" id="{{$field->flid}}_{{$field_num}}_info">
                            Current search interval: <span id="{{$field->flid}}_{{$field_num}}_interval">(-&infin;,&infin;)</span>
                        </div>

                        @include("advancedSearch.searchBoxes.number-validation", ["prefix" => strval($field->flid) . "_" . strval($field_num)])
                    </div>
                @else
                    <?php $multiple = ($types[$field_num] != "List") ? true : false; ?>

                    <label for={{$field->flid}}_{{$field_num}}_input">Search option{{($multiple) ? "s" : ""}} for {{$names[$field_num]}}:</label><br/>
                    {!! Form::select( $field->flid . "_"  . $field_num . "_input" . ($multiple) ? "[]" : "", $options[$field_num], "", ["class" => "form-control", ($multiple) ? "Multiple" : "", 'id' => $field->flid . "_" . $field_num ."_input", "style" => "width: 100%"]) !!}

                    @if($multiple)
                        <script>
                            var multiple = {{$multiple}};

                            if (multiple) {
                                var generated = Boolean({{ $types[$field_num] == "Generated List" }});

                                var selector = $("#{{$field->flid}}_{{$field_num}}_input")
                                selector.empty();
                                selector.select2({tags:generated});
                            }
                        </script>
                    @endif
                @endif
            <?php $field_num++; ?>
            <br/>
            @endforeach
        </div>
    </div>
</div>