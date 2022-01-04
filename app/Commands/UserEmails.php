<?php namespace App\Commands;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserEmails extends MailCommand implements ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | User Emails
    |--------------------------------------------------------------------------
    |
    | This command handles emails sent out related to users
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
            case 'BatchUserInvite':
                $token = $this->options['token'];
                $password = $this->options['password'];
                $username = $this->options['username'];
                $personal_message = $this->options['personal_message'];
                $sender = $this->options['sender'];
                $project = $this->options['project'];
                $projectGroup = $this->options['projectGroup'];
                $email = $this->options['email'];
                try {
                    Mail::send('emails.batch-activation', compact('token', 'password', 'username', 'personal_message', 'sender', 'project', 'projectGroup'), function ($message) use ($email) {
                        $message->from(config('mail.from.address'));
                        $message->to($email);
                        $message->subject('kora Account Activation');
                    });
                } catch(\Exception $e) {
                    Log::error("Batch User Invite Email Failed!!!");
                    Log::info($e);
                }
                break;
            default:
                Log::info("Unknown email type was requested.");
                break;
        }
    }

}
