<div class="token card all {{ $index == 0 ? 'active' : '' }}
    {{ $token->search ? 'search' : '' }}
    {{ $token->edit ? 'edit' : '' }}
    {{ $token->create ? 'create' : '' }}
    {{ $token->delete ? 'delete' : '' }}" id="{{$token->id}}">
    <div class="header {{ $index == 0 ? 'active' : '' }}">
        <div class="left pl-m">
            <a class="title" href="#">
                <span class="name">{{$token->title}}</span>
            </a>
        </div>

        <div class="card-toggle-wrap">
            <a href="#" class="card-toggle token-toggle-js">
                <i class="icon icon-chevron {{ $index == 0 ? 'active' : '' }}"></i>
            </a>
        </div>
    </div>

    <div class="content content-js {{ $index == 0 ? 'active' : '' }}">
        <div class="id">
            <span class="attribute">Unique Token Key: </span>
            <span>{{$token->token}}</span>
        </div>

        <div class="description">
            This token can X, Y, and Z within the following projects:
        </div>

        {{--This is where the list of projects goes--}}

        <div class="footer">
            <a class="quick-action left danger" href="#">
                <i class="icon icon-trash"></i>
            </a>

            <a class="quick-action underline-middle-hover" href="#">
                <i class="icon icon-edit-little"></i>
                <span>Edit Token</span>
            </a>

            <a class="quick-action underline-middle-hover" href="#">
                <span>Add Projects to Token</span>
            </a>
        </div>
    </div>
</div>
