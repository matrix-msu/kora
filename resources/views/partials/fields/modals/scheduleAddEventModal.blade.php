<div class="modal modal-js modal-mask schedule-add-event-modal-js">
    <div class="content">
        <div class="header">
            <span class="title title-js">Add a New Event</span>
            <a href="#" class="modal-toggle modal-toggle-js">
                <i class="icon icon-cancel"></i>
            </a>
        </div>
        <div class="body">
            <div class="form-group">
                {!! Form::label('eventname','Event Name: ') !!}
                <input type="text" class="text-input event-name-js" maxlength="24"
                       placeholder="Enter the Event name here"/>
            </div>
            <div class="form-group mt-xl half">
                {!! Form::label('startdatetime','Start Time: ') !!}
                <input type='text' class="text-input event-start-time-js"/>
            </div>
            <div class="form-group mt-xl half">
                {!! Form::label('enddatetime','End Time: ') !!}
                <input type='text' class="text-input event-end-time-js"/>
            </div>
            <div class="form-group mt-xl">
                <label for="allday">All Day?</label>
                <div class="check-box">
                    <input type="checkbox" value="1" id="preset" class="check-box-input event-allday-js" name="allday" />
                    <div class="check-box-background"></div>
                    <span class="check"></span>
                    <span class="placeholder">Select to set the event as all day</span>
                    <span class="placeholder-alt">Event is set to be all day</span>
                </div>

                <p class="sub-text mt-sm">
                    Designate Start and End Days as All Day Events
                </p>
            </div>
            <div class="form-group mt-xxxl">
                <a href="#" class="btn add-new-event-js">Create Event</a>
            </div>
        </div>
    </div>
</div>