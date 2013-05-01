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

    class Product extends OwnedSecurableItem
    {
	const OPEN_STAGE	= 'Open';

        public static function getByName($name)
        {
            return self::getByNameOrEquivalent('name', $name);
        }

        protected function untranslatedAttributeLabels()
        {
            return array_merge(parent::untranslatedAttributeLabels(),
                array(
			'productTemplate' => 'Catalog Item',
			'contact'         => 'ContactsModuleSingularLabel',
			'account'         => 'AccountsModuleSingularLabel',
			'opportunity'     => 'OpportunitiesModuleSingularLabel',
		    )
            );
        }

        public function __toString()
        {
            try
            {
                if (trim($this->name) == '')
                {
                    return Zurmo::t('ProductsModule', '(Unnamed)');
                }
                return $this->name;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        public static function getModuleClassName()
        {
            return 'ProductsModule';
        }

        /**
         * Returns the display name for the model class.
         * @return dynamic label name based on module.
         */
        protected static function getLabel()
        {
            return 'ProductsModuleSingularLabel';
        }

        /**
         * Returns the display name for plural of the model class.
         * @return dynamic label name based on module.
         */
        protected static function getPluralLabel()
        {
            return 'ProductsModulePluralLabel';
        }

        public static function canSaveMetadata()
        {
            return true;
        }

	public static function translatedAttributeLabels($language)
        {
            $params = LabelUtil::getTranslationParamsForAllModules();
            return array_merge(parent::translatedAttributeLabels($language), array(
                'pricefrequency'    => Zurmo::t('ProductsModule', 'Price Frequency', $params, null, $language),
		'account'	    => Zurmo::t('AccountsModule', 'AccountsModuleSingularLabel', $params, null, $language),
                'contact'	    => Zurmo::t('ContactsModule', 'ContactsModuleSingularLabel', $params, null, $language),
                'opportunity'	    => Zurmo::t('OpportunitiesModule', 'OpportunitiesModuleSingularLabel', $params, null, $language),
                'productTemplate'   => Zurmo::t('ProductTemplatesModule', 'Catalog Item', $params, null, $language),
		'productCategories' => Zurmo::t('ProductTemplatesModule', 'Product Categories', array(), null, $language),
		'sellPrice'	    => Zurmo::t('ProductTemplatesModule', 'Sell Price', array(), null, $language),
		'stage'		    => Zurmo::t('ProductsModule', 'Stage', array(), null, $language)
                ));
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'name',
                    'description',
                    'quantity',
		    'pricefrequency',//In template it is priceFrequency which is not working here due to difference in type of item
                    'sellPrice',
                    'type'
                ),
                'relations' => array(
                    'account'			=> array(RedBeanModel::HAS_ONE, 'Account'),
                    'contact'			=> array(RedBeanModel::HAS_ONE, 'Contact'),
                    'opportunity'		=> array(RedBeanModel::HAS_ONE, 'Opportunity'),
                    'productTemplate'		=> array(RedBeanModel::HAS_ONE, 'ProductTemplate'),
                    'stage'			=> array(RedBeanModel::HAS_ONE, 'OwnedCustomField', RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'stage'),
		    'productCategories'         => array(RedBeanModel::MANY_MANY, 'ProductCategory'),
                    'sellPrice'                 => array(RedBeanModel::HAS_ONE,   'CurrencyValue',    RedBeanModel::OWNED,
                                                RedBeanModel::LINK_TYPE_SPECIFIC, 'sellPrice'),
                ),
                'rules' => array(
                    array('name',		'required'),
                    array('name',		'type',    'type' => 'string'),
                    array('name',		'length',  'min'  => 3, 'max' => 64),
                    array('description',	'type',    'type' => 'string'),
                    array('quantity',		'numerical',  'integerOnly' => true, 'allowEmpty' => false, 'min' => 1),
		    array('sellPrice',		'validatePrice', 'skipOnError' => false),
                    array('stage',		'required'),
		    array('quantity',		'required'),
		    array('type',		'type',    'type' => 'integer'),
                    array('pricefrequency',	'type',    'type' => 'integer'),
		    array('sellPrice',		'required'),
		    array('type',		'required'),
		    array('pricefrequency',	'required'),
                ),
                'elements' => array(
		    'account'	     => 'Account',
		    'contact'	     => 'Contact',
		    'description'    => 'TextArea',
		    'opportunity'    => 'Opportunity',
		    'pricefrequency' => 'ProductTemplatePriceFrequencyDropDown',
		    'productTemplate'=> 'ProductTemplate',
                    'sellPrice'      => 'CurrencyValue',
		    'type'           => 'ProductTemplateTypeDropDown',
                ),
                'customFields' => array(
                    'stage'    => 'ProductStages',
                ),
                'defaultSortAttribute' => 'name',
                'noAudit' => array(
                ),
		'nonConfigurableAttributes' => array('pricefrequency', 'type', 'productTemplate')
            );
            return $metadata;
        }

        public static function isTypeDeletable()
        {
            return true;
        }

        public static function hasReadPermissionsOptimization()
        {
            return true;
        }

        public static function getGamificationRulesType()
        {
            //return 'ProductGamification';
        }

	public function validatePrice($attribute, $params)
	{
	    if($this->{$attribute}->value < 0)
	    {
		$this->{$attribute}->addError('value', $this->getAttributeLabel($attribute) . Zurmo::t('ProductsModule',' should be greater than 0'));
	    }
	}
    }
?>