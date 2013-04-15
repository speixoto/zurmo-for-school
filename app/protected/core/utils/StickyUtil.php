<?php

    /**
     * Helper for working with sticky items such as sticky search or sticky reports
     * Extend as needed
     */
    class StickyUtil
    {
        public static function clearDataByKey($key)
        {
            assert('is_string($key) || is_int($key)');
            Yii::app()->user->setState($key, null);
        }

        public static function getDataByKey($key)
        {
            assert('is_string($key) || is_int($key)');
            $stickyData = Yii::app()->user->getState($key);
            if ($stickyData == null)
            {
                return null;
            }
            return unserialize($stickyData);
        }

        public static function setDataByKeyAndData($key, array $data)
        {
            Yii::app()->user->setState($key, serialize($data));
        }
    }
?>