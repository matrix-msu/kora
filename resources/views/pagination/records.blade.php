@if ($records->lastPage() > 1)
    <ul class="pagination">
        <li class="{{ ($records->currentPage() == 1) ? ' disabled' : '' }}">
            @if($records->currentPage() == 1)
                <span>«</span>
            @else
                <a href="{{ $records->url($records->currentPage()-1) }}">«</a>
            @endif
        </li>



        @if( $records->currentPage()-3 > 1)

            <li>
                <a href="{{ $records->url(1) }}">1</a>
            </li>

            @if($records->currentPage() - 10 > 1)
                @for($i=10; $i < $records->currentPage() - 3; $i+=10)
                    <li>
                        <a href="{{$records->url($i)}}"> {{ $i }} </a>
                    </li>
                @endfor
            @endif


            @if( $records->currentPage()-4 != 1)
                <li class="disabled"><span>...</span></li>
            @endif

        @endif

        @for($i = max($records->currentPage()-3, 1); $i <= min($records->currentPage()+3,$records->lastPage()); $i++)
            <li class="{{ ($records->currentPage() == $i) ? ' active' : '' }}">
                <a href="{{ $records->url($i) }}">{{ $i }}</a>
            </li>
        @endfor

        @if( $records->currentPage()+3 < $records->lastPage())

            @if( $records->currentPage()+4 != $records->lastPage())
                <li class="disabled"><span>...</span></li>
            @endif

            @if($records->currentPage() + 10 < $records->lastPage())
                @for($i = floor( ($records->currentPage() + 13) / 10) * 10; $i < $records->lastPage(); $i+=10)
                    <li>
                        <a href="{{$records->url($i)}}"> {{ $i }} </a>
                    </li>
                @endfor
            @endif

            <li>
                <a href="{{ $records->url($records->lastPage()) }}">{{$records->lastPage()}}</a>
            </li>

        @endif


        <li class="{{ ($records->currentPage() == $records->lastPage()) ? ' disabled' : '' }}">
            @if ($records->currentPage() == $records->lastPage())
                <span>»</span>
            @else
                <a href="{{ $records->url($records->currentPage()+1) }}" >»</a>
            @endif
        </li>
    </ul>
@endif