#!/bin/sh
sudo apt-get update
sudo apt-get install php5-imap php5-mysql php5-memcache php5-curl php-apc php-soap php5-imap php5-gd
chmod -R 755 ../
chmod -R 757 app/protected/data
chmod -R 757 app/protected/runtime
chmod -R 757 app/assets
chmod -R 757 app/protected/config
cp testScript/debugTest.php app/protected/config/debugTest.php
cp testScript/perInstanceTest.php app/protected/config/perInstanceTest.php
chmod -R 757 app/protected/config/debugTest.php
chmod -R 757 app/protected/config/perInstanceTest.php
chmod -R 757 app/version.php