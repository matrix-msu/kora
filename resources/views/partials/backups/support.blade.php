@if(\Illuminate\Support\Facades\Session::has('user_backup_support'))
    <h1 id="user_backup_support_message">( ͡° ͜ʖ ͡°) </h1>
    <script>
        setTimeout(function(){$("#user_backup_support_message").remove();},3000);
    </script>
@endif