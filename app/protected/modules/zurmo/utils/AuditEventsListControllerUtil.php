<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class to render a list of audit events called from a model controller.
     */
    class AuditEventsListControllerUtil
    {
        /**
         * @return rendered content from view as string.
         */
        public static function renderList(CController $controller, $dataProvider)
        {
            assert('$dataProvider instanceof RedBeanModelDataProvider');
            $auditEventsListView = new AuditEventsModalListView(
                $controller->getId(),
                $controller->getModule()->getId(),
                'AuditEvent',
                $dataProvider,
                'modal'
            );
            $view = new ModalView($controller, $auditEventsListView);
            return $view->render();
        }

        /**
         * Creates the appropriate filtering of audit events by the specified model.
         * @param object $model AuditEvent
         * @return array $searchAttributeData
         */
        public static function makeModalSearchAttributeDataByAuditedModel($model)
        {
            assert('$model instanceof Item');
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'        => 'modelClassName',
                    'operatorType'         => 'equals',
                    'value'                => get_class($model),
                ),
                2 => array(
                    'attributeName'        => 'modelId',
                    'operatorType'         => 'equals',
                    'value'                => $model->id,
                ),
                3 => array(
                    'attributeName'        => 'eventName',
                    'operatorType'         => 'doesNotEqual',
                    'value'                => ZurmoModule::AUDIT_EVENT_ITEM_VIEWED,
                )
            );
            $searchAttributeData['structure'] = '1 and 2 and 3';
            return $searchAttributeData;
        }

        /**
         * Given an array of searchAttributeData, a RedBeanModelDataProvider is created and returned.
         * @param array $searchAttributeData
         * @return object $RedBeanModelDataProvider
         */
        public static function makeDataProviderBySearchAttributeData($searchAttributeData)
        {
            assert('is_array($searchAttributeData)');
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType('subListPageSize');
            return new RedBeanModelDataProvider( 'AuditEvent', 'dateTime', true,
                                                                $searchAttributeData, array(
                                                                    'pagination' => array(
                                                                        'pageSize' => $pageSize,
                                                                    )
                                                                ));
        }
    }
?>