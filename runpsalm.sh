#!/bin/sh
./vendor/vimeo/psalm/psalm --config=psalm.xml --show-info=true --find-dead-code=true
./vendor/vimeo/psalm/psalm --config=ipsalm.xml --show-info=true --find-dead-code=true