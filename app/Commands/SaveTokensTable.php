<?php namespace App\Commands;


use App\Token;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;;


class SaveTokensTable extends Command implements SelfHandling, ShouldBeQueued
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Token ");

        $table_path = $this->backup_filepath . "/tokens/";

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $this->makeBackupTableArray("tokens")
        );

        $this->backup_fs->makeDirectory($table_path);
        Token::chunk(1000, function($tokens) use ($table_path, $row_id) {
            $count = 0;
            $all_tokens_data = new Collection();

            foreach ($tokens as $token) {
                $individual_token_data = new Collection();

                $token_data = new Collection();
                $token_data->put("id", $token->id);
                $token_data->put("type", $token->type);
                $token_data->put("token", $token->token);
                $token_data->put("created_at", $token->created_at->toDateTimeString());
                $token_data->put("updated_at", $token->updated_at->toDateTimeString());
                $individual_token_data->put("token_data",$token_data);
                $individual_token_data->put("project_data",$token->projects()->get()->modelKeys());

                $all_tokens_data->push($individual_token_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_tokens_data));
        });
    }
}