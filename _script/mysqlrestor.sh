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
# restore setting
####################

# backup file save directory
dirpath='/var/www/html/htdocs_atobarai/data/backup'

####################
# restore
####################
echo "File List -----------------------------"
ls $dirpath
echo "---------------------------------------"

echo -n "Input Restore File : "
read filename

# restore
#zcat $dirpath/$filename.sql.gz | mysql -u $user -p$password -h $host $database
zcat $dirpath/$filename | mysql -u $user -h $host $database

