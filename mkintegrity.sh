#!/bin/sh
code=`openssl dgst -sha384 -binary $1 | openssl base64  -A`
echo integrity=\"sha384-$code\" crossorigin=\"anonymous\"

