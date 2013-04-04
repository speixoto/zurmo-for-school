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

    class MarketingListDetailsOverlayView extends SecuredEditAndDetailsView
    {
        protected function renderContent()
        {
            // TODO: @Shoaibi/@Jason: High: An alternate to this was to use metadata and have separate item for each of the functions defined below.
            return $this->renderMemberStats() . ZurmoHtml::tag('hr') . $this->renderDescription() .
                        $this->renderAfterFormLayoutForDetailsContent();
        }

        protected function renderMemberStats()
        {
            $memberStats    = $this->renderSubscriberCount() .
                                $this->renderUnsubscriberCount() .
                                $this->renderInvalidEmailsCount();
            return ZurmoHtml::tag('div', array('class' => 'marketing-list-member-stats'), $memberStats);

        }

        protected function renderSubscriberCount()
        {
            return $this->renderMemberCountMessage(false);
        }

        protected function renderUnsubscriberCount()
        {
            return $this->renderMemberCountMessage(true);
        }

        protected function renderMemberCountMessage($unsubscribers = false)
        {
            $count          = MarketingListMember::getCountByMarketingListIdAndUnsubscribed($this->modelId, $unsubscribers);
            $divClass       = ($unsubscribers)? 'marketing-list-unsubscribers-stats' : 'marketing-list-subscribers-stats';
            $messageSuffix  = ($unsubscribers)? 'unsubscribers' : 'subscribers';
            $message        = Zurmo::t('MarketingListsModule', '{count} ' . $messageSuffix, array('{count}' => $count));
            return ZurmoHtml::tag('div', array('class' => $divClass), $message);
        }

        protected function renderInvalidEmailsCount()
        {
            $count          = 0; // TODO: @Shoaibi/@Jason: Critical: How do we do this?
            $message        = Zurmo::t('MarketingListsModule', '{count} invalid email address', array('{count}' => $count));
            return ZurmoHtml::tag('div', array('class' => 'marketing-list-invalid-email-stats'), $message);
        }

        protected function renderDescription()
        {
            return ZurmoHtml::tag('div', array('class' => 'marketing-list-description'), $this->model->description);
        }
    }
?>