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
     * View to display to users upon login.  Shows informatin such as tips, helpful links and ideas of what to do.
     */
    class WelcomeView extends View
    {
        protected $tipContent;

        protected $splashImageName;

        protected $hasDashboardAccess;

        protected static function renderHelpfulLinksContent()
        {
            $content  = '<div>';
            $content .= '<h3>' . Yii::t('Default', 'Helpful Links') . '</h3>';
            $content .= '<ul>';
            $content .= '<li>' . CHtml::link(Yii::t('Default', 'Join the forum'), 'http://www.zurmo.org/forums') . '</li>';
            $content .= '<li>' . CHtml::link(Yii::t('Default', 'Read the wiki'),  'http://zurmo.org/wiki') . '</li>';
            $content .= '<li>' . CHtml::link(Yii::t('Default', 'View a tutorial'), 'http://www.zurmo.org/tutorials') . '</li>';
            $content .= '<li>' . CHtml::link(Yii::t('Default', 'Watch a video'), 'http://zurmo.org/screencasts') . '</li>';
            $content .= '</ul>';
            $content .= '</div>';
            return $content;
        }

        protected static function renderSocialLinksContent()
        {
            return AboutView::renderSocialLinksContent();
        }

        public function __construct($tipContent, $splashImageName, $hasDashboardAccess)
        {
            assert('is_string($tipContent)');
            assert('is_array($recommendedActivitiesData)');
            assert('is_string($splashImageName)');
            assert('is_bool($hasDashboardAccess)');
            $this->tipContent                  = $tipContent;
            $this->splashImageName           = $splashImageName;
            $this->hasDashboardAccess        = $hasDashboardAccess;
        }

        protected function renderContent()
        {
            $content  = '<div>';
            $content .= '<h1>Zurmo Open Source CRM</span></h1>';
            $content .= '<div id="leftCol">';
            $content .= static::renderHelpfulLinksContent();
            $content .= $this->renderTipsContent();
            $content .= static::renderSocialLinksContent();
            $content .= '</div>';
            $content .= '<div id="rightCol">';
            $content .= $this->renderDashboardLinkContent();
            $content .= $this->renderSplashImageContent();
            $content .= $this->renderHideLinkContent();
            $content .= '</div>';
            $content .= '</div>';
            return $content;
        }

        protected function renderTipsContent()
        {
            if($this->tipContent != null)
            {
                $content  = '<div>';
                $content .= '<h3>' . Yii::t('Default', 'Tip of the Day') . '</h3>';
                $content .= '<ul>';
                $content .= '<li>' . $this->tipContent . '</li>';
                $content .= '</ul>';
                $content .= '</div>';
                return $content;
            }
        }

        protected function renderDashboardLinkContent()
        {
            if($this->hasDashboardAccess)
            {
                $label    = Yii::t('Default', 'Go to the dashboard');
                $content  = CHtml::link($label, Yii::app()->createUrl('home/default'));
                return $content;
            }
        }

        protected function renderSplashImageContent()
        {
            return 'todo: show image using Chtml::image' . $this->splashImageName;
        }

        protected function renderHideLinkContent()
        {
            if($this->hasDashboardAccess)
            {
                $label    = Yii::t('Default', 'Don\'t show me this screen again');
                $content  = CHtml::link($label, Yii::app()->createUrl('home/default/hideWelcome'));
                $content .= '<br/><i>(' . Yii::t('Default', 'Don\'t worry you can turn it on again') . ')</i>';
                return $content;
            }
        }
    }
?>
