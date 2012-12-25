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

    abstract class ReportWizardView extends View
    {
        protected $model;

        abstract protected function registerClickFlowScript();

        abstract protected function renderContainingViews(ReportActiveForm $form);

        abstract protected function renderConfigSaveAjax($formName);

        public function __construct(ReportWizardForm $model)
        {
            $this->model = $model;
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        public static function getFormId()
        {
            return 'edit-form';
        }

        public function getTitle()
        {
            return Yii::t('Default', 'Report Wizard');
        }

        protected function renderContent()
        {
            $content  = $this->renderForm();
            $this->registerScripts();
            $this->registerCss();
            return $content;
        }

        protected function renderForm()
        {
            $content  = '<div class="wrapper">';
            $content .= $this->renderTitleContent();
            $content .= '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                            'ReportActiveForm',
                                                            array('id'                      => static::getFormId(),
                                                                  'action'                  => $this->getFormActionUrl(),
                                                                  'enableAjaxValidation'    => true,
                                                                  'clientOptions'           => $this->getClientOptions(),
                                                                  'modelClassNameForError'  => get_class($this->model))
                                                            );
            $content .= $formStart;
            $content .= static::renderValidationScenarioInputContent();
            $content .= $this->renderContainingViews($form);
            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= '</div></div>';
            return $content;
        }

        protected function getClientOptions()
        {
            return array(
                        'validateOnSubmit'  => true,
                        'validateOnChange'  => false,
                        'beforeValidate'    => 'js:beforeValidateAction',
                        'afterValidate'     => 'js:afterValidateAjaxAction',
                        'afterValidateAjax' => $this->renderConfigSaveAjax(static::getFormId()),
                    );
        }

        protected function getFormActionUrl()
        {
            return Yii::app()->createUrl('reports/default/save',
                                         array('type' => $this->model->type, 'id' => $this->model->id));
        }

        protected function registerScripts()
        {
            Yii::app()->getClientScript()->registerCoreScript('treeview');
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.modules.reports.views.assets')) . '/ReportUtils.js');
            $this->registerClickFlowScript();
        }

        protected function registerCss()
        {
            Yii::app()->getClientScript()->registerCssFile(Yii::app()->getClientScript()->getCoreScriptUrl() .
                                                           '/treeview/jquery.treeview.css');
        }

        protected static function renderValidationScenarioInputContent()
        {
            $idInputHtmlOptions  = array('id' => static::getValidationScenarioInputId());
            $hiddenInputName     = 'validationScenario';
            return ZurmoHtml::hiddenField($hiddenInputName, static::getStartingValidationScenario(), $idInputHtmlOptions);
        }

        protected static function getStartingValidationScenario()
        {
            return ReportWizardForm::MODULE_VALIDATION_SCENARIO;
        }

        protected static function getValidationScenarioInputId()
        {
            return 'componentType';
        }

        protected function getSaveAjaxString($formName)
        {
            $saveRedirectToDetailsUrl = Yii::app()->createUrl('reports/default/details');
            $saveRedirectToListUrl    = Yii::app()->createUrl('reports/default/list');
            return ZurmoHtml::ajax(array(
                                            'type'     => 'POST',
                                            'data'     => 'js:$("#' . $formName . '").serialize()',
                                            'url'      =>  $this->getFormActionUrl(),
                                            'dataType' => 'json',
                                            'success'  => 'js:function(data){
                                                if(data.redirectToList)
                                                {
                                                    url = "' . $saveRedirectToListUrl . '";
                                                }
                                                else
                                                {
                                                    url = "' . $saveRedirectToDetailsUrl . '" + "?id=" + data.id
                                                }
                                                window.location.href = url;
                                            }'
                                          ));
        }

        protected function renderTreeViewAjaxScriptContent($formName, $componentViewClassName)
        {
            assert('is_string($formName)');
            assert('is_string($componentViewClassName)');
            $url    =  Yii::app()->createUrl('reports/default/relationsAndAttributesTree',
                       array_merge($_GET, array('type' => $this->model->type,
                                                'treeType' => $componentViewClassName::getTreeType())));
            $script = "
                $('#" . FiltersForReportWizardView::getTreeDivId() . "').addClass('loading');
                makeLargeLoadingSpinner('" . $componentViewClassName::getTreeDivId() . "');
                $.ajax({
                    url : '" . $url . "',
                    type : 'POST',
                    data : $('#" . $formName . "').serialize(),
                    success : function(data)
                    {
                        $('#" . $componentViewClassName::getTreeDivId() . "').html(data);
                    },
                    error : function()
                    {
                        //todo: error call
                    }
                });
            ";
            return $script;
        }
    }
?>