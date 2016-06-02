<?php namespace App\Commands;

use App\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class SaveUsersTable extends Command implements SelfHandling, ShouldBeQueued
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */

    public function handle() {
        Log::info("Started backing up the Forms table.");

        $table_path = $this->backup_filepath . "/users/";

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $this->makeBackupTableArray("users")
        );
        DB::table('backup_partial_progress')->where('id',$row_id)->decrement("overall",1);

        $this->backup_fs->makeDirectory($table_path);
        User::chunk(1000, function($users) use ($table_path, $row_id) {
            $count = 0;
            $all_users_data = new Collection();

            foreach($users as $user) {
                $individual_user_data = new Collection();

                if ($user->id == 1) continue; //skip the first admin account (the user who will be restoring)
                $individual_user_data->put("id", $user->id);
                $individual_user_data->put("admin", $user->admin);
                $individual_user_data->put("active", $user->active);
                $individual_user_data->put("username", $user->username);
                $individual_user_data->put("name", $user->name);
                $individual_user_data->put("email", $user->email);
                $individual_user_data->put("password", $user->password);
                $individual_user_data->put("organization", $user->organization);
                $individual_user_data->put("language", $user->language);
                $individual_user_data->put("regtoken", $user->regtoken);
                $individual_user_data->put("remember_token", $user->remember_token);
                $individual_user_data->put("created_at", $user->created_at->toDateTimeString());
                $individual_user_data->put("updated_at", $user->updated_at->toDateTimeString());

                $all_users_data->push($individual_user_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_users_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}