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
     * Currency Value Price Validator to validate the value part in Currency Value Model
     */
    class CurrencyValuePriceValidator extends CValidator
    {
	/**
	 * @var boolean whether the attribute value can be zero. Defaults to false,
	 * meaning that if the attribute is less than or equal to zero, it is considered invalid.
	 */
	public $allowZero=true;

	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty=true;

        /**
         * Override existing method
         * @param RedBeanModel $model the model being validated
         * @param string $attribute the attribute being validated
         */
        protected function validateAttribute($object, $attribute)
        {
	    assert('$object instanceof RedBeanModel');
	    $value = $object->$attribute->value;
	    if($this->allowEmpty === false)
	    {
		if($this->allowZero === false)
		{
		    if($value <= 0)
		    {
			$message = $object->getAttributeLabel($attribute) . Zurmo::t('Core',' should be > 0');
			$this->addError($object, $attribute, $message);
		    }
		}
		else
		{
		    if($value < 0)
		    {
			$message = $object->getAttributeLabel($attribute) . Zurmo::t('Core',' should be >= 0');
			$this->addError($object, $attribute, $message);
		    }
		}
	    }
        }
    }
?>