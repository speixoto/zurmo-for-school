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
            assert('is_int($model->{$attribute}) || is_string($model->{$attribute})');
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
            $cs = Yii::app()->getClientScript();
            $cs->registerCoreScript('bbq');
            $cs->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.elements.assets')
                ) . '/Modal.js',
                CClientScript::POS_END
            );
            $this->renderQtipForPreviewImage();
            $applyScript = null;
            if ($this->getApplyLinkId() != null)
            {
                $applyScript = "$('#{$this->getApplyLinkId()}').click();";
            }
            $cs->registerScript(get_class($this), "
                        function replaceImageSummary(id, value)
                        {
                            $('#' + id).html(value);
                            {$applyScript}
                        };
                    ");
            $content  = ZurmoHtml::tag('div', array('id' => $this->getIdForPreviewDiv()), $this->renderImageDetails());
            $content .= $this->renderReplaceOrBrowseLink();
            $content .= ZurmoHtml::hiddenField($this->getEditableInputName(), $this->model->{$this->attribute});
            return $content;
        }

        protected function renderQtipForPreviewImage()
        {
            $qtipOptions = array(
                    'position' => array(
                        'my' => 'center left',
                        'at' => 'top right',
                        'target' => 'mouse',
	                    'adjust' => array(
		                    'x' => 10
	                    )
                    ),
                    'show' => array(
                        'solo' => true,
                        'modal' => false,
                        'ready' => true,
	                    'effect' => 'js:function(offset){$(this).fadeIn(200);}'
                    ),
	                'hide' => array(
		                'effect' => 'js:function(offset){$(this).fadeOut(200);}'
	                ),
	                'style' => array(
		                'classes' => 'builder-image-element-qtip'
	                ),
                    'overwrite' => false,
                    'content' => array(
	                    'text' => "js:function(event, api) {
                                $.ajax({
                                    url: $(this).data('url')
                                })
                                .then(function(content) {
                                    api.set('content.text', content);
                                }, function(xhr, status, error) {
                                    api.set('content.text', status + ': ' + error);
                                });
                            }",
                    )
            );
            ZurmoTip::qtip2('.builder-uploaded-image-thumb > img', $qtipOptions, true, true);
        }

        protected function renderControlNonEditable()
        {
            if ($this->image instanceof ImageFileModel || (int) ($this->model->{$this->attribute}) > 0 || $this->model->{$this->attribute} == '')
            {
                return $this->renderImage();
            }
            else
            {
                return $this->model->{$this->attribute};
            }
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
                $summary = ImageFileModelUtil::getImageSummary($this->image, $this->getDefaultLayout());
                return $summary;//ZurmoHtml::tag('div', array(), $summary);
            }
            else
            {
                $content = $this->renderImage(true);
                return $content;
            }
        }

	    protected function getDefaultLayout()
	    {
		    $createdByLabel = Zurmo::t('ZurmoModule', 'Created by');
		    $onLabel        = Zurmo::t('ZurmoModule', 'on');
		    return '<div class="builder-uploaded-image-thumb">{image}'.$this->renderReplaceOrBrowseLink().'</div><div class="builder-image-details">' .
		           '<strong>{name}</strong><br />{size} · {dimensions} · ' . $createdByLabel .
		           ' {creator} ' . $onLabel . ' {createdTime}</div>';
	    }



        protected function renderReplaceOrBrowseLink()
        {
            $id         = $this->getIdForSelectLink();
            $title      = ZurmoHtml::tag('strong', array(), Zurmo::t('ZurmoModule', 'Upload an Image')) . '<br>';
            $linkText   = Zurmo::t('ZurmoModule', 'Browse');
            if ($this->image != null)
            {
                $title = '';
                $linkText = Zurmo::t('ZurmoModule', 'Change');
            }
            $content = ZurmoHtml::ajaxLink(
	                        '<span class="z-spinner"></span>' . ZurmoHtml::tag('span', array('class' => 'z-label'), $linkText),
                            Yii::app()->createUrl('zurmo/imageModel/modalList/', $this->getSelectLinkUrlParams()),
                            $this->resolveAjaxOptionsForSelectingModel($id),
                            array('id' => $id, 'namespace' => 'selectLink', 'class' => 'secondary-button'));
            $content .= $this->renderEditImageLink();
            return $title . $content;
        }


        protected function registerImageModalEditScript()
        {
            $sourceId = BuilderCanvasWizardView::ELEMENT_EDIT_CONTAINER_ID;
            $modalId = $this->getModalContainerId();
            $url = Yii::app()->createUrl('zurmo/imageModel/modalEdit/', $this->getSelectLinkUrlParams());
            $ajaxOptions = $this->resolveAjaxOptionsForEditingModel();

            $ajaxOptions['beforeSend'] = new CJavaScriptExpression($ajaxOptions['beforeSend']);
            $script = " $(document).off('click.imageEditLink', '#{$sourceId} .image-detail-link');
                        $(document).on('click.imageEditLink',  '#{$sourceId} .image-detail-link', function()
                        {
                            var id = $('#{$this->getEditableInputId()}').val();
                            $.ajax(
                            {
                                'type' : 'GET',
                                'url'  : '{$url}' + '&id=' + id,
                                'beforeSend' : {$ajaxOptions['beforeSend']},
                                'update'     : '{$ajaxOptions['update']}',
                                'success': function(html){jQuery('#{$modalId}').html(html)}
                            });
                            return false;
                          }
                        );";
            Yii::app()->clientScript->registerScript('imageModalEditScript' . $sourceId, $script);
        }

        protected function renderEditImageLink()
        {
            $this->registerImageModalEditScript();
            $id = $this->getIdForEditLink();
            if ($this->image != null)
            {
                $editText = Zurmo::t('Core', 'Edit');
                $content  = ZurmoHtml::link(
                    '<span class="z-spinner"></span>' . ZurmoHtml::tag('span', array('class' => 'z-label'), $editText),
                    '#',
                    array('id' => $id, 'namespace' => 'editLink', 'class' => 'secondary-button image-detail-link'));
                return $content;
            }
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

        protected function getHtmlOptions()
        {
            $htmlOptions = parent::getHtmlOptions();
            if ($this->image != null)
            {
                if (!isset($htmlOptions['width']))
                {
                    $htmlOptions['width'] = $this->image->width;
                }
                if (!isset($htmlOptions['height']))
                {
                    $htmlOptions['height'] = $this->image->height;
                }
            }
            return $htmlOptions;
        }

        protected function getIdForSelectLink()
        {
            return $this->getEditableInputId($this->attribute, 'SelectLink');
        }

        protected function getIdForEditLink()
        {
            return $this->getEditableInputId($this->attribute, 'EditLink');
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

        protected function resolveAjaxOptionsForEditingModel()
        {
            $title = $this->getModalTitleForEditingModel();
            return   ModalView::getAjaxOptionsForModalLink($title, $this->getModalContainerId(), 'auto',
                                                           600, 'center top+25', "'image-edit-modal'");
        }

        protected function getModalTitleForSelectingModel()
        {
            return Zurmo::t('ZurmoModule', 'Select an Image');
        }

        protected function getModalTitleForEditingModel()
        {
            return Zurmo::t('ZurmoModule', 'Edit Image');
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

        protected function getApplyLinkId()
        {
            if (!isset($this->params['applyLinkId']))
            {
                return null;
            }
            return $this->params['applyLinkId'];
        }
    }
?>