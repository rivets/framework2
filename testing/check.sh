#!/bin/sh
git pull origin devel
./vendor/vimeo/psalm/psalm --clear-cache
./vendor/vimeo/psalm/psalm --no-progress --stats --find-unused-psalm-suppress --config=testing/psalm.xml --show-info=true --find-dead-code=true --taint-analysis
./vendor/vimeo/psalm/psalm --no-progress --stats --config=ipsalm.xml --find-unused-psalm-suppress --show-info=true --find-dead-code=true --taint-analysis
./vendor/bin/phpstan analyse class devel index.php ajax.php install.php
./vendor/bin/phpinsights -n -vv -c testing/phpinsights.php
