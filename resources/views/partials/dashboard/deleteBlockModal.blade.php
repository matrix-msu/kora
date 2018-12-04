<div class="modal modal-js modal-mask delete-block-modal delete-block-modal-js">
    <div class="content small">
        <div class="header">
            <span class="title delete-block">Delete Block?</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
			<span class="description delete-block">Are you sure you wish to delete this block?</span>
			<form class="delete-block-form-js" action="">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<div class="form-group">
					<input class="btn warning delete-block-js" type="submit" value="Delete Block">
				</div>
            </form>
            <input type="hidden" name="blkID" value="">
        </div>
    </div>
</div>