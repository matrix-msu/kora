@extends('app')

@section('leftNavLinks')
    <li>
        <a href="{{ url('/projects/'.$form->pid) }}">{{ $projName }}</a>
    </li>
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ $form->name }}<b class="caret"></b></a>
        <ul class="dropdown-menu">
            <li><a href="{{ url('/projects/'.$form->pid) }}">Project Home</a></li>
        </ul>
    </li>
@stop

@section('content')
    <span><h1>{{ $form->name }}</h1></span>
    <div><b>Internal Name:</b> {{ $form->slug }}</div>
    <div><b>Description:</b> {{ $form->description }}</div>
    <hr/>
    <h2>Fields</h2>
    @foreach($form->fields as $field)
        <div class="panel panel-default">
            <div class="panel-heading" style="font-size: 1.5em;">
                <a href="{{ action('FieldController@show',['pid' => $field->pid,'fid' => $field->fid, 'flid' => $field->flid]) }}">{{ $field->name }}</a>
            </div>
            <div class="collapseTest" style="display:none">
                <div class="panel-body"><b>Description:</b> {{ $field->desc }}</div>
                <div class="panel-footer">
                    <span>
                        <a href="{{ action('FieldController@edit',['pid' => $form->pid, 'fid' => $form->fid, 'flid' => $field->flid]) }}">[Edit]</a>
                    </span>
                    <span>
                        <a onclick="deleteField('{{ $field->name }}', {{ $field->fid }})" href="javascript:void(0)">[Delete]</a>
                    </span>
                </div>
            </div>
        </div>
    @endforeach

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
                    url: '{{ action('FormController@destroy',['pid' => $form->pid, 'fid' => $form->fid, 'flid' => '']) }}/'+flid,
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