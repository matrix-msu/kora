@extends('app')

@section('content')
    <style>
        .body_error {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            color: #B0BEC5;
            display: table;
            font-weight: 100;
            font-family: 'Lato';
        }

        .container_error {
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }

        .content_error {
            text-align: center;
            display: inline-block;
        }

        .title_error {
            font-size: 72px;
            margin-bottom: 40px;
        }
    </style>
    <div class="body_error">
        <div class="container_error">
            <div class="content_error">
                <div class="title_error">{{trans('errors_404.droids')}} 4(04).</div>
            </div>
        </div>
    </div>
@endsection
