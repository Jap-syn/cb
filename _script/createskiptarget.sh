#!/bin/bash

cd /var/www/html/htdocs_atobarai/tools

run() {
    
    php ./createskiptarget.php
    if [ $? -ne 0 ]; then
        return 1
    fi
    
}

# 多重起動チェック
CMDLINE=$(cat /proc/$$/cmdline | xargs --null)
if [[ $$ -ne $(pgrep -oxf "${CMDLINE}") ]]; then
  exit
fi

# batch GO!
run

if [ $? -eq 0 ]; then
  # OK
  echo OK

else
  # ERROR!!
  echo ERROR
  php ./batch_error.php createskiptarget.php

fi

