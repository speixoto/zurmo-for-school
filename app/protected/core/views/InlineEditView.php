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

    /**
     * Abstraction for displaying an inline display of a model edit view.
     */
    abstract class InlineEditView extends EditView
    {
        /**
         * Action id to use by ajax for validating and saving the model.
         * @var string
         */
        protected $saveActionId;

        /**
         * Parameters to pass in the url for validation any actions called.
         * @var array
         */
        protected $urlParameters;

        /**
         * Unique identifier used to identify this view on the page.
         * @var string
         */
        protected $uniquePageId;

        public function __construct(RedBeanModel $model, $controllerId, $moduleId, $saveActionId, $urlParameters, $uniquePageId)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($saveActionId)');
            assert('is_array($urlParameters)');
            assert('is_string($uniquePageId) || $uniquePageId == null');
            $this->model              = $model;
            $this->modelClassName     = get_class($model);
            $this->controllerId       = $controllerId;
            $this->moduleId           = $moduleId;
            $this->saveActionId       = $saveActionId;
            $this->urlParameters      = $urlParameters;
            $this->uniquePageId       = $uniquePageId;
        }

        protected function renderContent()
        {
            $formName = $this->getFormName();
            $afterValidateAjax = $this->renderConfigSaveAjax($formName);
            $content = '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                'ZurmoActiveForm',
                array(
                    'id' => $formName,
                    'action' => $this->getValidateAndSaveUrl(),
                    'enableAjaxValidation' => true,
                    'clientOptions' => array(
                        'validateOnSubmit'  => true,
                        'validateOnChange'  => false,
                        'beforeValidate'    => 'js:beforeValidateAction',
                        'afterValidate'     => 'js:afterValidateAjaxAction',
                        'afterValidateAjax' => $afterValidateAjax,
                    ),
                )
            );

            $cs = Yii::app()->getClientScript();
            $cs->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.elements.assets')
                    ) . '/Modal.js',
                CClientScript::POS_END
            );
            $content .= $formStart;
            $content .= $this->renderFormLayout($form);
            $content .= $this->renderAfterFormLayout($form);
            $actionElementContent = $this->renderActionElementBar(true);
            if ($actionElementContent != null)
            {
                $content .= '<div class="view-toolbar-container clearfix"><div class="form-toolbar clearfix">';
                $content .= $actionElementContent;
                $content .= '</div></div>';
            }
            $formEnd = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= '</div>';
            return $content;
        }

        public function getFormName()
        {
            return "inline-edit-form";
        }

        protected function renderConfigSaveAjax($formName)
        {
            return ZurmoHtml::ajax(array(
                    'type' => 'POST',
                    'data' => 'js:$("#' . $formName . '").serialize()',
                    'url'  =>  $this->getValidateAndSaveUrl(),
                    'update' => '#' . $this->uniquePageId,
                ));
        }

        protected function getValidateAndSaveUrl()
        {
            return Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/' . $this->saveActionId, $this->urlParameters);
        }

        public function isUniqueToAPage()
        {
            return false;
        }

        public function getPostArrayName()
        {
            return get_class($this->model);
        }

        public function setMetadataFromPost($postArray)
        {
            $this->model->setAttributes($postArray);
        }

        public function validate()
        {
            echo ZurmoActiveForm::validate($this->model);
        }

        public static function getDesignerRulesType()
        {
            return 'InlineEditView';
        }

        public function getViewMetadata()
        {
            return $this->model->getAttributes();
        }

        protected function getMorePanelsLinkLabel()
        {
            return Yii::t('Default', 'More Options');
        }

        protected function getLessPanelsLinkLabel()
        {
            return Yii::t('Default', 'Fewer Options');
        }

        public static function getDisplayDescription()
        {
            return null;
        }
    }
?>