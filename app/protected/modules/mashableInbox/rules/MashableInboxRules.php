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

    abstract class MashableInboxRules
    {

        public $shouldRenderCreateAction = true;

        abstract public function getUnreadCountForCurrentUser();

        abstract public function getMetadataFilteredByFilteredBy($filteredBy);

        abstract public function getMetadataFilteredByOption($option);

        abstract public function getActionViewOptions();

        abstract public function getModelClassName();

        abstract public function getMachableInboxOrderByAttributeName();

        public function getSearchAttributeData($searchTerm)
        {
            return null;
        }

        protected function getListViewClassName()
        {
            $modelClassName = $this->getModelClassName();
            return $modelClassName . 's' . 'ListView';
        }

        public function getListView($option, $filteredBy = MashableInboxForm::FILTERED_BY_ALL, $searchTerm = '')
        {
            $modelClassName             = $this->getModelClassName();
            $orderBy                    = $this->getMachableInboxOrderByAttributeName();
            $pageSize                   = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                                                'listPageSize', get_class(Yii::app()->controller->module));
            $metadataByOption           = $this->getMetadataFilteredByOption($option);
            $metadataByOptionAndFilter  = MashableUtil::mergeMetada($metadataByOption, $this->getMetadataFilteredByFilteredBy($filteredBy));
            $metadata                   = MashableUtil::mergeMetada($metadataByOptionAndFilter, $this->getSearchAttributeData($searchTerm));
            $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider(
                $metadata,
                $modelClassName,
                'RedBeanModelDataProvider',
                $orderBy,
                true,
                $pageSize
            );
            $listViewClassName = $this->getListViewClassName();
            $listView = new $listViewClassName(
                    Yii::app()->controller->id,
                    Yii::app()->controller->module->id,
                    $modelClassName,
                    $dataProvider,
                    array());
            return $listView;
        }

        public function getModelStringContent(RedBeanModel $model)
        {
            $modelDisplayString = strval($model);
            $params          = array('label' => $modelDisplayString, 'wrapLabel' => false);
            $moduleClassName = $model->getModuleClassName();
            $moduleId        = $moduleClassName::getDirectoryName();
            $element         = new DetailsLinkActionElement('default', $moduleId, $model->id, $params);
            return $element->render();
        }

        public function getModelCreationTimeContent(RedBeanModel $model)
        {
            return MashableUtil::getTimeSinceLatestUpdate($model->latestDateTime);
        }

        public function getSummaryContentTemplate()
        {
            return "<span>{modelStringContent}</span><span>{modelCreationTimeContent}</span>";
        }
    }
?>