@if ($object->lastPage() > 1)
    <ul class="pagination">
        <li class="{{ ($object->currentPage() == 1) ? ' disabled' : '' }}">
            @if($object->currentPage() == 1)
                <span>«</span>
            @else
                <a href="{{ $object->url($object->currentPage()-1) }}">«</a>
            @endif
        </li>

        @if( $object->currentPage()-3 > 1)

            <li>
                <a href="{{ $object->url(1) }}">1</a>
            </li>

            @if($object->currentPage() - 10 > 1)
                @for($i=10; $i < $object->currentPage() - 3; $i+=10)
                    <li>
                        <a href="{{$object->url($i)}}"> {{ $i }} </a>
                    </li>
                @endfor
            @endif


            @if( $object->currentPage()-4 != 1)
                <li class="disabled"><span>...</span></li>
            @endif

        @endif

        @for($i = max($object->currentPage()-3, 1); $i <= min($object->currentPage()+3,$object->lastPage()); $i++)
            <li class="{{ ($object->currentPage() == $i) ? ' active' : '' }}">
                <a href="{{ $object->url($i) }}">{{ $i }}</a>
            </li>
        @endfor

        @if( $object->currentPage()+3 < $object->lastPage())

            @if( $object->currentPage()+4 != $object->lastPage())
                <li class="disabled"><span>...</span></li>
            @endif

            @if($object->currentPage() + 10 < $object->lastPage())
                @for($i = floor( ($object->currentPage() + 13) / 10) * 10; $i < $object->lastPage(); $i+=10)
                    <li>
                        <a href="{{$object->url($i)}}"> {{ $i }} </a>
                    </li>
                @endfor
            @endif
          
            <li>
                <a href="{{ $object->url($object->lastPage()) }}">{{$object->lastPage()}}</a>
            </li>

        @endif

        <li class="{{ ($object->currentPage() == $object->lastPage()) ? ' disabled' : '' }}">
            @if ($object->currentPage() == $object->lastPage())
                <span>»</span>
            @else
                <a href="{{ $object->url($object->currentPage()+1) }}" >»</a>
            @endif
        </li>
    </ul>
@endif