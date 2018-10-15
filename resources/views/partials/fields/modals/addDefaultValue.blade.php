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
                @include('partials.fields.combo.inputs.defaults',['field'=>$field, 'type'=>$oneType, 'cfName'=>$oneName, 'fnum'=>'one'])
            </section>
            <section class="combo-list-input-two mt-xxl">
                @include('partials.fields.combo.inputs.defaults',['field'=>$field, 'type'=>$twoType, 'cfName'=>$twoName, 'fnum'=>'two'])
            </section>
            <section class="form-group mt-xxl">
                <input class="btn add-combo-value-js disabled" type="button" value="Create Default Combo Value">        
            </section>
        </div>
    </div>
</div>