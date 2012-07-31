<?php
/*
 * GlobalSearchResultsDataCollection
 * @param
 */
    class GlobalSearchResultsDataCollection
    {

        private $term;
        private $scopeData;
        private $user;
        private $views = array();

        /*
         * @param   string
         * @param   integer
         * @param   User        User model
         * @param   array       Modules to be searched
         */
        public function __construct($term, $pageSize, $user, $scopeData = null) {
            //TODO: make asserts
            $this->term = $term;
            $this->pageSize = $pageSize;
            $this->user = $user;
            $this->scopeData = $scopeData;
            $this->makeViews();
        }

        /*
         * makeViews
         * @return  array   moduleName => listView
         */
        private function makeViews()
        {
            $pageSize = $this->pageSize;
            $globalSearchModuleNamesAndLabelsData = GlobalSearchUtil::
                    getGlobalSearchScopingModuleNamesAndLabelsDataByUser($this->user);
            foreach ($globalSearchModuleNamesAndLabelsData as $moduleName => $label)
            {
                if ($this->scopeData == null || in_array($moduleName, $this->scopeData))
                {
                    $module = Yii::app()->findModule($moduleName);
                    $searchFormClassName = $module::getGlobalSearchFormClassName();
                    $modelClassName = $module::getPrimaryModelName();
                    $model  = new $modelClassName(false);
                    $searchForm = new $searchFormClassName($model);
                    $sanitizedSearchAttributes = MixedTermSearchUtil::
                                getGlobalSearchAttributeByModuleAndPartialTerm($module, $this->term);
                    $metadataAdapter = new SearchDataProviderMetadataAdapter(
                                $searchForm,
                                $this->user->id,
                                $sanitizedSearchAttributes
                            );
                    $listViewClassName = $module::getPluralCamelCasedName() . 'ListView';
                    $sortAttribute     = SearchUtil::resolveSortAttributeFromGetArray($modelClassName);
                    $sortDescending    = SearchUtil::resolveSortDescendingFromGetArray($modelClassName);
                    $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider(
                                 $metadataAdapter->getAdaptedMetadata(false),
                                 $modelClassName,
                                 'RedBeanModelDataProvider',
                                 $sortAttribute,
                                 $sortDescending,
                                 $pageSize,
                                 $module->getStateMetadataAdapterClassName()
                            );
                    $listView = new $listViewClassName(
                                'default',
                                $module->getId(),
                                $modelClassName,
                                $dataProvider,
                                GetUtil::resolveSelectedIdsFromGet(),
                                '-' .$moduleName,
                                array(
                                    'route' => '',
                                    'class' => 'SimpleListLinkPager'
                                  )
                             );                        
                    $this->views[$moduleName] = $listView;
                }                
            }
        }

        public function getViews()
        {
            return $this->views;
        }
    }
?>