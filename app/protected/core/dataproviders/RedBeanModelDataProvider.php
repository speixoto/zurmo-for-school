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
     * A data provider that returns models.
     */
    class RedBeanModelDataProvider extends CDataProvider
    {
        protected $modelClassName;
        protected $sortAttribute;
        protected $sortDescending;
        protected $searchAttributeData;
        protected $calculatedTotalItemCount;
        protected $offset;

        /**
         * @sortAttribute - Currently supports only non-related attributes.
         */
        public function __construct($modelClassName, $sortAttribute = null, $sortDescending = false, array $searchAttributeData = array(), array $config = array())
        {
            assert('is_string($modelClassName) && $modelClassName != ""');
            assert('$sortAttribute === null || is_string($sortAttribute) && $sortAttribute != ""');
            assert('is_bool($sortDescending) || $sortDescending === null');
            $this->modelClassName               = $modelClassName;
            $this->sortAttribute                = $sortAttribute;
            $this->sortDescending               = $sortDescending;
            $this->searchAttributeData          = $searchAttributeData;
            $this->setId($this->modelClassName);
            foreach ($config as $key => $value)
            {
                $this->$key = $value;
            }
            $sort = new RedBeanSort($this->modelClassName);
            $sort->sortVar = $this->getId().'_sort';
            $sort->setSortAttribute($sortAttribute);
            $sort->setSortDescending($sortDescending);
            $this->setSort($sort);
        }

        public function getModelClassName()
        {
            return $this->modelClassName;
        }

        /**
         * Override the offset value that comes from the pagination object if needed.  Used by sticky search for example
         * when retrieving a sticky list in the detail view of a model.
         * @param  integer $offset
         */
        public function setOffset($offset)
        {
            assert('is_int($offset)');
            $this->offset = $offset;
        }

        /**
         * If the count query results in 0, the data query will not be run and an empty array will be returned. This
         * helps to reduce queries to the database.
         * See the yii documentation.
         */
        protected function fetchData()
        {
            $pagination = $this->getPagination();
            if (isset($pagination))
            {
                $totalItemCount = $this->getTotalItemCount();
                $pagination->setItemCount($totalItemCount);
                $offset = $pagination->getOffset();
                $limit  = $pagination->getLimit();
            }
            else
            {
                $offset = 0;
                $limit  = null;
            }
            if ($this->offset != null)
            {
                $offset = $this->offset;
            }
            if ($totalItemCount == 0)
            {
                return array();
            }
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter($this->modelClassName);
            $where = $this->makeWhere($this->modelClassName, $this->searchAttributeData, $joinTablesAdapter);
            $orderBy = null;
            if ($this->sortAttribute !== null)
            {
                $orderBy = self::resolveSortAttributeColumnName($this->modelClassName,
                                                                $joinTablesAdapter, $this->sortAttribute);
                if ($this->sortDescending)
                {
                    $orderBy .= ' desc';
                }
            }
            else
            {
                try
                {
                    $sortAttribute = self::getSortAttributeName($this->modelClassName);
                    if ($sortAttribute != null)
                    {
                        $orderBy = self::resolveSortAttributeColumnName($this->modelClassName, $joinTablesAdapter, $sortAttribute);
                        if ($this->sortDescending)
                        {
                            $orderBy .= ' desc';
                        }
                    }
                }
                catch (NotImplementedException $e)
                {
                }
            }
            $modelClassName = $this->modelClassName;
            $this->resolveExtraSql($joinTablesAdapter, $where);
            return $modelClassName::getSubset($joinTablesAdapter, $offset, $limit, $where, $orderBy,
                                              $this->modelClassName, $joinTablesAdapter->getSelectDistinct());
        }

        /**
         *
         */
        public static function resolveSortAttributeColumnName($modelClassName, &$joinTablesAdapter, $sortAttribute)
        {
            assert('$sortAttribute === null || is_string($sortAttribute) && $sortAttribute != ""');
            $sortRelatedAttribute = null;
            if ($modelClassName::isRelation($sortAttribute))
            {
                $relationType = $modelClassName::getRelationType($sortAttribute);
                //MANY_MANY not supported currently for sorting.
                assert('$relationType != RedBeanModel::MANY_MANY');
                $relationModelClassName = $modelClassName::getRelationModelClassName($sortAttribute);
                $sortRelatedAttribute   = self::getSortAttributeName($relationModelClassName);
            }
            $modelAttributeToDataProviderAdapter = new RedBeanModelAttributeToDataProviderAdapter(
                                                       $modelClassName, $sortAttribute, $sortRelatedAttribute);
            return ModelDataProviderUtil::resolveSortAttributeColumnName($modelAttributeToDataProviderAdapter,
                                                                         $joinTablesAdapter);
        }

        /**
         * Each model has a sort attribute that is used to order the models if none is specified.
         */
        public static function getSortAttributeName($modelClassName)
        {
            $metadata = $modelClassName::getMetadata();
            while (!isset($metadata[$modelClassName]['defaultSortAttribute']))
            {
                $modelClassName = get_parent_class($modelClassName);
                if ($modelClassName == 'RedBeanModel')
                {
                    //This means the sortAttribute value was not found.
                    throw new NotImplementedException();
                }
            }
            assert('isset($metadata[$modelClassName]["defaultSortAttribute"])');
            return $metadata[$modelClassName]['defaultSortAttribute'];
        }

        /**
         * @return CSort the sorting object. If this is false, it means the sorting is disabled.
         */
        public function getSort()
        {
            if (($sort = parent::getSort()) !== false)
            {
                $sort->modelClass = $this->modelClassName;
            }
            return $sort;
        }

        /**
         * Not for use by applications. Public for unit tests only.
         * Override from RedBeanModelDataProvider to support multiple
         * where clauses for the same attribute and operatorTypes
         * @param metadata - array expected to have clauses and structure elements
         * @param $joinTablesAdapter
         * @see DataProviderMetadataAdapter
         * @return string
         */
        public static function makeWhere($modelClassName, array $metadata, &$joinTablesAdapter)
        {
            return ModelDataProviderUtil::makeWhere($modelClassName, $metadata, $joinTablesAdapter);
        }

        /**
         * See the yii documentation. This function is made public for unit testing.  Setting $selectDistinct to true
         * since the count should always be on unique ids
         */
        public function calculateTotalItemCount()
        {
            $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter($this->modelClassName);
            $where             = $this->makeWhere($this->modelClassName, $this->searchAttributeData, $joinTablesAdapter);
            $modelClassName    = $this->modelClassName;
            $this->resolveExtraSql($joinTablesAdapter, $where);
            return $modelClassName::getCount($joinTablesAdapter, $where, $this->modelClassName, true);
        }

        /**
         * See the yii documentation.
         */
        protected function fetchKeys()
        {
            $keys = array();
            foreach ($this->getData() as $model)
            {
                $keys[] = $model->id;
            }
            return $keys;
        }

        /**
         * Override to add extra where option to the sql
         * @param RedBeanModelJoinTablesQueryAdapter $joinTablesAdapter
         * @param type $where
         */
        protected function resolveExtraSql(RedBeanModelJoinTablesQueryAdapter &$joinTablesAdapter, &$where)
        {
        }
    }
?>
