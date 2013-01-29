<?php
/*********************************************************************************
 * Zurmo is a customer relationship management program developed by
 * Zurmo, Inc. Copyright (C) 2012 Zurmo Inc.
 *
 * Zurmo is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * Zurmo is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact Zurmo, Inc. with a mailing address at 113 McHenry Road Suite 207,
 * Buffalo Grove, IL 60089, USA. or at email address contact@zurmo.com.
 ********************************************************************************/

/**
 * Helper class for encrypting/decrypting passwords
 */
class ZurmoPasswordSecurityUtil
{
    /**
     * Encrypt value, using CSecurityManager::encrypt method
     * @param string $value
     * @param string $salt
     * @return string
     */
    public static function encrypt($value, $salt = PASSWORD_SALT)
    {
        if ($value == '' || $value == null)
        {
            return $value;
        }
        return base64_encode(Yii::app()->getSecurityManager()->encrypt($value, $salt));
    }

    /**
     * Decrypt value, using CSecurityManager::decrypt method
     * @param string $value
     * @param string $salt
     * @return mixed
     */
    public static function decrypt($value, $salt = PASSWORD_SALT)
    {
        if ($value == '' || $value == null)
        {
            return $value;
        }
        return Yii::app()->getSecurityManager()->decrypt(base64_decode($value), $salt);
    }

    /**
     * Generate zurmo token and write it to version.php file.
     * @param $instanceRoot
     * @return string
     */
    public static function setPasswordSaltAndWriteToPerInstanceFile($instanceRoot, $perInstanceFilename = 'perInstance.php')
    {
        assert('is_dir($instanceRoot)');

        if (!defined('PASSWORD_SALT') || PASSWORD_SALT == 'defaultValue')
        {
            $perInstanceConfigFile     = "$instanceRoot/protected/config/$perInstanceFilename";
            $contents = file_get_contents($perInstanceConfigFile);

            $passwordSalt = substr(md5(microtime() * mt_rand()), 0, 15);

            $contents = preg_replace('/define\(\'PASSWORD_SALT\', \'defaultValue\'\);/',
                "define('PASSWORD_SALT', '$passwordSalt');",
                $contents);

            file_put_contents($perInstanceConfigFile, $contents);
            return $passwordSalt;
        }
        return PASSWORD_SALT;
    }
}
?>