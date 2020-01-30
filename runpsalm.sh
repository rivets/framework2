#!/bin/sh
./vendor/vimeo/psalm/psalm --config=psalm.xml --show-info --find-dead-code
./vendor/vimeo/psalm/psalm --config=ipsalm.xml --show-info --find-dead-code