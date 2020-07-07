#!/bin/sh
git pull origin devel
./vendor/vimeo/psalm/psalm --config=testing/psalm.xml --clear-cache
./vendor/vimeo/psalm/psalm --no-progress --stats --find-unused-psalm-suppress --config=testing/psalm.xml --show-info=true --find-dead-code=true --taint-analysis
./vendor/vimeo/psalm/psalm --no-progress --stats --find-unused-psalm-suppress --config=testing/ipsalm.xml --show-info=true --find-dead-code=true --taint-analysis
./vendor/bin/phpstan analyse class devel index.php ajax.php install.php
./vendor/bin/phpinsights --config-path=testing/phpinsights.php -n -vv 
