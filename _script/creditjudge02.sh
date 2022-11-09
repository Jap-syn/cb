#!/bin/bash

cd /var/www/html/htdocs_atobarai/tools

run() {
    
    php ./creditjudge.php 1
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
  php ./batch_error.php creditjudge02
  
fi

