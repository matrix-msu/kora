<script src="{{ config('app.url') }}assets/javascripts/vendor/grapejs/grapes.min.js"></script>
<script src="{{ config('app.url') }}assets/javascripts/vendor/grapejs/plugins/blocks/dist/grapesjs-blocks-basic.min.js"></script>
<script src="{{ config('app.url') }}assets/javascripts/vendor/grapejs/plugins/countdown/dist/grapesjs-component-countdown.min.js"></script>
<script src="{{ config('app.url') }}assets/javascripts/vendor/grapejs/plugins/export/dist/grapesjs-plugin-export.min.js"></script>
<script src="{{ config('app.url') }}assets/javascripts/vendor/grapejs/plugins/forms/dist/grapesjs-plugin-forms.min.js"></script>
<script src="{{ config('app.url') }}assets/javascripts/vendor/grapejs/plugins/navbar/dist/grapesjs-navbar.min.js"></script>

{!! Minify::javascript([
  '/assets/javascripts/publish/editor.js',
])->withFullUrl() !!}