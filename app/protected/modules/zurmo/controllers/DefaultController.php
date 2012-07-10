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

    class ZurmoDefaultController extends ZurmoBaseController
    {
        public function filters()
        {
            $moduleClassName = get_class($this->getModule());
            return array(
                array(
                    ZurmoBaseController::RIGHTS_FILTER_PATH . ' + logout, index, about',
                    'moduleClassName' => $moduleClassName,
                    'rightName'       => $moduleClassName::getAccessRight(),
               ),
                array(
                    ZurmoBaseController::RIGHTS_FILTER_PATH . ' + configurationEdit',
                    'moduleClassName' => $moduleClassName,
                    'rightName'       => ZurmoModule::RIGHT_ACCESS_GLOBAL_CONFIGURATION,
               ),
            );
        }

        public function actionIndex()
        {
            $this->redirect(Yii::app()->homeUrl);
        }

        public function actionLogin()
        {
            $formModel = new LoginForm();
            if (isset($_POST['ajax']) && $_POST['ajax'] == 'login-form')
            {
                echo ZurmoActiveForm::validate($formModel);
                Yii::app()->end(0, false);
            }
            elseif (isset($_POST['LoginForm']))
            {
                $formModel->attributes = $_POST['LoginForm'];
                if ($formModel->validate() && $formModel->login())
                {
                    $this->redirect(Yii::app()->user->returnUrl);
                }
            }
            $extraHeaderContent = ZurmoConfigurationUtil::getByModuleName('ZurmoModule', 'loginViewExtraHeaderContent');
            $view = new LoginPageView($this, $formModel, $extraHeaderContent);
            echo $view->render();
        }

        public function actionLogout()
        {
            Yii::app()->user->logout();
            $this->redirect(Yii::app()->homeUrl);
        }

        public function actionError()
        {
            if ($error = Yii::app()->errorHandler->error)
            {
                if (Yii::app()->request->isAjaxRequest)
                {
                    echo $error['message'];
                }
                else
                {
                    $view = new ErrorPageView($error['message']);
                    echo $view->render();
                }
            }
        }

        public function actionUnsupportedBrowser($name)
        {
            if ($name == '')
            {
                $name = 'not detected';
            }
            $view = new UnsupportedBrowserPageView($name);
            echo $view->render();
        }

        public function actionAbout()
        {
            $view = new AboutPageView(ZurmoDefaultViewUtil::
                                         makeStandardViewForCurrentUser($this, new AboutView()));
            echo $view->render();
        }

        public function actionConfigurationEdit()
        {
            $configurationForm = ZurmoConfigurationFormAdapter::makeFormFromGlobalConfiguration();
            $postVariableName   = get_class($configurationForm);
            if (isset($_POST[$postVariableName]))
            {
                $configurationForm->setAttributes($_POST[$postVariableName]);
                if ($configurationForm->validate())
                {
                    ZurmoConfigurationFormAdapter::setConfigurationFromForm($configurationForm);
                    Yii::app()->user->setFlash('notification',
                        Yii::t('Default', 'Global configuration saved successfully.')
                    );
                    $this->redirect(Yii::app()->createUrl('configuration/default/index'));
                }
            }
            $editView = new ZurmoConfigurationEditAndDetailsView(
                                    'Edit',
                                    $this->getId(),
                                    $this->getModule()->getId(),
                                    $configurationForm);
            $editView->setCssClasses( array('AdministrativeArea') );
            $view = new ZurmoConfigurationPageView(ZurmoDefaultAdminViewUtil::
                                         makeStandardViewForCurrentUser($this, $editView));
            echo $view->render();
        }

        public function actionGlobalSearchAutoComplete($term)
        {
            $scopeData = GlobalSearchUtil::resolveGlobalSearchScopeFromGetData($_GET);
            $pageSize  = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'autoCompleteListPageSize', get_class($this->getModule()));
            $autoCompleteResults = ModelAutoCompleteUtil::
                                   getGlobalSearchResultsByPartialTerm($term, $pageSize, Yii::app()->user->userModel,
                                                                       $scopeData);
            echo CJSON::encode($autoCompleteResults);
        }

        /**
         * Given a name of a customFieldData object and a term to search on return a JSON encoded
         * array of autocomplete search results.
         * @param string $name - Name of CustomFieldData
         * @param string $term - term to search on
         */
        public function actionAutoCompleteCustomFieldData($name, $term)
        {
            assert('is_string($name)');
            assert('is_string($term)');
            $autoCompleteResults = ModelAutoCompleteUtil::getCustomFieldDataByPartialName(
                                       $name, $term);
            if (count($autoCompleteResults) == 0)
            {
                $data = 'No Results Found';
                $autoCompleteResults[] = array('id'    => '',
                                               'name' => $data
                );
            }
            echo CJSON::encode($autoCompleteResults);
        }

        public function actionDynamicSearchAddExtraRow($viewClassName, $modelClassName, $formModelClassName, $rowNumber, $suffix = null)
        {
            $searchableAttributeIndicesAndDerivedTypes = DynamicSearchUtil::
                                                            getSearchableAttributesAndLabels($viewClassName,
                                                                                             $modelClassName);
            $ajaxOnChangeUrl  = Yii::app()->createUrl("zurmo/default/dynamicSearchAttributeInput",
                                   array('viewClassName'      => $viewClassName,
                                         'modelClassName'     => $modelClassName,
                                         'formModelClassName' => $formModelClassName,
                                         'rowNumber'          => $rowNumber,
                                         'suffix'             => $suffix));
            $extraRowView     = new DynamicSearchExtraRowView(
                                    $searchableAttributeIndicesAndDerivedTypes, (int)$rowNumber, $suffix,
                                    $formModelClassName, $ajaxOnChangeUrl);
            $view             = new AjaxPageView($extraRowView);
            echo CHtml::tag('div', array('class' => 'dynamic-search-row'), $view->render());
        }

        public function actionDynamicSearchAttributeInput($viewClassName, $modelClassName, $formModelClassName, $rowNumber,
                                                          $attributeIndexOrDerivedType, $suffix = null)
        {
            if($attributeIndexOrDerivedType == null)
            {
                Yii::app()->end(0, false);
            }
            $content          = null;
            if(count(explode(DynamicSearchUtil::RELATION_DELIMITER, $attributeIndexOrDerivedType)) > 1)
            {
                $model            = new $modelClassName(false);
                $nestedAttributes = explode(DynamicSearchUtil::RELATION_DELIMITER, $attributeIndexOrDerivedType);
                $inputPrefix      = array($formModelClassName, DynamicSearchForm::DYNAMIC_NAME, $rowNumber);
                $totalNestedCount = count($nestedAttributes);
                $processCount     = 1;
                foreach($nestedAttributes as $attribute)
                {
                    if($processCount < $totalNestedCount)
                    {
                        $model           = SearchUtil::resolveModelToUseByModelAndAttributeName($model, $attribute);
                        $inputPrefix[]   = $attribute;
                        $relatedDataName = Element::resolveInputIdPrefixIntoString($inputPrefix) . '[relatedData]';
                        $content        .= ZurmoHtml::hiddenField($relatedDataName, true);
                    }
                    $processCount ++;
                }
                $attributeIndexOrDerivedType = $attribute;
                $modelToUse                  = $model;
                $cellElementModelClassName   = get_class($model->getModel());
                //Dynamic Search needs to always assume there is an available SearchForm
                //Always assumes the SearchView to use matches the exact pluralCamelCasedName.
                //Does not support nested relations to leads persay.  It will resolve as a Contact.
                $moduleClassName             = $model->getModel()->getModuleClassName();
                $viewClassName               = $moduleClassName::getPluralCamelCasedName() . 'SearchView';
                $element                     = DynamicSearchUtil::getCellElement($viewClassName, $cellElementModelClassName,
                                                                                 $attributeIndexOrDerivedType);
            }
            else
            {
                $model                 = new $modelClassName(false);
                $modelToUse            = new $formModelClassName($model);
                $inputPrefix           = array($formModelClassName, DynamicSearchForm::DYNAMIC_NAME, $rowNumber);
                $element               = DynamicSearchUtil::getCellElement($viewClassName, $modelClassName,
                                                                          $attributeIndexOrDerivedType);
            }
            $form                      = new NoRequiredsActiveForm();
            $element['inputPrefix']    = $inputPrefix;
            $elementclassname          = $element['type'] . 'Element';
            $element                   = new $elementclassname($modelToUse, $element['attributeName'],
                                                              $form, array_slice($element, 2));
            $element->editableTemplate = '{content}{error}';
            $content                  .= $element->render();
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.views.assets')) . '/dropDownInteractions.js', CClientScript::POS_END);
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('ext.zurmoinc.framework.views.assets')) . '/jquery.dropkick-1.0.0.js', CClientScript::POS_END);
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        public function actionValidateDynamicSearch($viewClassName, $modelClassName, $formModelClassName)
        {
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'search-form' && isset($_POST[$formModelClassName]))
            {
                $model                     = new $modelClassName(false);
                $searchForm                = new $formModelClassName($model);
                //$rawPostFormData           = $_POST[$formModelClassName];
                if(isset($_POST[$formModelClassName]['anyMixedAttributesScope']))
                {
                    $searchForm->setAnyMixedAttributesScope($_POST[$formModelClassName]['anyMixedAttributesScope']);
                    unset($_POST[$formModelClassName]['anyMixedAttributesScope']);
                }
                $sanitizedSearchData = $this->resolveAndSanitizeDynamicSearchAttributesByPostData(
                                                                $_POST[$formModelClassName], $searchForm);
                $searchForm->setAttributes($sanitizedSearchData);
                if(isset($_POST['save']) && $_POST['save'] == 'saveSearch')
                {
                    $searchForm->setScenario('validateSaveSearch');
                    if($searchForm->validate())
                    {
                        $this->processSaveSearch($searchForm);
                    }
                }
                else
                {
                    $searchForm->setScenario('validateDynamic');
                }
                if(!$searchForm->validate())
                {
                     $errorData = array();
                    foreach ($searchForm->getErrors() as $attribute => $errors)
                    {
                            $errorData[CHtml::activeId($searchForm, $attribute)] = $errors;
                    }
                    echo CJSON::encode($errorData);
                    Yii::app()->end(0, false);
                }
            }
        }

        protected function processSaveSearch($searchForm)
        {
            /**
            echo "<pre>";
            print_r($searchForm->anyMixedAttributes);
            print_r($searchForm->getAnyMixedAttributesScope());
            print_r($searchForm->dynamicStructure);
            print_r($searchForm->dynamicClauses);
            print_r($searchForm->savedSearchName);
            print_r($searchForm->savedSearchId);
            echo "</pre>";
            **/
            //adapter needed to go both ways i think.
            //$savedSearch = SomeAdapter::makeSavedSearchBySearchForm($searchForm);
            //$savedSearch->save();
            //what happens if this fails to save? throw not failed to save exception
        }

        protected function resolveAndSanitizeDynamicSearchAttributesByPostData($postData, DynamicSearchForm $searchForm)
        {
            if(isset($postData['dynamicClauses']))
            {
                $dynamicSearchAttributes          = SearchUtil::getSearchAttributesFromSearchArray($postData['dynamicClauses']);
                $sanitizedDynamicSearchAttributes = SearchUtil::
                                                    sanitizeDynamicSearchAttributesByDesignerTypeForSavingModel(
                                                        $searchForm, $dynamicSearchAttributes);
                $postData['dynamicClauses']       = $sanitizedDynamicSearchAttributes;
            }
            return $postData;
        }
    }
?>