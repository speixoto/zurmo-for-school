<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * Calendar Items list view display.
     */
    class CalendarItemsListView extends ListView
    {
        protected $calendarItems;

        protected $params;

        public function __construct($controllerId, $moduleId, $calendarItems, $params)
        {
            $this->controllerId      = $controllerId;
            $this->moduleId          = $moduleId;
            $this->calendarItems     = $calendarItems;
            $this->params            = $params;
        }

        protected static function getPagerCssClass()
        {
            return 'pager horizontal';
        }

        protected static function getSummaryText()
        {
            return Zurmo::t('Core', '{start}-{end} of {count} result(s).');
        }

        protected function getCGridViewPagerParams()
        {
            return array(
                    'firstPageLabel'   => '<span>first</span>',
                    'prevPageLabel'    => '<span>previous</span>',
                    'nextPageLabel'    => '<span>next</span>',
                    'lastPageLabel'    => '<span>last</span>',
                    'paginationParams' => $this->params,
                    'route'            => '/calendars/default/getDayEvents',
                    'class'            => 'SimpleListLinkPager',
                );
        }

        /**
         * Override to not run global eval, since it causes doubling up of ajax requests on the pager.
         * (non-PHPdoc)
         * @see ListView::getCGridViewAfterAjaxUpdate()
         */
        protected function getCGridViewAfterAjaxUpdate()
        {
            // Begin Not Coding Standard
            return 'js:function(id, data) {
                        processAjaxSuccessError(id, data);
                        processListViewSummaryClone("' . $this->getGridViewId() . '",
                                                    "' . static::getSummaryCssClass() . '",
                                                    "' . $this->getSummaryCloneId() . '" );
                    }';
            // End Not Coding Standard
        }

        public static function getDesignerRulesType()
        {
            return null;
        }

        protected function getDataProvider()
        {
            return new CalendarListItemsDataProvider($this->calendarItems, $this->resolveConfigForDataProvider());
        }

        /**
         * Get the fields from calendar items to create a column array that fits the CGridView columns API
         */
         protected function getCGridViewColumns()
         {
            $columns = array('title', 'start', 'end');
            //$lastColumn = $this->getCGridViewLastColumn();
            $lastColumn = null;
            if (!empty($lastColumn))
            {
                array_push($columns, $lastColumn);
            }
            return $columns;
        }

        /**
         * Resolve configuration for data provider
         * @return array
         */
        protected function resolveConfigForDataProvider()
        {
            return array(
                            'pagination' => array(
                                                    'pageSize' => CalendarItemsDataProvider::MAXIMUM_CALENDAR_ITEMS_DISPLAYED_FOR_ANY_DATE,
                                                 )
                    );
        }

        protected function getCGridViewParams()
        {
            $params = parent::getCGridViewParams();
            $params['ajaxUpdate'] = true;
            return $params;
        }

        public function getGridViewId()
        {
            $startDateArray = explode('-', $this->params['startDate']);
            return 'calendarDayEvents-' . $startDateArray[2];
        }

        protected function renderContent()
        {
            $content = parent::renderContent();
            Yii::app()->getClientScript()->render($content);
            return $content;
        }
    }
?>
