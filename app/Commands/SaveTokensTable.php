<?php namespace App\Commands;


use App\Token;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;;


class SaveTokensTable extends Command implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Token ");

        $table_path = $this->backup_filepath . "/tokens/";
        $table_array = $this->makeBackupTableArray("tokens");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        Token::chunk(500, function($tokens) use ($table_path, $row_id) {
            $count = 0;
            $all_tokens_data = new Collection();

            foreach ($tokens as $token) {
                $individual_token_data = new Collection();

                $individual_token_data->put("id", $token->id);
                $individual_token_data->put("token", $token->token);
                $individual_token_data->put("title", $token->title);
                $individual_token_data->put("search", $token->search);
                $individual_token_data->put("create", $token->create);
                $individual_token_data->put("edit", $token->edit);
                $individual_token_data->put("delete", $token->delete);
                $individual_token_data->put("created_at", $token->created_at->toDateTimeString());
                $individual_token_data->put("updated_at", $token->updated_at->toDateTimeString());

                $all_tokens_data->push($individual_token_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_tokens_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}