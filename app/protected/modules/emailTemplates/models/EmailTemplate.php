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

    class EmailTemplate extends OwnedSecurableItem
    {
        const TYPE_WORKFLOW = 1;

        const TYPE_CONTACT  = 2;

        public static function getByName($name)
        {
            return self::getByNameOrEquivalent('name', $name);
        }

        public static function getModuleClassName()
        {
            return 'EmailTemplatesModule';
        }

        public static function getTypeDropDownArray()
        {
             return array(
                 self::TYPE_WORKFLOW     => Zurmo::t('EmailTemplatesModule', 'Workflow'),
                 self::TYPE_CONTACT      => Zurmo::t('EmailTemplatesModule', 'Contact'),
             );
        }

        public static function renderNonEditableTypeStringContent($type)
        {
            assert('is_int($type) || $type == null');
            $dropDownArray = self::getTypeDropDownArray();
            if (!empty($dropDownArray[$type]))
            {
                return Yii::app()->format->text($dropDownArray[$type]);
            }
        }

        public function __toString()
        {
            try
            {
                if (trim($this->name) == '')
                {
                    return Zurmo::t('Default', '(Unnamed)');
                }
                return $this->name;
            }
            catch (AccessDeniedSecurityException $e)
            {
                return '';
            }
        }

        /**
         * Returns the display name for plural of the model class.
         * @return dynamic label name based on module.
         */
     /*   protected static function getPluralLabel()
        {
            return 'EmailTemplatesModulePluralLabel';
        }
*/
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
                    'modelClassName',
                    'name',
                    'subject',
                    'language',
                    'htmlContent',
                    'textContent',
                ),
                'rules' => array(
                    array('type',                       'required'),
                    array('type',                       'type',    'type' => 'integer'),
                    array('type',                       'numerical', 'min' => self::TYPE_WORKFLOW,
                                                                'max' => self::TYPE_CONTACT),
                    array('modelClassName',             'required'),
                    array('modelClassName',             'type',     'type' => 'string'),
                    array('modelClassName',             'length', 'max' => 64),
                    array('modelClassName',             'validateModelExists'),
                    array('name',                       'required'),
                    array('name',                       'type',    'type' => 'string'),
                    array('name',                       'length',  'min'  => 3, 'max' => 64),
                    array('subject',                    'required'),
                    array('subject',                    'type',    'type' => 'string'),
                    array('subject',                    'length',  'min'  => 3, 'max' => 64),
                    array('language',                   'type',    'type' => 'string'),
                    array('language',                   'length',  'min' => 2, 'max' => 2),
                    array('language',                   'setToUserDefaultLanguage'),
                    array('htmlContent',                'type',    'type' => 'string'),
                    array('textContent',                'type',    'type' => 'string'),
                    array('htmlContent, textContent',   'validateHtmlContentAndTextContent'),
                    array('htmlContent, textContent',   'validateMergeTags'),
                ),
                'elements' => array(
                    'htmlContent'                  => 'TextArea',
                    'textContent'                  => 'TextArea',
                ),
            );
            return $metadata;
        }

        public function validateModelExists($attribute, $params)
        {
            if (@class_exists($this->$attribute))
            {
                if (is_subclass_of($this->$attribute, 'RedBeanModel'))
                {
                }
                else
                {
                    $this->addError($attribute, Zurmo::t('EmailTemplatesModule', 'Provided class name is not a valid Model class.'));
                }
            }
            else
            {
                // TODO: @Shoaibi/@Jason This check messes up schema build operations.
                /**
                 * Details:
                when i do updateSchema it prints:


                Error - *** Saving the sample EmailTemplate failed.
                Error - The attributes that did not validate probably need more rules, or are not deletable types.
                Error - Array
                (
                [modelClassName] => Array
                (
                [0] => Provided class name does not exist.
                )

                )

                Info - Auto built EmailTemplate saved.
                ... clipped ...
                Info - EmailTemplate Not Deleted but never saved so this is ok. (Most likely it is a - Has Many Owned)



                Doing a CVarDumper  inside validator reveals that "$this->modelClassName" is random each time, some sample values collected are: X, L, RPT, LWQ
                Needless to say emailtemplate table is never generated.


                We could exclude the last "else" as in real life the value for this attribute would be coming from a DDL but in that case automated POST or API operations could contain invalid class names that do not exist. So i would prefer to go down that route.
                An alternate could be to not do the else check if running on cli but that would disable this check in unit and walkthrough test and i wouldn't want that.
                 */
                //$this->addError($attribute, Zurmo::t('EmailTemplatesModule', 'Provided class name does not exist.'));
            }
        }

        public function validateHtmlContentAndTextContent($attribute, $params)
        {
            if (empty($this->textContent) && empty($this->htmlContent))
            {
                $this->addError($attribute, Zurmo::t('EmailTemplatesModule', 'Please provide at least one of the contents field.'));
            }
            else
            {
            }
        }

        public function setToUserDefaultLanguage($attribute, $params)
        {
            if (empty($this->$attribute))
            {
                $this->$attribute = Yii::app()->user->userModel->language;
            }
            else
            {
            }
        }

        public function validateMergeTags($attribute, $params)
        {

            if (!empty($this->$attribute) && @class_exists($this->modelClassName))
            {
                $model          = new $this->modelClassName(false);
                $mergeTagsUtil  = MergeTagsUtilFactory::make($this->type, $this->language, $this->$attribute);
                $invalidTags    = array();
                if (!$mergeTagsUtil->extractMergeTagsPlaceHolders() ||
                                    $mergeTagsUtil->resolveMergeTagsArrayToAttributes($model, $invalidTags, null))
                {
                }
                else
                {
                    $errorMessage = Zurmo::t('EmailTemplatesModule', 'Provided content contains few invalid merge tags');
                    if (!empty($invalidTags))
                    {
                        $errorMessage .= ':';
                        // TODO: @Shoaibi/@Jason This is TABOO!!!! View related logic in models.
                        $errorMessage .= "<ul id='${attribute}-invalid-merge-tags' class='invalid-merge-tags'>";
                        foreach ($invalidTags as $tag)
                        {
                            // TODO: @Shoaibi/@Amit This needs to be improved.
                            $errorMessage .= "<li>${tag}</li>";
                        }
                        $errorMessage .= "</ul>";
                    }
                    else
                    {
                        $errorMessage .= '.';
                    }
                    // TODO: @Shoaibi/@Amit/@Jason Error message against textContent from here is hidden under next div(htmlContent starting div).
                    $this->addError($attribute, $errorMessage);
                }
            }
            else
            {
            }
        }
    }
?>
