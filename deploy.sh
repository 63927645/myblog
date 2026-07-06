#!/bin/bash

cd /www/wwwroot/wordpress || exit

echo "==== deploy start ====" >> /tmp/deploy.log

git pull origin main >> /tmp/deploy.log 2>&1

echo "==== deploy done ====" >> /tmp/deploy.log
