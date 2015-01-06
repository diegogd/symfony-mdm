#!/bin/sh

if [ -d "certs" ]; then
    cd certs

    export PW=`pwgen -Bs 32 1`
    echo $PW > password

    if [ -f password ]; then
        scripts/generateCAServer.sh
        scripts/generateServerCert.sh
        scripts/exportCertificatesForNginx.sh
    fi
else
    echo "Execute in the path of the project"
fi


