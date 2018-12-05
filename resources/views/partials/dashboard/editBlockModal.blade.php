<div class="modal modal-js modal-mask edit-block-modal edit-block-modal-js">
    <div class="content">
        <div class="header">
            <span class="title">Edit Block</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <form method="post" id="block_edit_form" action="{{ action('DashboardController@editBlock') }}">
                @include("partials.dashboard.editBlockModalForm")
            </form>
        </div>
    </div>
</div>