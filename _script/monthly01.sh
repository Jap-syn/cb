#!/bin/bash

cd /var/www/html/htdocs_atobarai/tools

run() {

    php ./agencymonthfixed.php
    if [ $? -ne 0 ]; then
        return 1
    fi
    
    php ./autocreatecalendar.php
    if [ $? -ne 0 ]; then
        return 1
    fi

}

# monthly batch GO!
run

if [ $? -eq 0 ]; then
  # OK
  echo OK

else
  # ERROR!!
  echo ERROR
  php ./batch_error.php montly01

fi

