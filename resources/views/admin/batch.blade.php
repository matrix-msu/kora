{!! Form::open(['method' => 'PATCH', 'action' => 'AdminController@batch']) !!}

    <div class="form-group">
        {!! Form::label('emails', trans('admin_batch.enteremail').': ') !!}
        {!! Form::textarea('emails', null, ['class' => 'form-control', 'id' => 'message']) !!}
    </div>

    <div class="form-group">
        {!! Form::submit(trans('admin_batch.batchcreate'), ['class' => 'btn btn-primary form-control', 'name' => 'sendButton']) !!}
    </div>

{!! Form::close() !!}