<div class="record page card all active form-group">
    <div class="header active">
        <div class="left pl-m">
            <a class="title">
                <span class="name page-title">{{$page["title"]}}</span>
            </a>
        </div>

        <div class="card-toggle-wrap">
            <a href="#" class="card-toggle record-toggle-js">
                <i class="icon icon-chevron active"></i>
            </a>
        </div>
    </div>

    <div class="content active">
        <div class="description">
            @if(sizeof($page["flids"]) > 0)
                @foreach($page["flids"] as $flid)
                    @php $field = $form->layout['fields'][$flid]; @endphp
                    @if($field['viewable'])
                        <div class="field-title mt-m">{{$field['name']}}</div>

                        <section class="field-data">
                            @php
                                $typedField = $form->getFieldModel($field['type']);
                            @endphp
                            @if(!is_null($record->{$flid}))
                                @include($typedField->getFieldDisplayView(), ['field' => $field, 'typedField' => $typedField, 'value' => $record->{$flid}])
                            @else
                                <span class="record-no-data">No Data Inputted</span>
                            @endif
                        </section>
                    @endif
                @endforeach
            @else
                <div class="field-title no-field">No fields added to this page</div>
            @endif
            {{--THIS IS A SPACER--}}
            <div class="field-title"> </div>
        </div>
    </div>
</div>
