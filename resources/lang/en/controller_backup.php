<?php

return [

	//controller_backup english translations

	"admin" => "Only the default admin can view that page",
	"whoops" => "Whoops.",
	"fileexists" => "Could not create the backup, a file with that name already exists.",
	"cantsave" => "The backup failed, unable to save the backup file.",
	"errors" => "The backup completed with errors, the backup may be corrupted.  You can try downloading the file, if one was created.",
	"complete" => "The backup completed successfully",
	"nofiletemp" => "There is no file available right now.  You may have already downloaded the file, or there may have been an error during the backup process",
	"correct" => "The backup failed, correct these errors and try again.",
	"badrestore" => "The restore point you selected is not valid.",
	"cantmove" => "The file could not be moved to the backup directory.",
	"badfile" => "There is something wrong with the file that was uploaded",
	"nofiles" => "No file was uploaded.",
	"noselect" => "You did not select a valid restore point or upload a valid backup file",
	"noopen" => "The backup file couldn't be opened.  Make sure it still exists and the permissions are correct.",
	"badjson" => "The backup file contains invalid JSON data, it may be corrupt or damaged.  Check the file or try another one.  The restore did not start, so data already in the database was not deleted.",
	"reqmedia" => "Sorry, the required media files could not be found at ",
	"placefiles" => "  Place the files in that location, or create an empty directory with that name to proceed without them.  The existing database and records were not deleted, it should be safe to unlock users.",
	"dbpermission" => "There was a problem when attempting to remove existing information from the database, the database user may not have permission to do this or the database may be in use.",
	"filepermission" => "There was a problem when attempting to remove existing media files, make sure the permissions are correct and the files are not in use.",
	"mediaproblem" => "There is a problem with the media files for Documents/Gallery/Video/Model fields.",
	"record" => "Record ",
	"partrestored" => " is missing files, and was only partially restored. Locate the missing files and run the restore process again.",
	"docmissing" => " Documents field was not restored because it is missing all required files.",
	"galmissing" => " Gallery field was not restored because it is missing files.",
	"modmissing" => " Model field was not restored because it is missing files.",
	"notres" => " was not restored because it is missing files.",
	"unknown" => "An unknown error prevented the restore from completing. You can try restoring from a different backup file or restore point. Users will stay locked out until you run a successful restore or manually unlock them above. For this error, it's not recommended that you unlock users unless you have resolved the problem",
	"notalldata" => "Not all of your data was restored, check the errors below for details. The errors are in the order that they occurred, if you can resolve the first error, it will often correct one or more of the errors below it. Users will stay locked out until you run a successful restore or manually unlock them above.",
	"success" => "The restore completed successfully.",
	"notexist" => "Media file does not exist: ",

];