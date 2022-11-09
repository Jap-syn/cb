#!/bin/bash

cd $(cd $(dirname $0)/../tools;pwd)

run() {
    php ./syncNewOrderApi2Back.php
    if [ $? -ne 0 ]; then
        return 1
    fi
    php ./removeNewOrderApiRegistData.php
    if [ $? -ne 0 ]; then
        return 1
    fi
}

# batch GO!
run

if [ $? -eq 0 ]; then
  # OK
  echo OK
else
  # ERROR!!
  echo ERROR
  php ./batch_error.php syncNewOrderApi2Back
  
fi

# s3 upload
targetDate=`date "+%Y%m%d"`
formatDate=`date "+%Y%m%d%H%M%S"`
target=../data/log/cbadmin_log_${targetDate}.txt
if [ ! -f ${target} ]; then
    targetDate=`date --date "1 days ago" "+%Y%m%d"`
    target=../data/log/cbadmin_log_${targetDate}.txt
fi

mv ${target} ../data/log/${formatDate}.txt
aws s3 cp ../data/log/${formatDate}.txt s3://${S3_BACKET_NAME}/`date "+%Y%m"`/`date "+%d"`/syncNewOrderApi2Back/`date "+%H"`/
