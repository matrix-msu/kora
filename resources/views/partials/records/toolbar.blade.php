<div class="toolbar hidden">
  <div class="left delete-multiple-records-js ml-xs tooltip" tooltip="Delete Record(s)" >
    <i class="icon icon-trash ml-m"></i>
    <span class="count ml-xxs"></span>
  </div>
  <div class="line"></div>
  <div class="middle">
    <span><a class="batch-assign" href="{{ action('RecordController@showSelectedAssignmentView',['pid' => $form->project_id, 'fid' => $form->id]) }}"><i class="icon icon-zap-toolbar mr-xxs"></i>Batch Assign<span class="count ml-xxs"></span></a></span>
    <span class="export-mult-records-js ml-sm"><i class="icon icon-download mr-xxs"></i>Export<span class="count ml-xxs"></span></span>
  </div>
  <div class="line"></div>
  <div class="right mr-m">
    <span class="select-all mr-sm"><i class="icon icon-plus mr-xxs"></i>Select All<span class="count-all ml-xxs"></span></span>
    <span class="deselect-all"><i class="icon icon-minus mr-xxs"></i>Deselect All<span class="count ml-xxs"></span></span>
  </div>
</div>