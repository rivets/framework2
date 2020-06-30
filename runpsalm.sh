#!/bin/sh
./vendor/vimeo/psalm/psalm --clear-cache
./vendor/vimeo/psalm/psalm --no-progress --stats --find-unused-psalm-suppress --config=psalm.xml --show-info=true --find-dead-code=true --taint-analysis
./vendor/vimeo/psalm/psalm --no-progress --stats --config=ipsalm.xml --find-unused-psalm-suppress --show-info=true --find-dead-code=true --taint-analysis