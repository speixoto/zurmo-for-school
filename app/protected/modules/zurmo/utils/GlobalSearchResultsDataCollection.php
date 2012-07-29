<?php
    class GlobalSearchResultsDataCollection {        
        
        private $term;
        
        private $scopeData;
        
        private $user;
        
        private $dataCollection = array();        
        
        public function __construct($term, $scopeData, $user) {
            //TODO: make asserts
            $this->term = $term;
            $this->scopeData = $scopeData;
            $this->user = $user;
            $this->makeDataCollection();
        }
        
        private function makeDataCollection()
        {                                    
            $globalSearchModuleNamesAndLabelsData = GlobalSearchUtil::getGlobalSearchScopingModuleNamesAndLabelsDataByUser($this->user);            
            foreach ($globalSearchModuleNamesAndLabelsData as $moduleName => $label)
            {                                        
                $pageSize = 10; //TODO: Make generic
                $module = Yii::app()->findModule($moduleName);                
                $searchFormClassName = $module::getGlobalSearchFormClassName();
                $modelClassName = $module::getPrimaryModelName();                
                $model  = new $modelClassName(false);                
                $searchForm = new $searchFormClassName($model);  
                $searchAttributes = MixedTermSearchUtil::
                                     getGlobalSearchAttributeByModuleAndPartialTerm($module, $this->term);
                $dataProvider = new SearchDataProviderMetadataAdapter(
                                                $searchForm, 1, $searchAttributes);    
                
                $listViewClassName   = $module->getPluralCamelCasedName() . 'ListView';                
                $listView = new $listViewClassName(
                                    'default',
                                    $module->getId(),
                                    get_class($model),
                                    $dataProvider,
                                    GetUtil::resolveSelectedIdsFromGet());
                $this->dataCollection = $listView;
                print_r($dataProvider);                
            }
        }
        
        public function getDataCollection()
        {
            return $this->dataCollection;
        }              
    }
?>