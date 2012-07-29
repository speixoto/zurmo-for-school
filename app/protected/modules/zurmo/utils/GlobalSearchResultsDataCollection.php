<?php
    class GlobalSearchResultsDataCollection extends ZurmoModuleController{        
        
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
                $sanitizedSearchAttributes = MixedTermSearchUtil::
                                     getGlobalSearchAttributeByModuleAndPartialTerm($module, $this->term);                                                
                $metadataAdapter = new SearchDataProviderMetadataAdapter(
                                                        $model,
                                                        $this->user->id,
                                                        aray()//$sanitizedSearchAttributes
                                            );                   
                $listViewClassName = $module::getPluralCamelCasedName() . 'ListView';                
                $dataProvider = RedBeanModelDataProviderUtil::makeDataProvider(
                             $metadataAdapter->getAdaptedMetadata(false),
                             $modelClassName,
                             'RedBeanModelDataProvider',
                             'createdDateTime',
                             true,
                             $pageSize               
                        );                
                $listView = new $listViewClassName(
                                    'default',
                                    $module->getId(),
                                    $modelClassName,
                                    $dataProvider,
                                    GetUtil::resolveSelectedIdsFromGet());                                               
                $this->dataCollection[$moduleName] = $listView;
                echo '<p>';
                print_r($moduleName);
                echo '</p>';                
            }
        }
        
        public function getDataCollection()
        {
            return $this->dataCollection;
        }              
    }
?>