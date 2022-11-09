#!/bin/bash

# CURRENT DIR
DIR=$(cd $(dirname $0) && pwd)

# TOOLS DIR
TOOLS_DIR=/var/www/html/htdocs_atobarai/tools


# before backup
# sh $DIR/mysqlbackup.sh

cd /var/www/html/htdocs_atobarai/tools

run() {
#---- 2021-05-19 daily01_01.shより移動
    php $TOOLS_DIR/payingtempfixed.php
    if [ $? -ne 0 ]; then
        return 1
    fi

    php $TOOLS_DIR/createmonthdetaildata.php
    if [ $? -ne 0 ]; then
        return 1
    fi

    php $TOOLS_DIR/retentionalert.php
    if [ $? -ne 0 ]; then
        return 1
    fi

    php $TOOLS_DIR/turnoffclaimflg.php
    if [ $? -ne 0 ]; then
        return 1
    fi

    php $TOOLS_DIR/updateenterprisefixeddate.php
    if [ $? -ne 0 ]; then
        return 1
    fi

    php $TOOLS_DIR/cleanup.php
    if [ $? -ne 0 ]; then
        return 1
    fi
#----

    php $TOOLS_DIR/treas_account_data.php
    if [ $? -ne 0 ]; then
        return 1
    fi

    php $TOOLS_DIR/updatebusinessdate.php
    if [ $? -ne 0 ]; then
        return 1
    fi

    php $TOOLS_DIR/mypageorderinvalid.php
    if [ $? -ne 0 ]; then
        return 1
    fi

#    php $TOOLS_DIR/combinecustomer.php
#    if [ $? -ne 0 ]; then
#        return 1
#    fi
#
#    php $TOOLS_DIR/ilucustomerlistimport.php
#    if [ $? -ne 0 ]; then
#        return 1
#    fi
#
#    php $TOOLS_DIR/ilusetpaymentdata.php
#    if [ $? -ne 0 ]; then
#        return 1
#    fi

    php $TOOLS_DIR/old_delete.php
    if [ $? -ne 0 ]; then
        return 1
    fi

}

# daily batch GO!
run

if [ $? -eq 0 ]; then
  # OK
  # after backup
  # sh $DIR/mysqlbackup.sh
  echo OK

else
  # ERROR!!
  php $TOOLS_DIR/batch_error.php daily01

fi
