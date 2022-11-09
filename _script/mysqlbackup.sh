#!/bin/bash

####################
# mysql setting
####################

# user
user=root

# password
password=digicom2014

# database
database=coraldb_st02

# host
host=localhost


####################
# backup setting
####################

# backup files save days
period=7

# backup file save directory
dirpath='/var/www/html/htdocs_atobarai/data/backup'

# backup filename
filename=`date +%Y%m%d_%H%M%S_$database`


find $dirpath -mtime +$period -name "*.sql.gz" | xargs rm -f


# mysqldump
# mysqldump -u $user -p$password -h $host $database | gzip > $dirpath/$filename.sql.gz
mysqldump -u $user -h $host $database | gzip > $dirpath/$filename.sql.gz

# mod permission
chmod 700 $dirpath/$filename.sql.gz
