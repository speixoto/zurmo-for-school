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

    /**
     * Class ImageEditView
     */
    class ImageEditView extends View
    {
        private $controller;

        private $formModel;

        private $model;

        private $modalListLinkProvider;

        /**
         * @param CController $controller
         * @param CFormModel $formModel
         */
        public function __construct(CController $controller, ImageEditForm $formModel, ImageFileModel $model, $modalListLinkProvider)
        {
            $this->controller               = $controller;
            $this->formModel                = $formModel;
            $this->model                    = $model;
            $this->modalListLinkProvider    = $modalListLinkProvider;
        }

        /**
         * Renders the view content.
         */
        protected function renderContent()
        {
            $content = $this->renderForm();
            return $content;
        }

        protected function renderForm()
        {
            $this->renderScripts();
            list($form, $formStart) = $this->controller->renderBeginWidget(
                'ZurmoActiveForm',
                array(
                    'id'                   => 'image-edit-form',
                    'enableAjaxValidation' => true,
                    'clientOptions' => array(
                        'validateOnSubmit'  => true,
                        'validateOnChange'  => false,
                        'beforeValidate'    => 'js:$(this).beforeValidateAction',
                        'afterValidate'     => 'js:$(this).afterValidateAjaxAction',
                        'afterValidateAjax' => $this->renderConfigSaveAjax(),
                    ),
                )
            );
            $src      = ImageFileModelUtil::getUrlForGetImageFromImageFileName($this->model->getImageCacheFileName());
            $content  = $formStart;
            $content .= ZurmoHtml::openTag('div', array('class' => 'form-inputs'));
            $content .= $form->labelEx ($this->formModel, 'cropX');
            $content .= $form->urlField($this->formModel, 'cropX');
            $content .= $form->error   ($this->formModel, 'cropX');
            $content .= $form->labelEx ($this->formModel, 'cropY');
            $content .= $form->urlField($this->formModel, 'cropY');
            $content .= $form->error   ($this->formModel, 'cropY');
            $content .= $form->labelEx ($this->formModel, 'cropWidth');
            $content .= $form->urlField($this->formModel, 'cropWidth');
            $content .= $form->error   ($this->formModel, 'cropWidth');
            $content .= $form->labelEx ($this->formModel, 'cropHeight');
            $content .= $form->urlField($this->formModel, 'cropHeight');
            $content .= $form->error   ($this->formModel, 'cropHeight');
            $content .= $form->labelEx ($this->formModel, 'imageWidth');
            $content .= $form->urlField($this->formModel, 'imageWidth');
            $content .= $form->error   ($this->formModel, 'imageWidth');
            $content .= $form->labelEx ($this->formModel, 'imageHeight');
            $content .= $form->urlField($this->formModel, 'imageHeight');
            $content .= $form->error   ($this->formModel, 'imageHeight');
            $content .= ZurmoHtml::closeTag('div');
            $content .= ZurmoHtml::image($src, '', array('class' => 'crop-and-resize'));
            $linkOptions = array('onclick'  => "$(this).addClass('attachLoadingTarget').closest('form').submit()",
                                 'class'    => 'secondary-button');
            $content .= ZurmoHtml::link(ZurmoHtml::tag('span', array('class' => 'z-label'), Zurmo::t('Core', 'Save')),
                                        "#", $linkOptions);
            $content .= $this->controller->renderEndWidget();
            return $content;
        }

        protected function renderConfigSaveAjax()
        {
            return ZurmoHtml::ajax(array(
                'url'      => "js:$('#image-edit-form').attr('action')",
                'type'     => 'POST',
                'data'     => "js:$('#image-edit-form').serialize()",
                'success'  => "function(data)
                              {
                                var dataObject = jQuery.parseJSON(data);
                                transferModalValues('#{$this->modalListLinkProvider->getModalId()}',
                                                    { {$this->modalListLinkProvider->getSourceIdFieldId()}: dataObject.id});
                                replaceImageSummary('{$this->modalListLinkProvider->getSourceNameFieldId()}', dataObject.summary);
                              }",
            ));
        }

        protected function renderScripts()
        {
            $assetsPath = Yii::getPathOfAlias('application.modules.zurmo.views.image.assets');
            $assetsUrl = Yii::app()->assetManager->publish($assetsPath);
            Yii::app()->getClientScript()->registerScriptFile($assetsUrl . "/jquery.jrac.js");
            Yii::app()->getClientScript()->registerCssFile($assetsUrl . "/style.jrac.css");
            $javaScript = "$('img.crop-and-resize').jrac({
                'crop_width': {$this->formModel->cropWidth},
                'crop_height': {$this->formModel->cropHeight},
                'crop_left': {$this->formModel->cropX},
                'crop_top': {$this->formModel->cropY},
                'image_width': {$this->formModel->imageWidth},
                'image_height': {$this->formModel->imageHeight},
                'zoom_min': 10,
                'viewport_onload': function() {
                  var \$viewport = this;
                  var inputs = $('#ImageEditView').find('.form-inputs input');
                  var events = ['jrac_crop_x','jrac_crop_y','jrac_crop_width','jrac_crop_height','jrac_image_width','jrac_image_height'];
                  for (var i = 0; i < events.length; i++) {
                    var event_name = events[i];
                    // Register an event with an element.
                    \$viewport.observator.register(event_name, inputs.eq(i));
                    // Attach a handler to that event for the element.
                    inputs.eq(i).bind(event_name, function(event, \$viewport, value) {
                      $(this).val(value);
                    })
                    // Attach a handler for the built-in jQuery change event, handler
                    // which read user input and apply it to relevent viewport object.
                    .change(event_name, function(event) {
                      var event_name = event.data;
                      \$viewport.\$image.scale_proportion_locked = \$viewport.\$container.parent('.pane').find('.coords input:checkbox').is(':checked');
                      \$viewport.observator.set_property(event_name,$(this).val());
                    })
                  }
                }
            });";
            Yii::app()->clientScript->registerScript(__CLASS__, $javaScript);
        }
    }
?>