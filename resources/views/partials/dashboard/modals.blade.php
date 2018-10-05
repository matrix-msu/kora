<div class="modal modal-js modal-mask create-block-modal create-block-modal-js">
    <div class="content">
        <div class="header">
            <span class="title">Add New Dashboard Block</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <form method="post" id="block_create_form" action={{action("DashboardController@addBlock")}}>
                @include("partials.dashboard.addBlockModalForm")
            </form>
        </div>
    </div>
</div>