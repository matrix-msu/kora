<div class="modal modal-js modal-mask revoke-gitlab-modal-js">
    <div class="content small">
        <div class="header">
            <span class="title">Revoke Gitlab Access?</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <span class="description">
                Are you sure want to revoke gitlab access for this user? This cannot be undone.
            </span>

            <div class="form-group">
                {!! Form::submit('Revoke',['class' => 'btn warning revoke-gitlab-submit-js']) !!}
            </div>
        </div>
    </div>
</div>
