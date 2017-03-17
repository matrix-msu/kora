<div>
    <div>

        @if($field->metadata()->first() !== null)
            {{ $field->name }}
            <a href="#"  onclick="deleteMeta({{$field->flid}})" class="pull-right">[X]</a>
            <span style="padding-right:5px"  class="pull-right">{{$field->metadata()->first()->name }}</span>
        @else

        @endif
    </div>

</div>