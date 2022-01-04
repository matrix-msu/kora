<?php namespace App\Commands;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProjectEmails extends MailCommand implements ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Project Emails
    |--------------------------------------------------------------------------
    |
    | This command handles emails sent out related to projects
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
            case 'NewProjectUser':
                $userMail = $this->options['userMail'];
                $name = $this->options['name'];
                $group = $this->options['group'];
                $project = $this->options['project'];
                try {
                    Mail::send('emails.project.added', compact('project', 'name', 'group'), function ($message) use ($userMail) {
                        $message->from(config('mail.from.address'));
                        $message->to($userMail);
                        $message->subject('kora Project Permissions');
                    });
                } catch(\Exception $e) {
                    Log::error("New Project User Email Failed!!!");
                    Log::info($e);
                }
                break;
            case 'ProjectPermissionsUpdated':
                $email = $this->options['email'];
                $userMail = $this->options['userMail'];
                $name = $this->options['name'];
                $group = $this->options['group'];
                $project = $this->options['project'];
                try {
                    Mail::send($email, compact('project', 'name', 'group'), function ($message) use ($userMail) {
                        $message->from(config('mail.from.address'));
                        $message->to($userMail);
                        $message->subject('kora Project Permissions');
                    });
                } catch(\Exception $e) {
                    Log::error("Project Permissions Updated Email Failed!!!");
                    Log::info($e);
                }
                break;
                break;
            default:
                Log::info("Unknown email type was requested.");
                break;
        }
    }

}
