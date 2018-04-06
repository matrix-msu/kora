<form method="POST" action="{{action("AdvancedSearchController@search", ["pid" => $form->pid, "fid" => $form->fid])}}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="advanced-search-title">
        Advanced Search Options
    </div>
    @foreach($form->fields()->get() as $field)
        <?php $typedField = $field->getTypedField(); ?>
        @if($field->advsearch)
            <input type="hidden" name="{{$field->flid}}" value="{{$field->flid}}">
            @include($typedField::FIELD_ADV_INPUT_VIEW, ['field' => $field])
            <div class="form-group mt-sm">
                <div class="check-box-half">
                    <input type="checkbox" value="1" id="active" class="check-box-input" name="{{$field->flid}}_negative" />
                    <span class="check"></span>
                    <span class="placeholder">Negative</span>
                    <span class="sub-text">(“Negative” Returns records that do not meet this search)</span>
                </div>
            </div>
            {{--TODO::Make a empty function. Here?--}}
        @endif
    @endforeach
    <div class="form-group mt-xxxl">
        {!! Form::submit('Advanced Search', ['class' => 'btn']) !!}
    </div>
</form>