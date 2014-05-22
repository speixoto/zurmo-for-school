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
     * Display an image selection
     */
    class ImageElement extends Element implements ElementActionTypeInterface
    {
        protected $image;

        public function __construct($model, $attribute, $form = null, array $params = array())
        {
            parent::__construct($model, $attribute, $form, $params);
            $this->setImage($this->model->{$this->attribute});
        }

        protected function setImage($imageId)
        {
            $id = (int) $imageId;
            if ($id > 0 )
            {
                try
                {
                    $image = ImageFileModel::getById($id);
                    $this->image = $image;
                }
                catch (NotFoundException $exception)
                {
                    //Do nothing
                }
            }
        }

        protected function renderControlEditable()
        {
//            assert('$this->model->{$this->attribute} instanceof ImageModel');
            $cs = Yii::app()->getClientScript();
            $cs->registerCoreScript('bbq');
            $cs->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.elements.assets')
                ) . '/Modal.js',
                CClientScript::POS_END
            );
            $cs->registerScript(get_class($this), "
                        function replaceImageSummary(id, value)
                        {
                            $('#' + id).html(value);
                        };
                    ");
            $content  = ZurmoHtml::tag('div', array('id' => $this->getIdForPreviewDiv()), $this->renderImageDetails());
            $content .= $this->renderReplaceOrBrowseLink();
            $content .= ZurmoHtml::hiddenField($this->getEditableInputName(), $this->model->{$this->attribute});
            return $content;
        }

        protected function renderControlNonEditable()
        {
            return $this->renderImage();
        }

        public static function getEditableActionType()
        {
            return 'ModalList';
        }

        public static function getNonEditableActionType()
        {
            throw new NotSupportedException;
        }

        protected function renderImageDetails()
        {
            if ($this->image != null)
            {
                $summary = ImageFileModelUtil::getImageSummary($this->image);
                return ZurmoHtml::tag('div', array(), $summary);
            }
            else
            {
                $content = $this->renderImage(true);
                return $content . ZurmoHtml::tag('strong', array(), Zurmo::t('ZurmoModule', 'Upload an Image'));
            }
        }

        protected function renderReplaceOrBrowseLink()
        {
            $id = $this->getIdForSelectLink();
            $linkText = Zurmo::t('ZurmoModule', 'Browse');
            if ($this->image != null)
            {
                $linkText = Zurmo::t('ZurmoModule', 'Replace');
            }
            $content = ZurmoHtml::ajaxLink($linkText . '<span class="z-spinner"></span>',
                            Yii::app()->createUrl('zurmo/imageModel/modalList/', $this->getSelectLinkUrlParams()),
                            $this->resolveAjaxOptionsForSelectingModel($id),
                            array(
                                'id'        => $id,
                                'namespace' => 'selectLink',
                            )
            );
            return $content;
        }

        protected function renderImage($isThumb = false)
        {
            $altText = '';
            $htmlOptions = array();
            if (!$isThumb)
            {
                $altText = $this->getAltText();
                $htmlOptions = $this->getHtmlOptions();
            }
            if ($this->image != null)
            {
                $url = ImageFileModelUtil::getUrlForGetImageFromImageFileName($this->image->getImageCacheFileName(), $isThumb);
            }
            else
            {
                $url = PlaceholderImageUtil::resolvePlaceholderImageUrl();
            }
            return ZurmoHtml::image($url, $altText, $htmlOptions);
        }

        protected function getIdForSelectLink()
        {
            return $this->getEditableInputId($this->attribute, 'SelectLink');
        }

        protected function getSelectLinkUrlParams()
        {
            return array(
                'modalTransferInformation' => $this->getModalTransferInformation(),
            );
        }

        protected function getModalTransferInformation()
        {
            return array(
                'sourceIdFieldId'   => $this->getEditableInputId(),
                'sourceNameFieldId' => $this->getIdForPreviewDiv(),
                'modalId'           => $this->getModalContainerId(),
                'sourceModelId'     => (int) $this->model->{$this->attribute}
            );
        }

        protected function resolveAjaxOptionsForSelectingModel($formId)
        {
            assert('is_string($formId)');
            $title = $this->getModalTitleForSelectingModel();
            return   ModalView::getAjaxOptionsForModalLink($title, $this->getModalContainerId());
        }

        protected function getModalTitleForSelectingModel()
        {
            return Zurmo::t('ZurmoModule', 'Image Search');
        }

        protected function getModalContainerId()
        {
            return ModelElement::MODAL_CONTAINER_PREFIX . '-' . $this->form->id;
        }

        protected function getAltText()
        {
            if (!isset($this->params['alt']))
            {
                return '';
            }
            return $this->params['alt'];
        }

        protected function getIdForPreviewDiv()
        {
            return $this->getEditableInputId($this->attribute, 'preview');
        }
    }
?>