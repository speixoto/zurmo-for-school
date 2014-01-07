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

    class DesignerDefaultController extends ZurmoBaseController
    {
        public function actionIndex()
        {
            $title           = Zurmo::t('DesignerModule', 'Available Modules');
            $breadCrumbLinks = array(
                 $title,
            );
            $canvasView = new TitleBarAndDesignerPageMenuView(
                        $this->getId(),
                        $this->getModule()->getId(),
                        $title
            );
            $view = new DesignerPageView(ZurmoDefaultAdminViewUtil::
                            makeViewWithBreadcrumbsForCurrentUser($this, $canvasView, $breadCrumbLinks, 'DesignerBreadCrumbView'));
            echo $view->render();
        }

        public function actionModulesMenu()
        {
            assert('!empty($_GET["moduleClassName"])');
            $moduleClassName = $_GET['moduleClassName'];
            $module          = new $moduleClassName(null, null);
            $moduleMenuItems = $module->getDesignerMenuItems();
            if (ArrayUtil::getArrayValue($moduleMenuItems, 'showGeneralLink'))
            {
                $this->actionModuleEdit();
            }
            elseif (ArrayUtil::getArrayValue($moduleMenuItems, 'showFieldsLink'))
            {
                $this->actionAttributesList();
            }
            elseif (ArrayUtil::getArrayValue($moduleMenuItems, 'showLayoutsLink'))
            {
                $this->actionModuleLayoutsList();
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public function actionAttributesList()
        {
            assert('!empty($_GET["moduleClassName"])');
            $moduleClassName = $_GET['moduleClassName'];
            $module          = new $_GET['moduleClassName'](null, null);
            $title           = $moduleClassName::getModuleLabelByTypeAndLanguage('Plural') .
                               ': ' . Zurmo::t('DesignerModule', 'Fields');
            $breadCrumbLinks = array($title);
            $overrideClassName = $moduleClassName . 'AttributesListView';
            $overrideClassFile = Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR .
                                 $moduleClassName::getDirectoryName() .
                                 DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $overrideClassName . '.php';
            if (is_file($overrideClassFile) && class_exists($overrideClassName))
            {
                $viewClassName = $moduleClassName . 'AttributesListView';
                $canvasView    = new $viewClassName($this->getId(), $this->getModule()->getId());
            }
            else
            {
                $modelClassName           = $moduleClassName::getPrimaryModelName();
                $model                    = new $modelClassName();
                $adapter                  = new ModelAttributesAdapter($model);
                $derivedAttributesAdapter = new DerivedAttributesAdapter(get_class($model));
                $customAttributes         = array_merge($adapter->getCustomAttributes(),
                                                        $derivedAttributesAdapter->getAttributes());
                $customAttributes = ArrayUtil::subValueSort($customAttributes, 'attributeLabel', 'asort');
                $standardAttributes = ArrayUtil::subValueSort($adapter->getStandardAttributes(), 'attributeLabel', 'asort');
                $canvasView = new StandardAndCustomAttributesListView(
                            $this->getId(),
                            $this->getModule()->getId(),
                            $module,
                            $moduleClassName::getModuleLabelByTypeAndLanguage('Plural'),
                            $standardAttributes,
                            $customAttributes,
                            $modelClassName
                );
            }
            $view = new DesignerPageView(ZurmoDefaultAdminViewUtil::
                            makeViewWithBreadcrumbsForCurrentUser($this, $canvasView, $breadCrumbLinks, 'DesignerBreadCrumbView'));
            echo $view->render();
        }

        public function actionAttributeEdit()
        {
            assert('!empty($_GET["moduleClassName"])');
            assert('!empty($_GET["attributeTypeName"])');
            $attributeFormClassName = $_GET['attributeTypeName'] . 'AttributeForm';
            $module          = new $_GET['moduleClassName'](null, null);
            $moduleClassName = $_GET['moduleClassName'];
            $modelClassName  = $moduleClassName::getPrimaryModelName();
            $model = new $modelClassName();
            if (!empty($_GET['attributeName']))
            {
                $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($model, $_GET["attributeName"]);
                $attributeForm->setScenario('updateAttribute');
            }
            else
            {
                $attributeForm   = new $attributeFormClassName();
                $attributeForm->setScenario('createAttribute');
                $attributeForm->setModelClassName($modelClassName);
            }
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'edit-form')
            {
                $this->actionAttributeValidate($attributeForm, $modelClassName);
            }
            if (isset($_POST[get_class($attributeForm)]))
            {
                $this->actionAttributeSave($attributeForm, $model);
            }
            $title           = static::resolveAttributeEditTitle($attributeForm);
            $breadCrumbLinks = array(
                    $moduleClassName::getModuleLabelByTypeAndLanguage('Plural') . ': ' . Zurmo::t('DesignerModule', 'Fields') =>
                    array('default/attributesList',  'moduleClassName' => $_GET['moduleClassName']),
                $title,
            );
            $canvasView = new ActionBarAndAttributeEditView(
                        $this->getId(),
                        $this->getModule()->getId(),
                        $module,
                        $_GET['attributeTypeName'],
                        $modelClassName,
                        $attributeForm,
                        $title
            );
            $view = new DesignerPageView(ZurmoDefaultAdminViewUtil::
                            makeViewWithBreadcrumbsForCurrentUser($this, $canvasView, $breadCrumbLinks, 'DesignerBreadCrumbView'));
            echo $view->render();
        }

        protected static function resolveAttributeEditTitle(AttributeForm $model)
        {
            if (empty($model->attributeName))
            {
                return Zurmo::t('DesignerModule', 'Create Field') . ': ' . $model::getAttributeTypeDisplayName();
            }
            else
            {
                return Zurmo::t('DesignerModule', 'Edit Field')   . ': ' . strval($model);
            }
        }

        protected function actionAttributeValidate($attributeForm)
        {
            echo ZurmoActiveForm::validate($attributeForm);
            Yii::app()->end(0, false);
        }

        protected function actionAttributeSave($attributeForm, $model)
        {
            assert('!empty($_GET["moduleClassName"])');
            $wasRequired = $attributeForm->isRequired;
            $attributeForm->setAttributes($_POST[get_class($attributeForm)]);
            $modelAttributesAdapterClassName = $attributeForm::getModelAttributeAdapterNameForSavingAttributeFormData();
            $adapter = new $modelAttributesAdapterClassName($model);
            $adapter->setAttributeMetadataFromForm($attributeForm);
            if ($attributeForm->isRequired && !$wasRequired)
            {
                RequiredAttributesValidViewUtil::
                resolveToSetAsMissingRequiredAttributesByModelClassName(get_class($model), $attributeForm->attributeName);
            }
            elseif (!$attributeForm->isRequired && $wasRequired)
            {
                RequiredAttributesValidViewUtil::
                resolveToRemoveAttributeAsMissingRequiredAttribute(get_class($model), $attributeForm->attributeName);
            }
            RedBeanModelsCache::forgetAll(); //Ensures existing models that are cached see the new dropdown.
            $routeParams = array_merge(
                array('default/attributesList'), $_GET
            );
            $this->redirect($routeParams);
        }

        public function actionModuleLayoutsList()
        {
            assert('!empty($_GET["moduleClassName"])');
            $moduleClassName = $_GET['moduleClassName'];
            $module          = new $_GET['moduleClassName'](null, null);
            $modelClassName  = $moduleClassName::getPrimaryModelName();
            $model           = new $modelClassName();
            $viewClassNames  = $moduleClassName::getViewClassNames();
            $editableViewsCollection = array();
            foreach ($viewClassNames as $className)
            {
                $classToEvaluate     = new ReflectionClass($className);
                if (is_subclass_of($className, 'MetadataView') && !$classToEvaluate->isAbstract() &&
                    $className::getDesignerRulesType() != null)
                {
                    $designerRulesType = $className::getDesignerRulesType();
                    $designerRulesClassName = $designerRulesType . 'DesignerRules';
                    $designerRules = new $designerRulesClassName();
                    if ($designerRules->allowEditInLayoutTool())
                    {
                        $editableViewsCollection[] = array(
                            'titleLabel' => $designerRules->resolveDisplayNameByView($className),
                            'route' => '/designer/default/layoutEdit',
                            'viewClassName' => $className,
                        );
                    }
                }
            }
            $editableViewsCollection = ArrayUtil::subValueSort($editableViewsCollection, 'titleLabel', 'asort');
            $title           = $moduleClassName::getModuleLabelByTypeAndLanguage('Plural') .
                               ': ' . Zurmo::t('DesignerModule', 'Layouts');
            $breadCrumbLinks = array($title);
            $canvasView = new ActionBarAndModuleEditableMetadataCollectionView(
                        $this->getId(),
                        $this->getModule()->getId(),
                        $module,
                        $moduleClassName::getModuleLabelByTypeAndLanguage('Plural'),
                        $editableViewsCollection,
                        $title
            );
            $view = new DesignerPageView(ZurmoDefaultAdminViewUtil::
                            makeViewWithBreadcrumbsForCurrentUser($this, $canvasView, $breadCrumbLinks, 'DesignerBreadCrumbView'));
            echo $view->render();
        }

        public function actionLayoutEdit()
        {
            assert('!empty($_GET["moduleClassName"])');
            assert('!empty($_GET["viewClassName"])');
            $viewClassName            = $_GET['viewClassName'];
            $moduleClassName          = $_GET['moduleClassName'];
            $modelClassName           = $moduleClassName::getPrimaryModelName();
            $editableMetadata         = $viewClassName::getMetadata();
            $designerRulesType        = $viewClassName::getDesignerRulesType();
            $designerRulesClassName   = $designerRulesType . 'DesignerRules';
            $designerRules            = new $designerRulesClassName();
            $modelAttributesAdapter   = DesignerModelToViewUtil::getModelAttributesAdapter($viewClassName, $modelClassName);
            $derivedAttributesAdapter = new DerivedAttributesAdapter($modelClassName);
            $attributeCollection      = array_merge($modelAttributesAdapter->getAttributes(),
                                                        $derivedAttributesAdapter->getAttributes());
            $attributesLayoutAdapter = AttributesLayoutAdapterUtil::makeAttributesLayoutAdapter(
                $attributeCollection,
                $designerRules,
                $editableMetadata
            );
            if (isset($_POST['save']))
            {
                $layoutMetadataAdapter = new LayoutMetadataAdapter(
                    $viewClassName,
                    $moduleClassName,
                    $editableMetadata,
                    $designerRules,
                    $attributesLayoutAdapter->getPlaceableLayoutAttributes(),
                    $attributesLayoutAdapter->getRequiredDerivedLayoutAttributeTypes()
                );
                $savableMetadata = array();
                if ( $designerRules->canConfigureLayoutPanelsType() &&
                    !PanelsDisplayTypeLayoutMetadataUtil::populateSaveableMetadataFromPostData($savableMetadata,
                        $_POST['LayoutPanelsTypeForm']))
                {
                    echo CJSON::encode(array('message' => Zurmo::t('DesignerModule', 'Invalid panel configuration type'), 'type' => 'error'));
                }
                elseif ($layoutMetadataAdapter->setMetadataFromLayout(ArrayUtil::getArrayValue($_POST, 'layout'), $savableMetadata))
                {
                    if ($designerRules->requireAllRequiredFieldsInLayout())
                    {
                        RequiredAttributesValidViewUtil::
                        setAsContainingRequiredAttributes($moduleClassName, $viewClassName);
                    }
                    echo CJSON::encode(array('message' => $layoutMetadataAdapter->getMessage(), 'type' => 'message'));
                }
                else
                {
                    echo CJSON::encode(array('message' => $layoutMetadataAdapter->getMessage(), 'type' => 'error'));
                }
                Yii::app()->end(0, false);
            }
            $title           = Zurmo::t('DesignerModule', 'Edit Layout') . ': ' . $designerRules->resolveDisplayNameByView($_GET['viewClassName']);
            $breadCrumbLinks = array(
                    $moduleClassName::getModuleLabelByTypeAndLanguage('Plural') . ': ' . Zurmo::t('DesignerModule', 'Layouts') =>
                    array('default/moduleLayoutsList',  'moduleClassName' => $_GET['moduleClassName']),
                $title,
            );
            $canvasView = new MetadataViewEditView(
                        $this->getId(),
                        $this->getModule()->getId(),
                        $_GET['moduleClassName'],
                        $_GET['viewClassName'],
                        $editableMetadata,
                        $designerRules,
                        $attributeCollection,
                        $attributesLayoutAdapter->makeDesignerLayoutAttributes(),
                        $title

            );
            $view = new DesignerPageView(ZurmoDefaultAdminViewUtil::
                            makeViewWithBreadcrumbsForCurrentUser($this, $canvasView, $breadCrumbLinks, 'DesignerBreadCrumbView'));
            echo $view->render();
        }

        public function actionModuleEdit()
        {
            assert('!empty($_GET["moduleClassName"])');
            $module          = new $_GET['moduleClassName'](null, null);
            $moduleClassName = get_class($module);
            $metadata        = $module::getMetadata();
            $adapter         = new ModuleMetadataToFormAdapter($metadata['global'], get_class($module));
            $moduleForm      = $adapter->getModuleForm();
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'edit-form')
            {
                $this->actionModuleValidate($moduleForm);
            }
            if (isset($_POST[get_class($moduleForm)]))
            {
                $this->actionModuleSave($moduleForm, $module);
            }
            $title           = $moduleClassName::getModuleLabelByTypeAndLanguage('Plural') .
                               ': ' . Zurmo::t('Core', 'General');
            $breadCrumbLinks = array($title);
            $canvasView = new ActionBarAndModuleEditView(
                        $this->getId(),
                        $this->getModule()->getId(),
                        $module,
                        $moduleForm,
                        $title
            );
            $view = new DesignerPageView(ZurmoDefaultAdminViewUtil::
                            makeViewWithBreadcrumbsForCurrentUser($this, $canvasView, $breadCrumbLinks, 'DesignerBreadCrumbView'));
            echo $view->render();
        }

        protected function actionModuleValidate($moduleForm)
        {
            echo ZurmoActiveForm::validate($moduleForm);
            Yii::app()->end(0, false);
        }

        protected function actionModuleSave($moduleForm, $module)
        {
            $moduleForm->setAttributes($_POST[get_class($moduleForm)]);
            $adapter = new ModuleFormToMetadataAdapter($module, $moduleForm);
            $adapter->setMetadata();
            Yii::app()->languageHelper->flushModuleLabelTranslationParameters();
            GeneralCache::forgetAll();
            $routeParams = array_merge(
                array('default/modulesMenu'),
                $_GET,
                array('moduleClassName' => get_class($module))
            );
            $this->redirect($routeParams);
        }

        /**
         * Action called from user interface when the attributeName drop down is changed in the edit view for a dropdown
         * dependency.
         */
        public function actionChangeDropDownDependencyAttribute()
        {
            assert('!empty($_GET["moduleClassName"])');
            assert('!empty($_GET["attributeTypeName"])');
            $attributeFormClassName = $_GET['attributeTypeName'] . 'AttributeForm';
            $moduleClassName = $_GET['moduleClassName'];
            $modelClassName  = $moduleClassName::getPrimaryModelName();
            $model = new $modelClassName();
            if (!empty($_GET['attributeName']))
            {
                $attributeForm = AttributesFormFactory::createAttributeFormByAttributeName($model, $_GET["attributeName"]);
                $attributeForm->setModelClassName($modelClassName);
            }
            else
            {
                $attributeForm   = new $attributeFormClassName();
                $attributeForm->setScenario('createAttribute');
                $attributeForm->setModelClassName($modelClassName);
            }
            if (isset($_POST[get_class($attributeForm)]))
            {
                $attributeForm->sanitizeFromPostAndSetAttributes($_POST[get_class($attributeForm)]);
            }
            else
            {
                throw new NotSupportedException();
            }
            $content = DropDownDependencyAttributeEditView::
                       renderContainerAndMappingLayoutContent($attributeForm, $this->getId(), $this->getModule()->getId(), false);
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }
    }
?>
