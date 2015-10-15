<div class="form-group">
    <?php
        //we are building a list of records
        $option = \App\Http\Controllers\FieldController::getFieldOption($field,'SearchForms');
        $options = explode('[!]',$option);
        $opt_layout = array();

        foreach($options as $opt){
            $opt_fid = explode('[fid]',$opt)[1];
            $opt_search = explode('[search]',$opt)[1];
            $opt_flids = explode('[flids]',$opt)[1];
            $opt_flids = explode('-',$opt_flids);

            $opt_layout[$opt_fid] = ['search' => $opt_search, 'flids' => $opt_flids];
        }

        $assocRecords = array();
        foreach($opt_layout as $fid => $opt){
            $recs = \App\Record::where('fid','=',$fid)->get()->all();
            foreach($recs as $rec){
                $assocRecords[$rec->kid] = $rec->kid;
            }
        }
    ?>
    {!! Form::label($field->flid, $field->name.': ') !!}
    @if($field->required==1)
        <b style="color:red;font-size:20px">*</b>
    @endif
        {!! Form::select($field->flid.'[]',$assocRecords, explode('[!]',$field->default), ['class' => 'form-control', 'multiple', 'id' => $field->flid]) !!}
</div>

<script>
    $('#{{$field->flid}}').select2();
</script>