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

    class P extends RedBeanModel
    {
        public static function getByName($name)
        {
            assert('is_string($name)');
            assert('$name != ""');
            $bean = R::findOne('p', "name = '$name'");
            assert('$bean === false || $bean instanceof RedBean_OODBBean');
            if ($bean === false)
            {
                throw new NotFoundErception();
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
                    'name'
                ),
                'rules' => array(
                    array('name',          'required'),
                    array('name',          'type',    'type' => 'string'),
                    array('name',          'length',  'min'  => 3, 'max' => 64),
                ),
                'relations' => array(
                    'pp'                  => array(RedBeanModel::HAS_ONE,            'PP'),
                    'pp1'                 => array(RedBeanModel::HAS_ONE,            'PP',  RedBeanModel::NOT_OWNED,
                                                   RedBeanModel::LINK_TYPE_SPECIFIC, 'pp1Link'),
                    'pp2'                 => array(RedBeanModel::HAS_ONE,            'PP',  RedBeanModel::NOT_OWNED,
                                                   RedBeanModel::LINK_TYPE_SPECIFIC, 'pp2Link'),
                    'ppp'                 => array(RedBeanModel::HAS_MANY,           'PPP'),
                    'ppp1'                => array(RedBeanModel::HAS_MANY,           'PPP', RedBeanModel::NOT_OWNED,
                                                   RedBeanModel::LINK_TYPE_SPECIFIC, 'ppp1Link'),
                    'ppp2'                => array(RedBeanModel::HAS_MANY,           'PPP', RedBeanModel::NOT_OWNED,
                                                   RedBeanModel::LINK_TYPE_SPECIFIC, 'ppp2Link'),
                    'ppManyAssumptive'    => array(RedBeanModel::MANY_MANY,          'PP'),
                    'ppManySpecific'      => array(RedBeanModel::MANY_MANY,          'PP', RedBeanModel::NOT_OWNED,
                                                   RedBeanModel::LINK_TYPE_SPECIFIC, 'ppManySpecificLink'),
                ),
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function getModuleClassName()
        {
            return 'TestModule';
        }
    }
?>
