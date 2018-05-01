#!/bin/sh

# This script generates a backup of the user data in Kora3 including the
# configuration file .env
# the database that's specified in that configuration file
# and the user submitted data in storage/app
#
# The results are put into backup.timestamp.tgz, which should then be 
# stored off system.

if [ ! -f .env ] 
then
	echo ".env not found.  Kora not configured. refusing to backup nothing."
	exit 1;
fi;
export $(cat .env | grep -v ^# | xargs)

MYSQLDUMP=`which mysqldump`

if [ $? -ne 0 ] 
then 
	echo "no mysqldump found. exiting"; 
	exit 1;  
fi;


mkdir backup-tmp/ || exit 1;

$MYSQLDUMP -h $DB_HOST -u $DB_USERNAME --password=$DB_PASSWORD $DB_DATABASE  --single-transaction --quick --lock-tables=false > backup-tmp/database.sql || exit 1;

tar -czvf backup.$(date +%F.%s).tgz backup-tmp/database.sql .env storage/app/ || exit 1

rm -rf backup-tmp/
