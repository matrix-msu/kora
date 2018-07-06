<div class="modal modal-js modal-mask view-user-modal-js">
  <div class="content view-user">
    <div class="header">
      <?php  $imgpath = 'profiles/' . \Auth::user()->id . '/' . \Auth::user()->profile ?>
      @if(File::exists( config('app.base_path') . '/public/app/' . $imgpath ))
        <img class="profile-picture" src="{{config('app.storage_url') . $imgpath}}">
      @else
        <i class="icon icon-user-little profile-picture"></i>
      @endif
      <a href="#" class="modal-toggle modal-toggle-js">
        <i class="icon icon-cancel"></i>
      </a>
    </div>
    <div class="body">
      <div class="mb-m"><span class="attribute">Name: </span><span>Person Name</span></div>
      <div class="mb-m"><span class="attribute">User Name: </span><span>person.name</span></div>
      <div class="mb-m"><span class="attribute">Email: </span><span>person.name@email.com</span></div>
      <div class="mb-m"><span class="attribute">Organization: </span><span>Matrix</span></div>
    </div>
    <div class="footer mt-xxl">
      <a class="quick-action underline-middle-hover pb-xxs" href="#">
        <span>View Full Profile</span>
        <i class="icon icon-arrow-right"></i>
      </a>
    </div>
  </div>
</div>
