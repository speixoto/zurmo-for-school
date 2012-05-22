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
     * Improved Locale class to overcome some issues in Yii's CLocale.
     * Needed to redefine the constructor as the CLocale's constructor is protected.
     */
    class ZurmoLocale extends CLocale
    {

        private $_id;
        private $_data;
        private $_dateFormatter;
        private $_numberFormatter;

        /**
         * Returns the instance of the specified locale.
         * Since the constructor of CLocale is protected, you can only use
         * this method to obtain an instance of the specified locale.
         * @param string $id the locale ID (e.g. en_US)
         * @return CLocale the locale instance
         */
        public static function getInstance($id)
        {
            static $locales=array();
            if(isset($locales[$id]))
                return $locales[$id];
            else
                return $locales[$id] = new ZurmoLocale($id);
        }

        /**
         * Constructor.
         * Since the constructor is protected, please use {@link getInstance}
         * to obtain an instance of the specified locale.
         * @param string $id the locale ID (e.g. en_US)
         */
        protected function __construct($id)
        {
            parent::__construct($id);
        }


        /**
         * @return string datetime format, i.e., the order of date and time.
         */
        public function getDateTimeFormat()
        {
            if (in_array($this->_id, array('ja')))
            {
                return '{0} {1}';
            }
            return $this->_data['dateTimeFormat'];
        }
    }
?>