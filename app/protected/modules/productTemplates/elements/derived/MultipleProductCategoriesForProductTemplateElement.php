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
     * User interface element for managing related model relations for activities. This class supports a HAS_MANY
     * specifically for the 'contact' relation. This is utilized by the meeting model.
     *
     */
    class MultipleProductCategoriesForProductTemplateElement extends Element implements DerivedElementInterface
    {
        protected function renderControlNonEditable()
        {
            $content  = null;
            $contacts = $this->getExistingCategoriesRelationsIdsAndLabels();
            foreach ($contacts as $contactData)
            {
                if ($content != null)
                {
                    $content .= ', ';
                }
                $content .= $contactData['name'];
            }
            return $content;
        }

        protected function renderControlEditable()
        {
            assert('$this->model instanceof Activity');
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ModelElement");
            $cClipWidget->widget('application.core.widgets.MultiSelectAutoComplete', array(
                'name'        => $this->getNameForIdField(),
                'id'          => $this->getIdForIdField(),
                'jsonEncodedIdsAndLabels'   => '', //'CJSON::encode($this->getExistingCategoriesRelationsIdsAndLabels()),
                'sourceUrl'   => Yii::app()->createUrl('producttemplates/default/autoCompleteAllProductCategoriesForMultiSelectAutoComplete'),
                'htmlOptions' => array(
                    'disabled' => $this->getDisabledValue(),
                    ),
                'hintText' => Zurmo::t('MeetingsModule', 'Type a ContactsModuleSingularLowerCaseLabel ' .
                                                'or LeadsModuleSingularLowerCaseLabel: name or email address',
                                LabelUtil::getTranslationParamsForAllModules())
            ));
            $cClipWidget->endClip();
            $content = $cClipWidget->getController()->clips['ModelElement'];
            return $content;
        }

        protected function renderError()
        {
        }

        protected function renderLabel()
        {
            return $this->resolveNonActiveFormFormattedLabel($this->getFormattedAttributeLabel());
        }

        protected function getFormattedAttributeLabel()
        {
            return Yii::app()->format->text(Zurmo::t('MeetingsModule', 'Categories'));
        }

         public static function getDisplayName()
        {
            return Zurmo::t('MeetingsModule', 'Related ContactsModulePluralLabel and LeadsModulePluralLabel',
                       LabelUtil::getTranslationParamsForAllModules());
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element. For this element, there are no attributes from the model.
         * @return array - empty
         */
        public static function getModelAttributeNames()
        {
            return array();
        }

        protected function getNameForIdField()
        {
                return 'ActivityItemForm[Contact][ids]';
        }

        protected function getIdForIdField()
        {
            return 'ActivityItemForm_Contact_ids';
        }

        protected function getExistingCategoriesRelationsIdsAndLabels()
        {
            $existingCategories = array();
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('Category');
            foreach ($this->model->activityItems as $item)
            {
                try
                {
                    $contact = $item->castDown(array($modelDerivationPathToItem));
                    if (get_class($contact) == 'Contact')
                    {
                        $existingContacts[] = array('id' => $contact->id,
                                                    'name' => self::renderHtmlContentLabelFromContactAndKeyword($contact, null));
                    }
                }
                catch (NotFoundException $e)
                {
                    //do nothing
                }
            }
            return $existingContacts;
        }

        public static function renderHtmlContentLabelFromContactAndKeyword($contact, $keyword)
        {
            assert('$contact instanceof Contact && $contact->id > 0');
            assert('$keyword == null || is_string($keyword)');

            if (substr($contact->secondaryEmail->emailAddress, 0, strlen($keyword)) === $keyword)
            {
                $emailAddressToUse = $contact->secondaryEmail->emailAddress;
            }
            else
            {
                $emailAddressToUse = $contact->primaryEmail->emailAddress;
            }
            if ($emailAddressToUse != null)
            {
                return strval($contact) . '&#160&#160<b>' . strval($emailAddressToUse) . '</b>';
            }
            else
            {
                return strval($contact);
            }
        }
    }
?>