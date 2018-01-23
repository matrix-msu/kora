<script src="{{ config('app.url') }}grapejs/dist/grapes.min.js"></script>
<script src="{{ config('app.url') }}grapejs/plugins/blocks/dist/grapesjs-blocks-basic.min.js"></script>
<script src="{{ config('app.url') }}grapejs/plugins/countdown/dist/grapesjs-component-countdown.min.js"></script>
<script src="{{ config('app.url') }}grapejs/plugins/export/dist/grapesjs-plugin-export.min.js"></script>
<script src="{{ config('app.url') }}grapejs/plugins/forms/dist/grapesjs-plugin-forms.min.js"></script>
<script src="{{ config('app.url') }}grapejs/plugins/navbar/dist/grapesjs-navbar.min.js"></script>

{!! Minify::javascript([
  '/assets/javascripts/publish/editor.js',
])->withFullUrl() !!}