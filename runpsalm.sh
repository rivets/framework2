#!/bin/sh
./vendor/vimeo/psalm/psalm --no-progress --stats --clear-cache --find-unused-psalm-suppress --config=psalm.xml --show-info=true --find-dead-code=true --taint-analysis
./vendor/vimeo/psalm/psalm --no-progress --stats --clear-cache --config=ipsalm.xml --find-unused-psalm-suppress --show-info=true --find-dead-code=true --taint-analysis