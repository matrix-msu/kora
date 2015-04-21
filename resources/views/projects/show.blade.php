@extends('app')

@section('content')
    <span><h1>{{ $project->name.' ('.$project->slug.')' }}</h1></span>
    <div>Description: {{ $project->description }}</div>
    <div>Admin: (Display Admin Here)</div>
    <hr/>
    <h2>Forms</h2>

    @foreach($project->forms as $form)
        <formObj>
            <h3><a href="{{ action('FormController@show',['pid' => $project->pid, 'fid' => $form->fid]) }}">{{ $form->name }}</a></h3>
            <div class="body">{{ $form->description }}</div>
            <span>
                <a href="{{ action('FormController@edit',['pid' => $project->pid, 'fid' => $form->fid]) }}">[Edit]</a>
            </span>
            <span>
                <a onclick="deleteForm('{{ $form->name }}', {{ $form->fid }})" href="javascript:void(0)">[Delete]</a>
            </span>
        </formObj>
    @endforeach

    <form action="{{ action('FormController@create', ['pid' => $project->pid]) }}">
        <input type="submit" value="Create New" class="btn btn-primary form-control">
    </form>
@stop

@section('footer')
    <script>
        function deleteForm(formName, fid) {
            var response = confirm("Are you sure you want to delete "+formName+"?");
            if (response) {
                $.ajax({
                    //We manually create the link in a cheap way because the JS isn't aware of the fid until runtime
                    //We pass in a blank project to the action array and then manually add the id
                    url: '{{ action('FormController@destroy',['pid' => $project->pid, 'fid' => '']) }}/'+fid,
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