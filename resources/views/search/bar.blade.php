{!! Form::open(['method' => 'GET', 'action' => 'FormSearchController@search']) !!}

    {!! Form::hidden('pid', $pid) !!}
    {!! Form::hidden('fid', $fid) !!}

    <div class="form-group form-inline">
        {!! Form::label('query', trans('search_bar.search') . ': ') !!}
        {!! Form::text('query', null, ['class' => 'form-control']) !!}

        {!! Form::select('method', [
            'and' => trans('search_bar.and'),
            'or' => trans('search_bar.or'),
            'exact' => trans('search_bar.exact')],
            null, ['class' => 'form-control']) !!}

        {!! Form::submit(trans('search_bar.search'), ['class' => 'btn btn-primary form-control']) !!}
    </div>

{!! Form::close() !!}