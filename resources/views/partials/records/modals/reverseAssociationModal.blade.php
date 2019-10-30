<div class="modal modal-js modal-mask reverse-association-modal-js">
    <div class="content small">
        <div class="header">
            <span class="title">Build Reverse Association Cache</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body exp-rec">
            <div class="form-group">
                Would you like to re-generate the reverse association cache table?.
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