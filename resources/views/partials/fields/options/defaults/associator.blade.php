@php
    if(isset($seq))
        $seq = '_' . $seq;
    else
        $seq = '';
@endphp
<div class="form-group mt-xxxl">Association Search Configuration</div>

<div class="associator-section">
    @foreach(\App\Http\Controllers\AssociationController::getAvailableAssociations($form->id) as $a)
        @php
        $f = \App\Http\Controllers\FormController::getForm($a->data_form);
        $formFieldsData = $f->layout['fields'];
        $formFields = array();
        foreach($formFieldsData as $aflid => $data) {
            $formFields[$aflid] = $data['name'];
        }

        //get layout info for this form
        if(array_key_exists($f->id,$assocLayout)){
            $f_check = true;
            $f_flids = $assocLayout[$f->id]['flids'];
        }else{
            $f_check = false;
            $f_flids = null;
        }

        @endphp
        <div class="form-group mt-xl">
            <div class="check-box-half">
                <input type="checkbox" value="1" id="active" class="check-box-input association-check-js" name="checkbox_{{$f->id}}{{$seq}}"
                @if($f_check)
                    checked
                @endif
                />
                <span class="check"></span>
                <span class="placeholder">Search through {{$f->name}}?</span>
            </div>
        </div>

        <div class="form-group mt-m
        @if(!$f_check)
            hidden
        @endif
        ">
            {!! Form::label('preview_' . $f->id . $seq . '[]', 'Preview Value') !!}
            {!! Form::select('preview_' . $f->id . $seq . '[]', $formFields, $f_flids, ['class' => 'multi-select assoc-preview-js', 'multiple', 'data-placeholder' => 'Select field preview value']) !!}
        </div>
    @endforeach
</div>
