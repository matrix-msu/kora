<div class="modal modal-js modal-mask edit-quick-actions-modal edit-quick-actions-modal-js">
    <div class="content">
        <div class="header">
            <span class="title">Edit Quick Actions</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <p>Edit Quick Action Order Via Drag and Drop</p>
            <p>(First 6 Display as Icons on Block)</p>
            <form method="post" id="edit_quickActions_form" action="{{ action('DashboardController@editBlock') }}">
                @include("partials.dashboard.editQuickOptionsModalForm")
            </form>
        </div>
    </div>
</div>