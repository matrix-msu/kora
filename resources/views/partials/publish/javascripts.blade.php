<script src="{{ env('BASE_URL') }}grapejs/dist/grapes.min.js"></script>
<script src="{{ env('BASE_URL') }}grapejs/plugins/blocks/dist/grapesjs-blocks-basic.min.js"></script>
<script src="{{ env('BASE_URL') }}grapejs/plugins/export/dist/grapesjs-plugin-export.min.js"></script>

{!! Minify::javascript([
  '/assets/javascripts/publish/editor.js',
])->withFullUrl() !!}