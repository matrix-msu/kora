@extends('app')

@section('content')
<style>
    .body_quote {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
        color: #B0BEC5;
        display: table;
        font-weight: 100;
        font-family: 'Lato';
    }

    .container_quote {
        text-align: center;
        display: table-cell;
        vertical-align: middle;
    }

    .content_quote {
        text-align: center;
        display: inline-block;
    }

    .title_quote {
        font-size: 96px;
        margin-bottom: 40px;
    }

    .quote {
        font-size: 24px;
    }
</style>
<div class="body_quote">
    <div class="container_quote">
        <div class="content_quote">
            <div class="title_quote">Kora 3</div>
            <div class="quote">{{ Inspiring::quote() }}</div>
            <div class="quote">Powered by Laravel</div>
        </div>
    </div>
</div>
@endsection

