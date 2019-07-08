@unless (count($revisions) == 0 && is_null($_GET) || ($_GET && (!array_key_exists('records', $_GET) && !array_key_exists('users', $_GET) && !array_key_exists('dates', $_GET))))
  <section class="filters center">
      @if (!isset($rid) || Request::get('revisions'))
          <div class="form-group mt-xxxl mb-xl">
              @php
                  $recordListValues = [];
                  foreach ($records as $record) {
                      $recordListValues[$record] = $record;
                  }

                  $placeholder = 'Currently Showing All Records';
                  if (count($selected_records) > 0) {
                      $placeholder = 'Select More Records to Show Revisions For';
                  }
              @endphp

              <label>Select Record(s) to Show Revisions For</label>
              <span class="error-message"></span>
              {!! Form::select('records[]', $recordListValues, $selected_records,
                  ['class' => 'multi-select', 'data-placeholder' => $placeholder, 'Multiple', 'id' => 'records-multi-select']) !!}
          </div>
      @endif
      <div class="form-group half">
          <label>Revision Date(s)</label>
          <input name="date-filter" id="date-filter" type="text" class="text-input date-picker-js" placeholder="Currently Showing All Dates">
      </div>
      <div class="form-group half">
          @php
              $userListValues = [];
              foreach ($revisions as $revision) {
                  $userListValues[$revision->owner] = $revision->owner;
              }

              $placeholder = 'Currently Showing All Users';
              if (count($selected_users) > 0) {
                  $placeholder = 'Select More Users to Show Revisions For';
              }
          @endphp

          <label>Revised By User(s)</label>
          {!! Form::select('users[]', $userListValues, $selected_users,
              ['class' => 'multi-select', 'data-placeholder' => $placeholder, 'Multiple', 'id' => 'users-multi-select']) !!}
      </div>
  </section>
@endif

@unless (count($revisions) == 0 && $_GET && (array_key_exists('records', $_GET) || array_key_exists('users', $_GET) || array_key_exists('dates', $_GET)))
  <section class="filters center">
      <div class="pagination-options pagination-options-js">
          <select class="page-count option-dropdown-js" id="page-count-dropdown">
              <option value="10">10 per page</option>
              <option value="20" {{app('request')->input('page-count') === '20' ? 'selected' : ''}}>20 per page</option>
              <option value="30" {{app('request')->input('page-count') === '30' ? 'selected' : ''}}>30 per page</option>
          </select>
          <select class="order option-dropdown-js" id="order-dropdown">
              <option value="lmd">Last Modified Descending</option>
              <option value="lma" {{app('request')->input('order') === 'lma' ? 'selected' : ''}}>Last Modified Ascending</option>
              <option value="idd" {{app('request')->input('order') === 'idd' ? 'selected' : ''}}>ID Descending</option>
              <option value="ida" {{app('request')->input('order') === 'ida' ? 'selected' : ''}}>ID Ascending</option>
          </select>
      </div>
      <div class="show-options show-options-js">
          <a href="#" class="tooltip expand-fields-js" tooltip="Expand all fields"><i class="icon icon-expand icon-expand-js"></i></a>
          <a href="#" class="tooltip collapse-fields-js" tooltip="Collapse all fields"><i class="icon icon-condense icon-condense-js"></i></a>
      </div>
  </section>
@endif
