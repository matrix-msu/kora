<?php namespace App\Commands;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FormEmails extends MailCommand implements ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Form Emails
    |--------------------------------------------------------------------------
    |
    | This command handles emails sent out related to forms
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle() {
        switch($this->operation) {
            case 'FormAssociationRequest':
                $myForm = $this->options['myForm'];
                $myProj = $this->options['myProj'];
                $thierForm = $this->options['thierForm'];
                $thierProj = $this->options['thierProj'];
                $user = $this->options['user'];
                try {
                    Mail::send('emails.request.assoc', compact('myForm', 'myProj', 'thierForm', 'thierProj'), function ($message) use ($user) {
                        $message->from(config('mail.from.address'));
                        $message->to($user->email);
                        $message->subject('Kora Form Association Request');
                    });
                } catch(\Exception $e) {
                    Log::error("Form Association Request Email Failed!!!");
                    Log::info($e);
                }
                break;
            default:
                Log::info("Unknown email type was requested.");
                break;
        }
    }

}
