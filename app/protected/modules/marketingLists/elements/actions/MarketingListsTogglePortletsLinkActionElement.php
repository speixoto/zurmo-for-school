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
     * Class to render link to toggle portlets for a report grid view
     */
    class MarketingListsTogglePortletsLinkActionElement extends LinkActionElement
    {
        /**
         * @return null
         */
        public function getActionType()
        {
            return null;
        }

        /**
         * @return string
         */
        public function render()
        {
            $content  = null;
            $membersClass = $this->getMembersPortletClass();
            $autorespondersClass = $this->getAutorespondersPortletClass();
            if ($membersClass)
            {
                $htmlOptions = array('onClick' => 'js:$(".' . $membersClass . '").toggle();');
                $label       = ZurmoHtml::label(Zurmo::t('MarketingListsModule', 'Members'), Zurmo::t('MarketingListsModule', 'Members'), array('class' => 'label-for-marketing-list-widgets'));
                $content    .= ZurmoHtml::checkBox(Zurmo::t('MarketingListsModule', 'Members'), true, $htmlOptions) . $label;
            }
            if($autorespondersClass)
            {
                $htmlOptions = array('onClick' => 'js:$(".' . $autorespondersClass . '").toggle();');
                $label       = ZurmoHtml::label(Zurmo::t('MarketingListsModule', 'Autoresponders'), Zurmo::t('MarketingListsModule', 'Autoresponders'), array('class' => 'label-for-marketing-list-widgets'));
                $content    .= ZurmoHtml::checkBox(Zurmo::t('MarketingListsModule', 'Autoresponders'), true, $htmlOptions) . $label;
            }
            return ZurmoHtml::tag('div', $this->getHtmlOptions(), $content );
        }

        /**
         * @return string
         */
        protected function getDefaultLabel()
        {
            return Zurmo::t('MarketingListsModule', 'Toggle View');
        }

        /**
         * @return null
         */
        protected function getDefaultRoute()
        {
            return null;
        }


        protected function getMembersPortletClass()
        {
            return ArrayUtil::getArrayValueWithExceptionIfNotFound($this->params, 'membersPortletClass');
        }

        protected function getAutorespondersPortletClass()
        {
            return ArrayUtil::getArrayValueWithExceptionIfNotFound($this->params, 'autorespondersPortletClass');
        }
    }
?>