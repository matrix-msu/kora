<div class="modal modal-js modal-mask create-token-modal create-token-modal-js">
    <div class="content">
        <div class="header">
            <span class="title">Create New Token</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <form method="post" id="token_create_form" action={{action("TokenController@create")}}>
                @include("partials.tokens.createTokenModalForm")
            </form>
        </div>
    </div>
</div>

<div class="modal modal-js modal-mask edit-token-modal edit-token-modal-js">
    <div class="content">
        <div class="header">
            <span class="title">Edit Token</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <form method="post" id="token_edit_form" action={{action("TokenController@edit")}}>
                @include("partials.tokens.editTokenModalForm")
            </form>
        </div>
    </div>
</div>

<div class="modal modal-js modal-mask delete-token-modal delete-token-modal-js">
    <div class="content">
        <div class="header">
            <span class="title">Delete Token?</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <form method="post" id="token_delete_form" action={{action("TokenController@deleteToken")}}>
                @include("partials.tokens.deleteTokenModalForm")
            </form>
        </div>
    </div>
</div>