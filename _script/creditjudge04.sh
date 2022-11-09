#!/bin/bash

cd $(cd $(dirname $0)/../tools;pwd)

run() {
    php ./creditjudgeawsbatch.php 3 ${CJ_BATCH_SEQ}
    if [ $? -eq 100 ]; then
        return 0
    fi
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
  php ./batch_error.php creditjudge04
  
fi

# s3 upload
targetDate=`date "+%Y%m%d"`
target=../data/log/cbadmin_log_${targetDate}.txt
if [ ! -f ${target} ]; then
    targetDate=`date --date "1 days ago" "+%Y%m%d"`
    target=../data/log/cbadmin_log_${targetDate}.txt
fi

mv ${target} ../data/log/cbadmin_log_${targetDate}_${CJ_BATCH_SEQ}.txt
aws s3 cp ../data/log/cbadmin_log_`date "+%Y%m%d"`_${CJ_BATCH_SEQ}.txt s3://${S3_BACKET_NAME}/`date "+%Y%m"`/
