<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2011 Zurmo Inc.
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
     * Helper class for constructing the default view used by the classes that extend the ZurmoPageView.
     */
    class ZurmoDefaultViewUtil
    {

        /**
         * Given a controller and contained view, construct the gridview
         * used by the zurmo page view.
         * @param CController $controller
         * @param View $containedView
         */
        public static function makeStandardViewForCurrentUser(CController $controller, View $containedView)
        {
            $horizontalGridView = new GridView(1, 1);
            $horizontalGridView->setView($containedView, 0, 0);
            $verticalGridView   = new GridView(6, 1);
            $verticalGridView->setView(static::makeHeaderView(),                    0, 0);
            $verticalGridView->setView(static::makeMenuView(),                      1, 0);
            $verticalGridView->setView(static::makeFlashMessageView($controller),   2, 0);
            $verticalGridView->setView($horizontalGridView,                         3, 0);
            $verticalGridView->setView(static::makeModalContainerView(),            4, 0);
            $verticalGridView->setView(static::makeFooterView(),                    5, 0);
            return $verticalGridView;
        }

        /**
         * Given a contained view, construct the gridview
         * used by the zurmo page view for errors.
         * @param View $containedView
         */
        public static function makeErrorViewForCurrentUser(View $containedView)
        {
            $horizontalGridView = new GridView(1, 1);
            $horizontalGridView->setView($containedView, 0, 0);
            $verticalGridView   = new GridView(4, 1);
            $verticalGridView->setView(static::makeHeaderView(),                    0, 0);
            $verticalGridView->setView(static::makeMenuView(),                      1, 0);
            $verticalGridView->setView($horizontalGridView,                         3, 0);
            $verticalGridView->setView(static::makeFooterView(),                    5, 0);
            return $verticalGridView;
        }

        protected static function makeHeaderView()
        {
            $menuMetadata         = MenuUtil::getAccessibleHeaderMenuByCurrentUser();
            $notificationsUrl     = Yii::app()->createUrl('notifications/default');
            $moduleNamesAndLabels = GlobalSearchUtil::
                                    getGlobalSearchScopingModuleNamesAndLabelsDataByUser(Yii::app()->user->userModel);
            $sourceUrl            = Yii::app()->createUrl('zurmo/default/globalSearchAutoComplete');
            GlobalSearchUtil::resolveModuleNamesAndLabelsDataWithAllOption($moduleNamesAndLabels);
            return new HeaderView($menuMetadata, $notificationsUrl, $moduleNamesAndLabels, $sourceUrl);
        }

        protected static function makeMenuView()
        {
            $items = MenuUtil::resolveByCacheAndGetVisibleAndOrderedTabMenuByCurrentUser();
            return new MenuView($items);
        }

        protected static function makeFlashMessageView(CController $controller)
        {
            return new FlashMessageView($controller);
        }

        protected static function makeModalContainerView()
        {
            return new ModalContainerView();
        }

        protected static function makeFooterView()
        {
            return new FooterView();
        }
    }
?>