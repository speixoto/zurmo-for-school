<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    class ImportModelTestItem extends OwnedSecurableItem
    {
        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'fullName' => 'Name',
                )
            );
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'firstName',
                    'lastName',
                    'boolean',
                    'date',
                    'dateTime',
                    'decimal',
                    'float',
                    'integer',
                    'numerical',
                    'phone',
                    'string',
                    'textArea',
                    'url',
            ),
                'relations' => array(
                    'currencyValue'    => array(static::HAS_ONE,   'CurrencyValue',    static::OWNED),
                    'dropDown'         => array(static::HAS_ONE,   'OwnedCustomField', static::OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'dropDown'),
                    'radioDropDown'    => array(static::HAS_ONE,   'OwnedCustomField', static::OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'radioDropDown'),
                    'multiDropDown'    => array(static::HAS_ONE,   'OwnedMultipleValuesCustomField', static::OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'multiDropDown'),
                    'tagCloud'         => array(static::HAS_ONE,   'OwnedMultipleValuesCustomField', static::OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'tagCloud'),
                    'hasOne'           => array(static::HAS_ONE,   'ImportModelTestItem2', static::NOT_OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'hasOne'),
                    'hasMany'          => array(static::MANY_MANY, 'ImportModelTestItem3'),
                    'hasOneAlso'       => array(static::HAS_ONE,   'ImportModelTestItem4', static::NOT_OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'hasOneAlso'),
                    'primaryEmail'     => array(static::HAS_ONE,   'Email', static::OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'primaryEmail'),
                    'primaryAddress'   => array(static::HAS_ONE,   'Address', static::OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'primaryAddress'),
                    'secondaryEmail'   => array(static::HAS_ONE,   'Email', static::OWNED,
                                                static::LINK_TYPE_SPECIFIC, 'secondaryEmail'),

                ),
                'rules' => array(
                    array('firstName', 'type',   'type' => 'string'),
                    array('firstName', 'length', 'min'  => 1, 'max' => 32),
                    array('lastName',  'required'),
                    array('lastName',  'type',   'type' => 'string'),
                    array('lastName',  'length', 'min'  => 2, 'max' => 32),
                    array('boolean',   'boolean'),
                    array('date',      'type', 'type' => 'date'),
                    array('dateTime',  'type', 'type' => 'datetime'),
                    array('decimal',   'type', 'type' => 'float'),
                    array('decimal',   'numerical', 'precision' => 2),
                    array('float',     'type',    'type' => 'float'),
                    array('integer',   'type',    'type' => 'integer'),
                    array('numerical', 'type',    'type' => 'integer'),
                    array('numerical', 'numerical', 'min' => 0, 'max' => 100),
                    array('phone',     'type',    'type' => 'string'),
                    array('phone',     'length',  'min'  => 1, 'max' => 14),
                    array('string',    'required'),
                    array('string',    'type',  'type' => 'string'),
                    array('string',    'length',  'min'  => 3, 'max' => 64),
                    array('textArea',  'type',    'type' => 'string'),
                    array('url',       'url'),
                ),
                'elements' => array(
                    'currencyValue'       => 'CurrencyValue',
                    'date'                => 'Date',
                    'dateTime'            => 'DateTime',
                    'hasOne'              => 'ImportModelTestItem2',
                    'hasOneAlso'          => 'ImportModelTestItem4',
                    'phone'               => 'Phone',
                    'primaryEmail'        => 'EmailAddressInformation',
                    'secondaryEmail'      => 'EmailAddressInformation',
                    'primaryAddress'      => 'Address',
                    'textArea'            => 'TextArea',
                    'radioDropDown'       => 'RadioDropDown',
                    'multiDropDown'       => 'MultiSelectDropDown',
                    'tagCloud'            => 'TagCloud',
                ),
                'customFields' => array(
                    'dropDown'        => 'ImportTestDropDown',
                    'radioDropDown'   => 'ImportTestRadioDropDown',
                    'multiDropDown'   => 'ImportTestMultiDropDown',
                    'tagCloud'        => 'ImportTestTagCloud',
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
            return 'ImportModule';
        }
    }
?>
