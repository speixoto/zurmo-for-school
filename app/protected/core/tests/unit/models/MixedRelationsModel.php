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

    class MixedRelationsModel extends RedBeanModel
    {
        public static function getByName($name)
        {
            assert('is_string($name)');
            assert('$name != ""');
            $bean = R::findOne('a', "name = '$name'");
            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            if ($bean === false)
            {
                throw new NotFoundException();
            }
            return self::makeModel($bean);
        }

        public static function canSaveMetadata()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'aName',
                    'bName',
                    'date',
                    'date2',
                    'dateTime',
                    'dateTime2'
                ),
                'rules' => array(
                    array('aName',     'type',   'type' => 'string'),
                    array('aName',     'length', 'min'  => 1, 'max' => 32),
                    array('bName',     'required'),
                    array('bName',     'type',   'type' => 'string'),
                    array('bName',     'length', 'min'  => 2, 'max' => 32),
                    array('date',      'type', 'type' => 'date'),
                    array('date2',     'type', 'type' => 'date'),
                    array('dateTime',  'type', 'type' => 'datetime'),
                    array('dateTime2', 'type', 'type' => 'datetime'),
                ),
                'relations' => array(
                    'primaryA'     => array(RedBeanModel::HAS_ONE, 'A', RedBeanModel::OWNED),
                    'secondaryA'   => array(RedBeanModel::HAS_ONE, 'A', RedBeanModel::OWNED),
                    'manyMany'     => array(RedBeanModel::MANY_MANY, 'DateDateTime', RedBeanModel::OWNED),
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }
    }
?>
