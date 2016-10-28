<?php namespace App\Commands;

use App\DocumentsField;
use App\GalleryField;
use Carbon\Carbon;
use App\RichTextField;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class SaveGalleryFieldsTable extends Command implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Gallery Fields table.");

        $table_path = $this->backup_filepath . "/gallery_fields/";
        $table_array = $this->makeBackupTableArray("gallery_fields");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        GalleryField::chunk(500, function($galleries) use ($table_path, $row_id) {
            $count = 0;
            $all_galleryfields_data = new Collection();

            foreach($galleries as $gal) {
                $individual_galleryfields_data = new Collection();

                $individual_galleryfields_data->put("id", $gal->id);
                $individual_galleryfields_data->put("rid", $gal->rid);
                $individual_galleryfields_data->put("fid", $gal->fid);
                $individual_galleryfields_data->put("flid", $gal->flid);
                $individual_galleryfields_data->put("images", $gal->images);
                $individual_galleryfields_data->put("created_at", $gal->created_at->toDateTimeString());
                $individual_galleryfields_data->put("updated_at", $gal->updated_at->toDateTimeString());

                $all_galleryfields_data->push($individual_galleryfields_data);
                $count++;
            }

            DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
            $increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
            $this->backup_fs->put($table_path . $increment . ".json",json_encode($all_galleryfields_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}