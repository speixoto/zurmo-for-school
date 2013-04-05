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
 * REMOVE THIS CLASS once EmailTemplates module is merged in.
 */
class EmailTemplate extends OwnedSecurableItem
{
    public static function getByName($name)
    {
        return self::getByNameOrEquivalent('name', $name);
    }

    public static function getModuleClassName()
    {
        return 'WorkflowsModule';
    }

    public function __toString()
    {
        try
        {
            if (trim($this->name) == '')
            {
                return Yii::t('Default', '(Unnamed)');
            }
            return $this->name;
        }
        catch (AccessDeniedSecurityException $e)
        {
            return '';
        }
    }

    public static function canSaveMetadata()
    {
        return true;
    }

    public static function isTypeDeletable()
    {
        return true;
    }

    public static function getDefaultMetadata()
    {
        $metadata = parent::getDefaultMetadata();
        $metadata[__CLASS__] = array(
            'members' => array(
                'type',
                'name',
                'subject',
                'htmlContent',
                'textContent',
            ),
            'rules' => array(
                array('type',                 'required'),
                array('type',                 'type',    'type' => 'integer'),
                array('type',                 'length',  'min'  => 1),
                array('name',                 'required'),
                array('name',                 'type',    'type' => 'string'),
                array('name',                 'length',  'min'  => 3, 'max' => 64),
                array('subject',              'required'),
                array('subject',              'type',    'type' => 'string'),
                array('subject',              'length',  'min'  => 3, 'max' => 64),
                array('htmlContent',          'type',    'type' => 'string'),
                array('textContent',          'type',    'type' => 'string'),
            ),
            'elements' => array(
                'htmlContent'                  => 'TextArea',
                'textContent'                  => 'TextArea',
            ),
        );
        return $metadata;
    }
}
?>