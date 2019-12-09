<div class="form-group mt-xl">
    {!! Form::label($flid.'_input', array_key_exists('alt_name', $field) && $field['alt_name']!='' ? $field['name'].' ('.$field['alt_name'].')' : $field['name']) !!}
    @php
        $asc = new \App\Http\Controllers\AssociatorSearchController();
        $request = new \Illuminate\Http\Request();
        $request->replace(['keyword' => '']);

        $results = $asc->assocSearch($form->project_id, $form->id, $flid, $request);
        $rids = array();

        foreach($results as $kid => $prevArray) {
            $preview = implode(" | ", $prevArray);
            $rids[$kid] = "$kid: $preview";
        }
    @endphp
    {!! Form::select($flid . "_input[]", $rids, '', ["class" => "multi-select", "Multiple"]) !!}
</div>
<div class="form-group mt-sm">
    <div class="check-box-half">
        <input type="checkbox" value="1" id="active" class="check-box-input" name="{{$flid}}_any" />
        <span class="check"></span>
        <span class="placeholder">Any</span>
        <span class="sub-text">(“Any” Returns records with at least one provided KID, instead of all)</span>
    </div>
</div>