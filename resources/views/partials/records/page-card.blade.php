<div class="page card all active">
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
            @if($page["fields"]->count() > 0)
                @foreach($page["fields"] as $field)
                    @if($field->viewable)
                        <div class="field-title mt-m">{{$field->name}}</div>

                        <section class="field-data">
                            <?php $typedField = $field->getTypedFieldFromRID($record->rid); ?>
                            @if(!is_null($typedField))
                                @include($typedField::FIELD_DISPLAY_VIEW, ['field' => $field, 'typedField' => $typedField])
                            @else
                                <span class="record-no-data">No Data Inputted</span>
                            @endif
                        </section>
                    @endif
                @endforeach
            @else
                <div class="field-title no-field">No fields added to this page</div>
            @endif
            <div class="field-title"> </div>
        </div>
    </div>
</div>