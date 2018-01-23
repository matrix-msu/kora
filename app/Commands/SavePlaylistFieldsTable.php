<?php namespace App\Commands;

use App\PlaylistField;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class SavePlaylistFieldsTable extends Command implements ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save Playlist Fields Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the playlist fields table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Playlist Fields table.");

        $table_path = $this->backup_filepath . "/playlist_fields/";
        $table_array = $this->makeBackupTableArray("playlist_fields");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        PlaylistField::chunk(500, function($playlists) use ($table_path, $row_id) {
            $count = 0;
            $all_playlistfields_data = new Collection();

            foreach($playlists as $playlist) {
                $individual_playlistfields_data = new Collection();

                $individual_playlistfields_data->put("id", $playlist->id);
                $individual_playlistfields_data->put("rid", $playlist->rid);
                $individual_playlistfields_data->put("fid", $playlist->fid);
                $individual_playlistfields_data->put("flid", $playlist->flid);
                $individual_playlistfields_data->put("audio", $playlist->audio);
                $individual_playlistfields_data->put("created_at", $playlist->created_at->toDateTimeString());
                $individual_playlistfields_data->put("updated_at", $playlist->updated_at->toDateTimeString());

                $all_playlistfields_data->push($individual_playlistfields_data);
                $count++;
            }

            DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
            $increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
            $this->backup_fs->put($table_path . $increment . ".json",json_encode($all_playlistfields_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}