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
     * The base View for a module's mass confirmation actions view.
     */
    abstract class MassConfirmView extends MassActionView
    {
        // TODO: @Shoaibi/@Jason: Low: This class should be named to a verbose one
        abstract protected function renderItemOperationType();

        abstract protected function renderSubmitButtonName();

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'CancelLink'),
                            array('type' => 'eval:$this->renderSubmitButtonName()',
                                  'htmlOptions' => array(
                                                         'params' => array(
                                                            'selectedRecordCount' => 'eval:$this->getSelectedRecordCount()'),

                                   ),
                            ),
                        ),
                    ),
                    'nonPlaceableAttributeNames' => array(
                        'name',
                    ),
                ),
            );
            return $metadata;
        }

        protected function getSelectedRecordCount()
        {
            return $this->selectedRecordCount;
        }

        protected function renderTitleContent()
        {
            return '<h1>' . $this->title . '</h1>';
        }

        protected function renderAlertMessage()
        {
            if (!empty($this->alertMessage))
            {
                return HtmlNotifyUtil::renderAlertBoxByMessage($this->alertMessage);
            }
        }

        protected function renderPreActionElementBar($form)
        {
            return null;
        }

        protected function renderOperationDescriptionContent()
        {
            $highlight      = $this->renderOperationHighlight();
            $message        = $this->renderOperationMessage();
            $description    = $highlight . $message;
            return ZurmoHtml::wrapLabel($description, 'operation-description');
        }

        protected function renderOperationHighlight()
        {
            $highlightOperation = substr($this->title, 0, strpos($this->title, ':'));
            $highlightMessage = $highlightOperation . ' is not reversable';
            return ZurmoHtml::tag('strong',
                                    array(),
                                    ZurmoHtml::tag('em',
                                                    array(),
                                                    Zurmo::t('Core', $highlightMessage)
                                                )
                                ) . ZurmoHtml::tag('br');
        }

        protected function renderOperationMessage()
        {
            $message  = $this->renderItemCount() .
                        $this->renderItemLabel() .
                        ' ' .
                        $this->renderOperationConfirmationMessage();
            return $message;
        }

        protected function renderOperationConfirmationMessage()
        {
            $confirmationMessage = 'selected for ' . $this->renderItemOperationType() . '.';
            return Zurmo::t('Core', $confirmationMessage);
        }

        protected function renderItemCount()
        {
            return ZurmoHtml::tag('strong', array(), $this->selectedRecordCount) . '&#160;';
        }

        protected function renderItemLabel()
        {
            // TODO: @Shoaibi/@Jason: High: Is this alright?
            return Zurmo::t('Core', $this->modelClassName . 'SingularLabel|' . $this->modelClassName . 'PluralLabel',
                array_merge(array($this->selectedRecordCount), LabelUtil::getTranslationParamsForAllModules()));
        }
    }
?>