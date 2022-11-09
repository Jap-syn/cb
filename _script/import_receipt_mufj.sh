#!/bin/bash

cd $(cd $(dirname $0)/../tools;pwd)

run() {
    
    php ./getMufjReceiptData.php
    if [ $? -ne 0 ]; then
        return 1
    fi

    php ./importMufjReceiptData.php
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
  php ./batch_error.php import_receipt_mufj
  
fi

