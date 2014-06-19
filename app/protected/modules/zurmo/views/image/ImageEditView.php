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

        /**
         * @param CController $controller
         * @param CFormModel $formModel
         */
        public function __construct(CController $controller, ImageEditForm $formModel, ImageFileModel $model)
        {
            $this->controller   = $controller;
            $this->formModel    = $formModel;
            $this->model        = $model;
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
                    'id'                   => 'image-import-form',
                    'action'               => Yii::app()->controller->createUrl('imageModel/modalEdit', GetUtil::getData()),
                    'enableAjaxValidation' => true,
                    'clientOptions' => array(
                        'validateOnSubmit' => true,
                        'validateOnChange' => false,
                    ),
                )
            );
            $src      = ImageFileModelUtil::getUrlForGetImageFromImageFileName($this->model->getImageCacheFileName());
            $content  = $formStart;
            $content .= ZurmoHtml::image($src, '', array('class' => 'crop-and-resize'));
            $linkOptions = array('onclick'  => "$(this).addClass('attachLoadingTarget').closest('form').submit()",
                                 'class'    => 'secondary-button');
            $content .= ZurmoHtml::link(ZurmoHtml::tag('span', array('class' => 'z-label'), Zurmo::t('Core', 'Save')),
                                        "#", $linkOptions);
            $content .= $this->controller->renderEndWidget();
            return $content;
        }

        protected function renderScripts()
        {
            $assetsPath = Yii::getPathOfAlias('application.modules.zurmo.views.image.assets');
            $assetsUrl = Yii::app()->assetManager->publish($assetsPath);
            Yii::app()->getClientScript()->registerScriptFile($assetsUrl . "/jquery.jrac.js");
            $javaScript = "$('img.crop-and-resize').jrac();";
            Yii::app()->clientScript->registerScript(__CLASS__, $javaScript);
        }
    }
?>