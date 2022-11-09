#!/bin/bash

cd /var/www/html/htdocs_atobarai/tools

run() {
    
    php ./importzip.php
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
  php ./batch_error.php importzip

fi

