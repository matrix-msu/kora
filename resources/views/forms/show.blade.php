@extends('app')

@section('leftNavLinks')
    @include('partials.menu.project', ['pid' => $form->pid])
    @include('partials.menu.form', ['pid' => $form->pid, 'fid' => $form->fid])
@stop

@section('content')
    <span><h1>{{ $form->name }}</h1></span>
    <div><b>Internal Names:</b> {{ $form->slug }}</div>
    <div><b>Description:</b> {{ $form->description }}</div>
    <div>
        <a href="{{ action('RecordController@index',['pid' => $form->pid, 'fid' => $form->fid]) }}">[Records]</a>
        <a href="{{ action('RecordController@create',['pid' => $form->pid, 'fid' => $form->fid]) }}">[New Record]</a>
    </div>
    <hr/>
    <h2>Fields</h2>

    <?php
            $xml = xml_parser_create();
            xml_parse_into_struct($xml,$form->layout, $vals, $index);
    ?>

    @for($i=0;$i<sizeof($vals);$i++)
        @if($vals[$i]['tag']=='ID')
            @include('forms.layout.printfield',['field' => App\Field::where('flid', '=', $vals[$i]['value'])->first()])
        @elseif($vals[$i]['tag']=='NODE')
            <?php
                    $level = $vals[$i]['level'];
                    $title = $vals[$i]['attributes']['TITLE'];
                    $node = array();
                    $i++;
                    while($vals[$i]['tag']!='NODE' | $vals[$i]['type']!='close' | $vals[$i]['level']!=$level){
                        array_push($node,$vals[$i]);
                        $i++;
                    }
            ?>
            @include('forms.layout.printnested',['node' => $node, 'title' => $title])
        @endif
    @endfor

    <form action="{{action('FieldController@create', ['pid' => $form->pid, 'fid' => $form->fid]) }}">
        <input type="submit" value="Create New Field" class="btn btn-primary form-control">
    </form>
@stop

@section('footer')
    <script>
        $( ".panel-heading" ).on( "click", function() {
            if ($(this).siblings('.collapseTest').css('display') == 'none' ){
                $(this).siblings('.collapseTest').slideDown();
            }else {
                $(this).siblings('.collapseTest').slideUp();
            }
        });

        function deleteField(fieldName, flid) {
            var response = confirm("Are you sure you want to delete "+fieldName+"?");
            if (response) {
                $.ajax({
                    //We manually create the link in a cheap way because the JS isn't aware of the fid until runtime
                    //We pass in a blank project to the action array and then manually add the id
                    url: '{{ action('FieldController@destroy', ['pid' => $form->pid, 'fid' => $form->fid, 'flid' => '']) }}/'+flid,
                    type: 'DELETE',
                    data: {
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function (result) {
                        location.reload();
                    }
                });
            }
        }
    </script>
@stop