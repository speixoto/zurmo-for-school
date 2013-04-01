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
     * View for displaying a row of recipient information for an email message
     */
    class EmailMessageRecipientRowForWorkflowComponentView extends View
    {
        const REMOVE_LINK_CLASS_NAME = 'remove-dynamic-email-message-recipient-row-link';
        /**
         * @var bool
         */
        public    $addWrapper = true;

        /**
         * @var WorkflowEmailMessageRecipientToElementAdapter
         */
        protected $elementAdapter;

        /**
         * @var int
         */
        protected $rowNumber;

        /**
         * @var array
         */
        protected $inputPrefixData;

        /**
         * @return string
         */
        public static function getFormId()
        {
            return WizardView::getFormId();
        }

        /**
         * @param WorkflowEmailMessageRecipientToElementAdapter $elementAdapter
         * @param integer $rowNumber
         * @param array $inputPrefixData
         */
        public function __construct($elementAdapter, $rowNumber, $inputPrefixData)
        {
            assert('$elementAdapter instanceof WorkflowEmailMessageRecipientToElementAdapter');
            assert('is_int($rowNumber)');
            assert('is_array($inputPrefixData)');
            $this->elementAdapter                     = $elementAdapter;
            $this->rowNumber                          = $rowNumber;
            $this->inputPrefixData                    = $inputPrefixData;
        }

        /**
         * @return string
         */
        public function render()
        {
            return $this->renderContent();
        }

        /**
         * @return string
         */
        protected function renderContent()
        {
            $content  = '<div>';
            $content .= $this->renderRecipientRowNumberLabel();
            $content .= $this->renderRecipientContent();
            $content .= '</div>';
            $content .= ZurmoHtml::link('—', '#', array('class' => self::REMOVE_LINK_CLASS_NAME));
            $content  = ZurmoHtml::tag('div', array('class' => "dynamic-sub-row dynamic-email-message-recipient-row"), $content);
            if($this->addWrapper)
            {
                return ZurmoHtml::tag('li', array(), $content);
            }
            return $content;
        }

        /**
         * @return string
         */
        protected function renderRecipientRowNumberLabel()
        {
            return ZurmoHtml::tag('span', array('class' => 'dynamic-row-number-label dynamic-email-message-recipient-row-number-label'),
                   ($this->rowNumber + 1) . '.');
        }

        /**
         * @return string
         */
        protected function renderRecipientContent()
        {
            $content = $this->elementAdapter->getContent();
            return $content;
        }
    }
?>