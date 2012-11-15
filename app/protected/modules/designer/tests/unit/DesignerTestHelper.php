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

    class DesignerTestHelper
    {
        public static function setCheckBoxAttribute($attributeName, $withDefaultData, $modelClassName)
        {
            assert('isset($attributeName) && $attributeName != null');
            assert('isset($withDefaultData) && is_bool($withDefaultData)');
            $attributeForm = new CheckBoxAttributeForm();
            $attributeForm->attributeName    = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Checkbox',
                'en' => 'Test Checkbox',
                'es' => 'Test Checkbox',
                'fr' => 'Test Checkbox',
                'it' => 'Test Checkbox',
            );
            $attributeForm->isAudited        = true;

            if ($withDefaultData)
            {
                $attributeForm->defaultValue     = 1; //means checked.
            }

            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $validate = $attributeForm->validate();
            if ($validate == false)
            {
                throw new FailedToValidateException();
            }
            $adapter = new $modelAttributesAdapterClassName(new $modelClassName());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                Yii::app()->end(0,false);
            }
        }

        public static function setCurrencyAttribute($attributeName, $withDefaultData, $modelClassName)
        {
            assert('isset($attributeName) && $attributeName != null');
            assert('isset($withDefaultData) && is_bool($withDefaultData)');

            $attributeForm = new CurrencyValueAttributeForm();
            $attributeForm->attributeName    = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Currency',
                'en' => 'Test Currency',
                'es' => 'Test Currency',
                'fr' => 'Test Currency',
                'it' => 'Test Currency',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = false;
            $validate = $attributeForm->validate();
            if ($validate == false)
            {
                throw new FailedToValidateException();
            }
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new $modelClassName());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                Yii::app()->end(0,false);
            }
        }

        public static function setDateAttribute($attributeName, $withDefaultData, $modelClassName)
        {
            assert('isset($attributeName) && $attributeName != null');
            assert('isset($withDefaultData) && is_bool($withDefaultData)');

            $attributeForm = new DateAttributeForm();
            $attributeForm->attributeName = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Date',
                'en' => 'Test Date',
                'es' => 'Test Date',
                'fr' => 'Test Date',
                'it' => 'Test Date',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = false;

            if ($withDefaultData)
            {
                $attributeForm->defaultValueCalculationType  = DateTimeCalculatorUtil::YESTERDAY;
            }
            $validate = $attributeForm->validate();
            if ($validate == false)
            {
                throw new FailedToValidateException();
            }
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new $modelClassName());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                Yii::app()->end(0,false);
            }
        }

        public static function setDateTimeAttribute($attributeName, $withDefaultData, $modelClassName)
        {
            assert('isset($attributeName) && $attributeName != null');
            assert('isset($withDefaultData) && is_bool($withDefaultData)');

            $attributeForm = new DateTimeAttributeForm();
            $attributeForm->attributeName = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test DateTime',
                'en' => 'Test DateTime',
                'es' => 'Test DateTime',
                'fr' => 'Test DateTime',
                'it' => 'Test DateTime',
            );
            $attributeForm->isAudited                    = true;
            $attributeForm->isRequired                   = false;

            if ($withDefaultData)
            {
                $attributeForm->defaultValueCalculationType  = DateTimeCalculatorUtil::NOW;
            }
            else
            {
                $attributeForm->defaultValueCalculationType  = null;
            }
            $validate = $attributeForm->validate();
            if ($validate == false)
            {
                throw new FailedToValidateException();
            }
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new $modelClassName());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                Yii::app()->end(0,false);
            }
        }


        public static function setDecimalAttribute($attributeName, $withDefaultData, $modelClassName)
        {
            assert('isset($attributeName) && $attributeName != null');
            assert('isset($withDefaultData) && is_bool($withDefaultData)');

            $attributeForm = new DecimalAttributeForm();
            $attributeForm->attributeName   = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Decimal',
                'en' => 'Test Decimal',
                'es' => 'Test Decimal',
                'fr' => 'Test Decimal',
                'it' => 'Test Decimal',
            );
            $attributeForm->isAudited       = true;
            $attributeForm->isRequired      = false;
            $attributeForm->maxLength       = 11;
            $attributeForm->precisionLength = 5;

            if ($withDefaultData)
            {
                $attributeForm->defaultValue    = 34.213;
            }
            $validate = $attributeForm->validate();
            if ($validate == false)
            {
                throw new FailedToValidateException();
            }
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new $modelClassName());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                Yii::app()->end(0,false);
            }
        }


        public static function setDropDownAttribute($attributeName, $withDefaultData, $modelClassName)
        {
            assert('isset($attributeName) && $attributeName != null');
            assert('isset($withDefaultData) && is_bool($withDefaultData)');

            $values = array(
                '747',
                'A380',
                'Seaplane',
                'Dive Bomber',
            );
            $labels = array('fr' => array('747 fr', 'A380 fr', 'Seaplane fr', 'Dive Bomber fr'),
                            'de' => array('747 de', 'A380 de', 'Seaplane de', 'Dive Bomber de'),
            );
            $airplanesFieldData = CustomFieldData::getByName('Airplanes');
            $airplanesFieldData->serializedData = serialize($values);
            $airplanesFieldData->save();

            $attributeForm = new DropDownAttributeForm();
            $attributeForm->attributeName       = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test DropDownAirplane',
                'en' => 'Test DropDownAirplane',
                'es' => 'Test DropDownAirplane',
                'fr' => 'Test DropDownAirplane',
                'it' => 'Test DropDownAirplane',
            );
            $attributeForm->isAudited             = true;
            $attributeForm->isRequired            = false;
            $attributeForm->defaultValueOrder     = 1;
            $attributeForm->customFieldDataData   = $values;
            $attributeForm->customFieldDataName   = 'Airplanes';
            $attributeForm->customFieldDataLabels = $labels;
            $validate = $attributeForm->validate();
            if ($validate == false)
            {
                throw new FailedToValidateException();
            }
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new $modelClassName());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                Yii::app()->end(0,false);
            }
        }


        public static function setIntegerAttribute($attributeName, $withDefaultData, $modelClassName)
        {
            assert('isset($attributeName) && $attributeName != null');
            assert('isset($withDefaultData) && is_bool($withDefaultData)');

            $attributeForm = new IntegerAttributeForm();
            $attributeForm->attributeName = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Integer',
                'en' => 'Test Integer',
                'es' => 'Test Integer',
                'fr' => 'Test Integer',
                'it' => 'Test Integer',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = false;
            $attributeForm->maxLength     = 11;
            $attributeForm->minValue      = -500;
            $attributeForm->maxValue      = 500;

            if ($withDefaultData)
            {
                $attributeForm->defaultValue  = 458;
            }
            $validate = $attributeForm->validate();

            if ($validate == false)
            {
                throw new FailedToValidateException();
            }
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new $modelClassName());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                Yii::app()->end(0,false);
            }
        }

        public static function setMultiSelectDropDownAttribute($attributeName, $withDefaultData, $modelClassName)
        {
            assert('isset($attributeName) && $attributeName != null');
            assert('isset($withDefaultData) && is_bool($withDefaultData)');

            $values = array(
                'Reading',
                'Writing',
                'Singing',
                'Surfing',
            );
            $labels = array('fr' => array('Reading fr', 'Writing fr', 'Singing fr', 'Surfing fr'),
                            'de' => array('Reading de', 'Writing de', 'Singing de', 'Surfing de'),
                            'en' => array('Reading en', 'Writing en', 'Singing en', 'Surfing en'),
            );
            $hobbiesFieldData = CustomFieldData::getByName('Hobbies');
            $hobbiesFieldData->serializedData = serialize($values);
            $hobbiesFieldData->save();

            $attributeForm = new MultiSelectDropDownAttributeForm();
            $attributeForm->attributeName    = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test MultiSelectDDHobbies',
                'en' => 'Test MultiSelectDDHobbies',
                'es' => 'Test MultiSelectDDHobbies',
                'fr' => 'Test MultiSelectDDHobbies',
                'it' => 'Test MultiSelectDDHobbies',
            );
            $attributeForm->isAudited             = true;
            $attributeForm->isRequired            = false;
            $attributeForm->customFieldDataData   = $values;
            $attributeForm->customFieldDataName   = 'Hobbies';
            $attributeForm->customFieldDataLabels = $labels;

            if ($withDefaultData)
            {
                $attributeForm->defaultValueOrder = 1;
            }
            else
            {
                $attributeForm->defaultValue      = null;
                $attributeForm->defaultValueOrder = null;
            }

            $validate = $attributeForm->validate();
            if ($validate == false)
            {
                throw new FailedToValidateException();
            }
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter                         = new $modelAttributesAdapterClassName(new $modelClassName());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                Yii::app()->end(0,false);
            }
        }

        public static function setTagCloudAttribute($attributeName, $withDefaultData, $modelClassName)
        {
            assert('isset($attributeName) && $attributeName != null');
            assert('isset($withDefaultData) && is_bool($withDefaultData)');
            $values = array(
                'English',
                'French',
                'Danish',
                'Spanish',
            );
            $labels = array('fr' => array('English fr', 'French fr', 'Danish fr', 'Spanish fr'),
                            'de' => array('English de', 'French de', 'Danish de', 'Spanish de'),
            );
            $languageFieldData = CustomFieldData::getByName('Languages');
            $languageFieldData->save();

            $attributeForm                   = new TagCloudAttributeForm();
            $attributeForm->attributeName    = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test TagCloudLanguages',
                'en' => 'Test TagCloudLanguages',
                'es' => 'Test TagCloudLanguages',
                'fr' => 'Test TagCloudLanguages',
                'it' => 'Test TagCloudLanguages',
            );
            $attributeForm->isAudited             = true;
            $attributeForm->isRequired            = false;
            $attributeForm->customFieldDataData   = $values;
            $attributeForm->customFieldDataName   = 'Languages';
            $attributeForm->customFieldDataLabels = $labels;

            if ($withDefaultData)
            {
                $attributeForm->defaultValueOrder = 1;
            }
            else
            {
                $attributeForm->defaultValueOrder = null;
            }

            $validate = $attributeForm->validate();
            if ($validate == false)
            {
                throw new FailedToValidateException();
            }
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new $modelClassName());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                Yii::app()->end(0,false);
            }
        }

       public static function setPhoneAttribute($attributeName, $withDefaultData, $modelClassName)
        {
            assert('isset($attributeName) && $attributeName != null');
            assert('isset($withDefaultData) && is_bool($withDefaultData)');

            $attributeForm = new PhoneAttributeForm();
            $attributeForm->attributeName = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Phone',
                'en' => 'Test Phone',
                'es' => 'Test Phone',
                'fr' => 'Test Phone',
                'it' => 'Test Phone',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = false;
            $attributeForm->maxLength     = 50;

            if ($withDefaultData)
            {
                $attributeForm->defaultValue  = '1-800-111-2233';
            }
            $validate = $attributeForm->validate();
            if ($validate == false)
            {
                throw new FailedToValidateException();
            }
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new $modelClassName());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                Yii::app()->end(0,false);
            }
        }

       public static function setRadioDropDownAttribute($attributeName, $withDefaultData, $modelClassName)
        {
            assert('isset($attributeName) && $attributeName != null');
            assert('isset($withDefaultData) && is_bool($withDefaultData)');

            $values = array(
                'Wing',
                'Nose',
                'Seat',
                'Wheel',
            );
            $airplanePartsFieldData = CustomFieldData::getByName('AirplaneParts');
            $airplanePartsFieldData->serializedData = serialize($values);
            $airplanePartsFieldData->save();
            $attributeForm = new RadioDropDownAttributeForm();
            $attributeForm->attributeName       = 'testAirPlaneParts';
            $attributeForm->attributeLabels  = array(
                'de' => 'Test RadioDDAirplane',
                'en' => 'Test RadioDDAirplane',
                'es' => 'Test RadioDDAirplane',
                'fr' => 'Test RadioDDAirplane',
                'it' => 'Test RadioDDAirplane',
            );
            $attributeForm->isAudited           = true;
            $attributeForm->isRequired          = false;
            $attributeForm->defaultValueOrder   = 3;
            $attributeForm->customFieldDataData = $values;
            $attributeForm->customFieldDataName = 'AirplaneParts';
            $validate = $attributeForm->validate();
            if ($validate == false)
            {
                throw new FailedToValidateException();
            }
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new $modelClassName());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                Yii::app()->end(0,false);
            }
        }


       public static function setTextAttribute($attributeName, $withDefaultData, $modelClassName)
        {
            assert('isset($attributeName) && $attributeName != null');
            assert('isset($withDefaultData) && is_bool($withDefaultData)');

            $attributeForm = new TextAttributeForm();
            $attributeForm->attributeName = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Text',
                'en' => 'Test Text',
                'es' => 'Test Text',
                'fr' => 'Test Text',
                'it' => 'Test Text',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = false;
            $attributeForm->maxLength     = 50;

            if ($withDefaultData)
            {
                $attributeForm->defaultValue  = 'Kangaroo';
            }
            $validate = $attributeForm->validate();
            if ($validate == false)
            {
                throw new FailedToValidateException();
            }
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new $modelClassName());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                Yii::app()->end(0,false);
            }
        }

       public static function setTextAreaAttribute($attributeName, $withDefaultData, $modelClassName)
        {
            assert('isset($attributeName) && $attributeName != null');
            assert('isset($withDefaultData) && is_bool($withDefaultData)');

            $attributeForm = new TextAreaAttributeForm();
            $attributeForm->attributeName = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Text Area',
                'en' => 'Test Text Area',
                'es' => 'Test Text Area',
                'fr' => 'Test Text Area',
                'it' => 'Test Text Area',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = false;

            if ($withDefaultData)
            {
                $attributeForm->defaultValue  = 'Kangaroo Pouch';
            }
            $validate = $attributeForm->validate();
            if ($validate == false)
            {
                throw new FailedToValidateException();
            }
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new $modelClassName());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                Yii::app()->end(0,false);
            }
        }

       public static function setUrlAttribute($attributeName, $withDefaultData, $modelClassName)
        {
            assert('isset($attributeName) && $attributeName != null');
            assert('isset($withDefaultData) && is_bool($withDefaultData)');

            $attributeForm = new UrlAttributeForm();
            $attributeForm->attributeName = $attributeName;
            $attributeForm->attributeLabels  = array(
                'de' => 'Test Url',
                'en' => 'Test Url',
                'es' => 'Test Url',
                'fr' => 'Test Url',
                'it' => 'Test Url',
            );
            $attributeForm->isAudited     = true;
            $attributeForm->isRequired    = false;
            $attributeForm->maxLength     = 50;

            if ($withDefaultData)
            {
                $attributeForm->defaultValue  = 'http://www.outback.com';
            }
            $validate = $attributeForm->validate();
            if ($validate == false)
            {
                throw new FailedToValidateException();
            }
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName(new $modelClassName());
            try
            {
                $adapter->setAttributeMetadataFromForm($attributeForm);
            }
            catch (FailedDatabaseSchemaChangeException $e)
            {
                echo $e->getMessage();
                Yii::app()->end(0,false);
            }
        }
    }

?>