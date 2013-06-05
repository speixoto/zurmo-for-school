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
            $linkRoute       = '/' . $moduleClassName::getDirectoryName() . '/default/details';
            $linkContent     = ActionSecurityUtil::resolveLinkToModelForCurrentUser(strval($contact), $contact,
                               $moduleClassName, $linkRoute);
            if($linkContent == null)
            {
                $linkContent = static::renderRestrictedContactAccessLink($contact);
            }
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-name'), $linkContent);
        }

        protected static function renderRestrictedContactAccessLink(Contact $contact)
        {
            $title       = Zurmo::t('CampaignsModule', 'You cannot see this contact due to limited access');
            $content     = ZurmoHtml::tag('em', array(), Zurmo::t('CampaignsModule', 'Restricted'));
            $content    .= ZurmoHtml::tag('span', array('id'    => 'restricted-access-contact-tooltip' . $contact->id,
                                                        'class' => 'tooltip',
                                                        'title' => $title), '?');
            $qtip = new ZurmoTip(array('options' => array('position' => array('my' => 'bottom right', 'at' => 'top left'))));
            $qtip->addQTip('restricted-access-contact-tooltip' . $contact->id);
            return $content;
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
            $isQueuedOrSkipped     = $campaignItem->isQueuedOrSkipped();
            $isSkipped             = $campaignItem->isSkipped();
            if($isQueuedOrSkipped && !$isSkipped)
            {
                $content = static::getQueuedContent();
            }
            elseif($isQueuedOrSkipped && $isSkipped)
            {
                $content = static::getSkippedContent();
            }
            elseif($campaignItem->hasFailedToSend())
            {
                $content = static::getSendFailedContent();
            }
            else //sent
            {
                $content = static::getSentContent();
                if($campaignItem->hasAtLeastOneOpenActivity())
                {
                    $content .= static::getOpenedContent();
                }
                if($campaignItem->hasAtLeastOneClickActivity())
                {
                    $content .= static::getClickedContent();
                }
                if($campaignItem->hasAtLeastOneUnsubscribeActivity())
                {
                    $content .= static::getUnsubscribedContent();
                }
                if($campaignItem->hasAtLeastOneBounceActivity())
                {
                    $content .= static::getBouncedContent();
                }
            }
            $clearFixContent = ZurmoHtml::tag('div', array('class' => 'clearfix'), $content);
            return ZurmoHtml::tag('div', array('class' => 'continuum'), $clearFixContent);
        }

        protected static function getQueuedContent()
        {
            $content = '<i>&#9679;</i><span>' . Zurmo::t('MarketingModule', 'Queued') . '</span>';
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-stage-status queued'), $content);
        }

        protected static function getSkippedContent()
        {
            $content = '<i>&#9679;</i><span>' . Zurmo::t('MarketingModule', 'Skipped') . '</span>';
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-stage-status stage-false'), $content);
        }

        protected static function getSentContent()
        {
            $content = '<i>&#9679;</i><span>' . Zurmo::t('MarketingModule', 'Sent') . '</span>';
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-stage-status stage-true'), $content);
        }

        protected static function getSendFailedContent()
        {
            $content = '<i>&#9679;</i><span>' . Zurmo::t('MarketingModule', 'Send Failed') . '</span>';
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-stage-status stage-false'), $content);
        }

        protected static function getOpenedContent()
        {
            $content = '<i>&#9679;</i><span>' . Zurmo::t('MarketingModule', 'Opened') . '</span>';
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-stage-status stage-true'), $content);
        }

        protected static function getClickedContent()
        {
            $content = '<i>&#9679;</i><span>' . Zurmo::t('MarketingModule', 'Clicked') . '</span>';
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-stage-status stage-true'), $content);
        }

        protected static function getUnsubscribedContent()
        {
            $content = '<i>&#9679;</i><span>' . Zurmo::t('MarketingModule', 'Unsubscribed') . '</span>';
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-stage-status stage-false'), $content);
        }

        protected static function getBouncedContent()
        {
            $content = '<i>&#9679;</i><span>' . Zurmo::t('MarketingModule', 'Bounced') . '</span>';
            return ZurmoHtml::tag('div', array('class' => 'email-recipient-stage-status stage-false'), $content);
        }
    }
?>