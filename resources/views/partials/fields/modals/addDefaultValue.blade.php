<div class="modal modal-js modal-mask combolist-add-list-value-modal-js">
    <div class="content">
        <div class="header">
            <span class="title title-js">Add a New Default Combo Value</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <section class="combo-list-input-one">
                @if(\Illuminate\Support\Facades\View::exists('partials.fields.options.defaults.' . $subViewOne))
                    @include(
                        "partials.fields.options.defaults.$subViewOne",
                        ['field'=>$field['one'], 'seq' => 'one', 'cfName'=>$oneName]
                    )
                @endif
            </section>
            <section class="combo-list-input-two mt-xxl">
                @if(\Illuminate\Support\Facades\View::exists('partials.fields.options.defaults.' . $subViewTwo))
                    @include(
                        "partials.fields.options.defaults.$subViewTwo",
                        ['field'=>$field['two'], 'seq' => 'two', 'cfName'=>$twoName]
                    )
                @endif
            </section>
            <section class="form-group mt-xxl">
                <input class="btn add-combo-value-js disabled" type="button" value="Create Default Combo Value">        
            </section>
        </div>
    </div>
</div>
