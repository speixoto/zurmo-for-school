chmod -R 755 ../../
chmod -R 757 ../app/protected/data
chmod -R 757 ../app/protected/runtime
chmod -R 757 ../app/assets
chmod -R 757 ../app/protected/config
cp debugTest.php ../app/protected/config/debugTest.php
cp perInstanceTest.php ../app/protected/config/perInstanceTest.php
chmod -R 757 ../app/protected/config/debugTest.php
chmod -R 757 ../app/protected/config/perInstanceTest.php
chmod -R 757 ../app/version.php