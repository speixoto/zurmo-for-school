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
     * Helper class for constructing the default view used by the classes that extend the ZurmoPageView.
     */
    class ZurmoDefaultViewUtil
    {
        protected static $showRecentlyViewed = true;

        /**
         * Given a controller, contained view, construct the gridview
         * used by the designer page view.
         * @param CController $controller
         * @param View $containedView
         * @param mixed $activeNodeModuleClassName (null or string)
         */
        public static function makeViewWithBreadcrumbsForCurrentUser(CController $controller,
                                                                     View $containedView,
                                                                     $breadcrumbLinks,
                                                                     $breadcrumbViewClassName,
                                                                     $cssClasses = array())
        {
            assert('is_array($breadcrumbLinks)');
            $gridView    = new GridView(2, 1);
            $gridView->setCssClasses($cssClasses);
            $gridView->setView(new $breadcrumbViewClassName($controller->getId(),
                                                            $controller->getModule()->getId(),
                                                            $breadcrumbLinks), 0, 0);
            $gridView->setView($containedView, 1, 0);
            return static::makeStandardViewForCurrentUser($controller, $gridView);
        }

        /**
         * Given a controller and contained view, construct the gridview
         * used by the zurmo page view.
         * @param CController $controller
         * @param View $containedView
         */
        public static function makeStandardViewForCurrentUser(CController $controller, View $containedView)
        {
            if (static::$showRecentlyViewed)
            {
                $verticalColumns = 2;
            }
            else
            {
                $verticalColumns = 1;
            }
            $aVerticalGridView   = new GridView($verticalColumns, 1);

            $aVerticalGridView->setCssClasses( array('AppNavigation', 'clearfix')); //navigation left column
            $aVerticalGridView->setView(static::makeMenuView($controller), 0, 0);
            if (static::$showRecentlyViewed)
            {
                $aVerticalGridView->setView(static::makeRecentlyViewedView(), 1, 0);
            }

            $horizontalGridView = new GridView(1, 3);
            $horizontalGridView->setCssClasses(array('AppContainer', 'clearfix')); //teh conatiner for the floated items
            $horizontalGridView->setView($aVerticalGridView, 0, 0);

            $containedView->setCssClasses(array_merge($containedView->getCssClasses(), array('AppContent'))); //the app itself to the right

            $horizontalGridView->setView($containedView, 0, 1);
            $horizontalGridView->setView(static::makeFlashMessageView($controller),   0, 2); //TODO needs to move into $cotainedView

            $verticalGridView   = new GridView(5, 1);
            $verticalGridView->setView(static::makeHeaderView($controller),                 0, 0);
            $verticalGridView->setView($horizontalGridView,                                 1, 0);
            $verticalGridView->setView(static::makeModalContainerView(),                    2, 0);
            $verticalGridView->setView(static::makeModalGameNotificationContainerView(),    3, 0);
            $verticalGridView->setView(static::makeFooterView(),                            4, 0);

            return $verticalGridView;
        }

        /**
         * Given a contained view, construct the gridview
         * used by the zurmo page view for errors.
         * @param View $containedView
         */
        public static function makeErrorViewForCurrentUser(CController $controller, View $containedView)
        {
            $aVerticalGridView   = new GridView(1, 1);
            $aVerticalGridView->setCssClasses( array('AppNavigation', 'clearfix')); //navigation left column
            $aVerticalGridView->setView(static::makeMenuView($controller), 0, 0);

            $horizontalGridView = new GridView(2, 1);
            $horizontalGridView->setCssClasses(array('AppContainer', 'clearfix'));
            $horizontalGridView->setView($aVerticalGridView, 0, 0);
            $containedView->setCssClasses(array_merge($containedView->getCssClasses(), array('AppContent', 'ErrorView'))); //the app itself to the right
            $horizontalGridView->setView($containedView, 1, 0);

            $verticalGridView   = new GridView(3, 1);
            $verticalGridView->setView(static::makeHeaderView($controller),         0, 0);
            $verticalGridView->setView($horizontalGridView,                         1, 0);
            $verticalGridView->setView(static::makeFooterView(),                    2, 0);
            return $verticalGridView;
        }

        protected static function makeHeaderView(CController $controller)
        {
            $settingsMenuItems        = MenuUtil::getOrderedAccessibleHeaderMenuForCurrentUser();
            $userMenuItems            = static::getAndResolveUserMenuItemsForHeader();
            $shortcutsCreateMenuItems = MenuUtil::getAccessibleShortcutsCreateMenuByCurrentUser();
            $notificationsUrl         = Yii::app()->createUrl('notifications/default/recentNotifcations');
            $moduleNamesAndLabels     = GlobalSearchUtil::
                                        getGlobalSearchScopingModuleNamesAndLabelsDataByUser(Yii::app()->user->userModel);
            $sourceUrl                = Yii::app()->createUrl('zurmo/default/globalSearchAutoComplete');
            GlobalSearchUtil::resolveModuleNamesAndLabelsDataWithAllOption(
                                        $moduleNamesAndLabels);
            $applicationName          = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'applicationName');
            return new HeaderView($controller->getId(), $controller->getModule()->getId(), $settingsMenuItems,
                                  $userMenuItems, $shortcutsCreateMenuItems, $notificationsUrl,
                                  $moduleNamesAndLabels, $sourceUrl, $applicationName);
        }

        protected static function getAndResolveUserMenuItemsForHeader()
        {
            $userMenuItems             = MenuUtil::getAccessibleOrderedUserHeaderMenuForCurrentUser();
            return $userMenuItems;
        }

        protected static function makeMenuView($controller = null)
        {
            assert('$controller == null || $controller instanceof CController');
            $items = MenuUtil::resolveByCacheAndGetVisibleAndOrderedTabMenuByCurrentUser();
            static::resolveForActiveMenuItem($items, $controller);
            return new MenuView($items);
        }

        protected static function resolveForActiveMenuItem(&$items, $controller)
        {
            assert('$controller == null || $controller instanceof CController');
            assert('is_array($items)');
            foreach ($items as $key => $item)
            {
                if ($controller != null && isset($item['moduleId']) &&
                   $controller->resolveAndGetModuleId() == $item['moduleId'])
                {
                    $items[$key]['active'] = true;
                }
            }
        }

        protected static function makeRecentlyViewedView()
        {
            $items = AuditEventsRecentlyViewedUtil::getRecentlyViewedItemsByUser(Yii::app()->user->userModel, 10);
            return new RecentlyViewedView($items);
        }

        protected static function makeFlashMessageView(CController $controller)
        {
            return new FlashMessageView($controller);
        }

        protected static function makeModalContainerView()
        {
            return new ModalContainerView();
        }

        protected static function makeModalGameNotificationContainerView()
        {
            return new ModalGameNotificationContainerView(GameNotification::getAllByUser(Yii::app()->user->userModel));
        }

        protected static function makeFooterView()
        {
            return new FooterView();
        }
    }
?>