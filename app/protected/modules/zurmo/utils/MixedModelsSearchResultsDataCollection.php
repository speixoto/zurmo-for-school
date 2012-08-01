<?php
/*
 * MixedModelsSearchSearchResultsDataCollection
 * @param
 */
    class MixedModelsSearchResultsDataCollection
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
        }
                
        /*
         * @return  View
         */
        public function getView($moduleName)
        {            
            $pageSize = $this->pageSize;
            $module = Yii::app()->findModule($moduleName);
            $searchFormClassName = $module::getGlobalSearchFormClassName();
            $modelClassName = $module::getPrimaryModelName();            
            $model = new $modelClassName(false);
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
            $listView->setRowsAreSelectable(false);
            return $listView;
        }
        
        /*
         * makeViews
         * @return  array   moduleName => listView
         */
        private function makeViews()
        {                        
            foreach ($this->getSearchedModules() as $moduleName => $title)
            {                
                $titleView = new TitleBarView($title, null, 3);
                $this->views['titleBar-' . $moduleName] = $titleView;
                $this->views[$moduleName] = $this->getView($moduleName);
            }
        }

        public function getSearchedModules()
        {
            $modules = array();
            $globalSearchModuleNamesAndLabelsData = GlobalSearchUtil::
                    getGlobalSearchScopingModuleNamesAndLabelsDataByUser($this->user);
            foreach ($globalSearchModuleNamesAndLabelsData as $moduleName => $label)
            {
                if ($this->scopeData == null || in_array($moduleName, $this->scopeData))
                {                 
                    $module = Yii::app()->findModule($moduleName);
                    $title = $module::getPluralCamelCasedName();
                    $modules[$moduleName] = $title;                    
                }                
            }
            return $modules;
        }
        
        public function getViews()
        {     
            $this->term = '?';
            $this->makeViews();
            return $this->views;
        }
    }
?>