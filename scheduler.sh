#!/bin/sh
cd /home/pojotechin/pojosmartdial_pojotech_in/
php82 artisan schedule:run >> /home/pojotechin/pojosmartdial_pojotech_in/cron.log 2>&1
