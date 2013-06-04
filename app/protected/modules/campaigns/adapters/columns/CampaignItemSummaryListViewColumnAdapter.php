<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class CampaignItemSummaryListViewColumnAdapter extends TextListViewColumnAdapter
    {
        public function renderGridViewData()
        {
            $className  = get_class($this);
            $value      = $className . '::resolveContactAndMetricsSummary($data)';
            return array(
                'value' => $value,
                'type'  => 'raw',
            );
        }

        public static function resolveContactAndMetricsSummary(CampaignItem $campaignItem)
        {
            $content  = static::resolveContactWithLink($campaignItem->contact);
            $content .= static::renderMetricsContent($campaignItem);
            return $content;
        }

        public static function resolveContactWithLink(Contact $contact)
        {
            $moduleClassName = static::resolveModuleClassName($contact);
            $linkRoute       = Yii::app()->createUrl('/' . $moduleClassName::getDirectoryName() . '/default/details',
                                                     array('id' => $contact->id));
            $linkContent     = ActionSecurityUtil::resolveLinkToModelForCurrentUser(strval($contact), $contact,
                               $moduleClassName, $linkRoute);
            if($linkContent == null){
                $title       = Zurmo::t('CampaignsModule', 'This recipient is restricted, you do not have access to see it.');
                $tooltip     = ZurmoHtml::tag('span', array('class' => 'tooltip', 'title' => $title), '?');
                $linkContent = ZurmoHtml::tag('em', array(), Zurmo::t('CampaignsModule', 'Restricted')) . $tooltip;
            }
            //todo: @amit if null - need to show 'restricted' in maybe a pill type background color. with a question mark
            //or on hover (without question that shows the tooltip to explain why it is restricted)
            //todo: @jason - not sure the wording and on the ::t category, also please render qtip jquery here
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-name'), $linkContent);
        }

        protected static function resolveModuleClassName(Contact $contact)
        {
            if (LeadsUtil::isStateALead($contact->state))
            {
                return 'LeadsModule';
            }
            else
            {
                return $contact->getModuleClassName();
            }
        }

        protected static function renderMetricsContent(CampaignItem $campaignItem)
        {
            $isQueued     = $campaignItem->isQueued();
            if(!$isQueued)
            {
                $isSent           = $campaignItem->isSent(); //we need to show them if its a continum
                $failedToSend     = $campaignItem->hasFailedToSend();//we need to show them if its a continum
                $hasOpened        = $campaignItem->hasAtLeastOneOpenActivity();
                $hasClicked       = $campaignItem->hasAtLeastOneClickActivity();
                $hasUnsubscribed  = $campaignItem->hasAtLeastOneUnsubscribeActivity();
                $hasBounced       = $campaignItem->hasAtLeastOneBounceActivity();
            }

            $content = '<div class="continuum">
                            <div class="clearfix">
                                <div class="email-recipient-stage-status queued"><i>&#9679;</i><span>Queued</span></div>
                            </div>
                       </div>

                       <div class="continuum">
                            <div class="clearfix">
                                <div class="email-recipient-stage-status queued stage-true"><i>&#9679;</i><span>Sent</span></div>
                            </div>
                       </div>

                       <div class="continuum">
                            <div class="clearfix">
                                <div class="email-recipient-stage-status queued stage-true"><i>&#9679;</i><span>Sent</span></div>
                                <div class="email-recipient-stage-status queued stage-false"><i>&#9679;</i><span>Bounced</span></div>
                            </div>
                       </div>

                       <div class="continuum">
                            <div class="clearfix">
                                <div class="email-recipient-stage-status queued stage-false"><i>&#9679;</i><span>Sent Failed</span></div>
                            </div>
                       </div>

                       <div class="continuum">
                            <div class="clearfix">
                                <div class="email-recipient-stage-status queued stage-true"><i>&#9679;</i><span>Sent</span></div>
                                <div class="email-recipient-stage-status stage-true"><i>&#9679;</i><span>Opened</span></div>
                                <div class="email-recipient-stage-status stage-true"><i>&#9679;</i><span>Clicked</span></div>
                                <div class="email-recipient-stage-status stage-false"><i>&#9679;</i><span>Unsubscribed</span></div>
                            </div>
                       </div>
                        ';

            return $content;
        }
    }
?>