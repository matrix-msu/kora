@php
  $iframeSession = uniqid();
  $processed = preg_replace("/[\r\n]*/","",$typedField->processDisplayData($field, $value));
@endphp
<div class="record-data-card">
  <div class="field-display richtext-field-display richtext-field-display-js">
    <div class="richtext richtext-js">
      <p><iframe id="{{$iframeSession}}" class="richtext-iframe-js" src="about:blank"></iframe></p>
      <div class="show-more-richtext show-more-richtext-js" showing="less">Show All</div>
    </div>
  </div>
</div>
<script>
  var iframe = document.getElementById('{{$iframeSession}}');
  var doc = iframe.contentWindow.document;
  //Store in iframe
  doc.open();
  doc.write('{!! $processed !!}');
  doc.close();
  //Reset the height to fit content, and let KORA handle the max height/read more stuff
  var height = iframe.contentWindow.document.documentElement.scrollHeight; //TODO::Not perfect, but close
  //Don't let it get below 77 though
  if(height < 77)
      height = 77;
  iframe.style.height = height + 'px';
</script>
