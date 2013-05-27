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

    /**
     * View to when first coming to the marketing dashboard. Provides an overview of how marketing works
     */
    class MarketingDashboardIntroView extends View
    {
        protected function renderContent()
        {
            $content  = '<div class="clearfix">';
            $content .= '<h1>' . Zurmo::t('HomeModule', 'Welcome to Zurmo'). '</h1>';
            $content .= static::renderSocialLinksContent();
            $content .= '<div id="welcome-content">';
            $content .= '<p>';
            $content .= Zurmo::t('HomeModule', 'Using a CRM shouldn\'t be a chore. With Zurmo, you can earn points, ' .
                               'collect badges, and compete against co-workers while getting your job done.');
            $content .= '</p>';
            $content .= '</div>';
            $content .= $this->renderHideLinkContent();
            $content .= '</div>';
            $content .= '</div>';
            return $content;
        }


        protected function renderHideLinkContent()
        {
            if ($this->hasDashboardAccess)
            {
                $label    = '<span></span>' . Zurmo::t('HomeModule', 'Don\'t show me this screen again');
                $content  = '<div class="hide-welcome">'.ZurmoHtml::link($label, Yii::app()->createUrl('home/default/hideWelcome'));
                $content .= ' <i>(' . Zurmo::t('HomeModule', 'Don\'t worry you can turn it on again') . ')</i></div>';
                return $content;
            }
        }
    }
?>
