@php
    $cacheCheck = checkAssocCacheState();
    $cacheDesc = $cacheCheck ? "It's been at least 7 days since the reverse association cache table was generated, and new records have been formed since. Would you like to re-generate the table?" : "Would you like to re-generate the reverse association cache table?";
@endphp
<div class="modal modal-js modal-mask reverse-association-modal-js @if($cacheCheck) echo active @endif">
    <div class="content small">
        <div class="header">
            <span class="title">Build Reverse Association Cache</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body exp-rec">
            <div class="form-group">
                {{ $cacheDesc }}
            </div>
            <div class="form-group mt-m">
                <a href="#" class="btn secondary assoc-cache-js" token="{{ csrf_token() }}"
                   cache-url="{{ action('AdminController@buildReverseCache') }}">
                    Build Cache
                </a>
            </div>
        </div>
    </div>
</div>
