<?php
    //This being done on-the-fly on a per token basis
    //This section formats display strings for the token types
    $typesHyphen = array();
    $typesDesc = array();

    if($token->search) {
        array_push($typesHyphen,'Search');
        array_push($typesDesc,'search');
    }
    if($token->create) {
        array_push($typesHyphen,'Create');
        array_push($typesDesc,'create');
    }
    if($token->edit) {
        array_push($typesHyphen,'Edit');
        array_push($typesDesc,'edit');
    }
    if($token->delete) {
        array_push($typesHyphen,'Delete');
        array_push($typesDesc,'delete');
    }

    $typesHyphen = implode(' - ', $typesHyphen);
    $typesDesc = implode(', ', $typesDesc);
?>

<div class="token card all {{ $index == 0 ? 'active' : '' }}
    {{ $token->search ? 'search' : '' }}
    {{ $token->edit ? 'edit' : '' }}
    {{ $token->create ? 'create' : '' }}
    {{ $token->delete ? 'delete' : '' }}" id="{{$token->id}}">
    <div class="header {{ $index == 0 ? 'active' : '' }}">
        <div class="left pl-m">
            <a class="title">
                <span class="name">{{$token->title}}</span>
            </a>
        </div>

        <div class="card-toggle-wrap">
            <a href="#" class="card-toggle token-toggle-js">
                <span class="chevron-text">{{$typesHyphen}}</span>
                <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
            </a>
        </div>
    </div>

    <div class="content content-js {{ $index == 0 ? 'active' : '' }}">
        <div class="id">
            <span class="attribute">Unique Token Key: </span>
            <span>{{$token->token}}</span>
        </div>

        @if($token->projects()->count() > 0)
            <div class="description mt-xl">
                <span>This token can
                    {{$typesDesc}}
                    within the following projects:</span>
            </div>

            {{--This is where the list of projects goes--}}
            <div class="token-projects mt-xl">
                @foreach($token->projects()->get() as $tp)
                    <div class="token-project">
                        <span><a class="token-project-delete-js" href="#"
                                 pid="{{$tp->pid}}" token="{{$token->id}}" pname="{{$tp->name}}">
                                <i class="icon icon-cancel-circle"></i></a>
                        </span>
                        <span class="ml-xs tp-title">{{$tp->name}}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="description mt-xl">
                <span>No projects have been added to this token. Select
                    <a class="empty-token-projects-link underline-middle-hover add-projects-js" href="#">
                        Add Project(s) to Token
                    </a>
                    to add some!</span>
            </div>
        @endif

        <div class="footer">
            <a class="quick-action trash-container left danger delete-token-js" href="#">
                <i class="icon icon-trash"></i>
            </a>

            <a class="quick-action underline-middle-hover edit-token-js" href="#">
                <i class="icon icon-edit-little"></i>
                <span>Edit Token</span>
            </a>

            <a class="quick-action underline-middle-hover add-projects-js" href="#">
                <span>Add Project(s) to Token</span>
            </a>
        </div>
    </div>
</div>
