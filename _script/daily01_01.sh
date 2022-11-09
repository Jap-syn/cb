#!/bin/bash

# CURRENT DIR
DIR=$(cd $(dirname $0) && pwd)

# TOOLS DIR
TOOLS_DIR=/var/www/html/htdocs_atobarai/tools


# before backup
# sh $DIR/mysqlbackup.sh

cd /var/www/html/htdocs_atobarai/tools

run() {

    php $TOOLS_DIR/agencydayfixed.php
    if [ $? -ne 0 ]; then
        return 1
    fi

    php $TOOLS_DIR/calcnprate.php
    if [ $? -ne 0 ]; then
        return 1
    fi

    php $TOOLS_DIR/serviceprovided.php
    if [ $? -ne 0 ]; then
        return 1
    fi

    php $TOOLS_DIR/combineclaim.php
    if [ $? -ne 0 ]; then
        return 1
    fi

    php $TOOLS_DIR/jnbreleaseaccount.php
    if [ $? -ne 0 ]; then
        return 1
    fi

    php $TOOLS_DIR/oemclaimtransform.php
    if [ $? -ne 0 ]; then
        return 1
    fi

    php $TOOLS_DIR/smbcpareleaseaccount.php
    if [ $? -ne 0 ]; then
        return 1
    fi

    php $TOOLS_DIR/oemfixedtotal.php
    if [ $? -ne 0 ]; then
        return 1
    fi

    php $TOOLS_DIR/credittransfer_update.php
    if [ $? -ne 0 ]; then
        return 1
    fi

    php $TOOLS_DIR/confirmRequestStatus.php
    if [ $? -ne 0 ]; then
        return 1
    fi

#    # 2021-05-19 コメントアウト
#    php $TOOLS_DIR/payingtempfixed.php
#    if [ $? -ne 0 ]; then
#        return 1
#    fi
#
#    php $TOOLS_DIR/createmonthdetaildata.php
#    if [ $? -ne 0 ]; then
#        return 1
#    fi
#
#    php $TOOLS_DIR/retentionalert.php
#    if [ $? -ne 0 ]; then
#        return 1
#    fi
#
#    php $TOOLS_DIR/turnoffclaimflg.php
#    if [ $? -ne 0 ]; then
#        return 1
#    fi
#
#    php $TOOLS_DIR/updateenterprisefixeddate.php
#    if [ $? -ne 0 ]; then
#        return 1
#    fi
#
#    php $TOOLS_DIR/cleanup.php
#    if [ $? -ne 0 ]; then
#        return 1
#    fi


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