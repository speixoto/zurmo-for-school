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

    class MetadataViewEditView extends View
    {
        protected $controllerId;
        protected $moduleId;
        protected $editableMetadata;
        protected $metadataDisplayName;
        protected $metadataViewClassName;
        protected $designerRules;
        protected $attributeCollection;
        protected $designerLayoutAttributes;
        protected $title;

        public function __construct($controllerId,
            $moduleId,
            $moduleClassName,
            $metadataViewClassName,
            $editableMetadata,
            DesignerRules $designerRules,
            $attributeCollection,
            DesignerLayoutAttributes $designerLayoutAttributes,
            $title
        )
        {
            assert('is_array($editableMetadata)');
            assert('is_array($attributeCollection)');
            $this->controllerId             = $controllerId;
            $this->moduleId                 = $moduleId;
            $this->moduleClassName          = $moduleClassName;
            $this->metadataViewClassName    = $metadataViewClassName;
            $this->editableMetadata         = $editableMetadata;
            $this->designerRules            = $designerRules;
            $this->attributeCollection      = $attributeCollection;
            $this->designerLayoutAttributes = $designerLayoutAttributes;
            $this->title                    = $title;
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        protected function renderContent()
        {
            $content = $this->renderForm();
            $this->renderStickyAnchorScript();
            return $content;
        }

        protected function renderForm()
        {
            $content  = '<div class="wrapper">';
            $content .= $this->renderTitleContent();
            $content .= '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                                'ZurmoActiveForm',
                                                                array_merge(
                                                                    array('id' => 'edit-form'),
                                                                    array('enableAjaxValidation' => false)
                                                                )
                                                            );
            $content .= $formStart;
            $content .= '<div class="designer-toolbar">';
            if ($this->designerRules->canConfigureLayoutPanelsType())
            {
                $content .= $this->renderLayoutPanelsType($form);
            }
            $content .= '</div>';
            $content .= $this->renderDesignerLayoutEditorWidget();
            $content .= '<div class="view-toolbar-container clearfix"><div class="form-toolbar">';
            $content .= $this->renderCancelLink();
            $content .= $this->renderSaveLayoutButton('FlashMessageBar');
            $content .= '</div></div>';

            $formEnd  = $clipWidget->renderEndWidget();
            $content .= $formEnd;
            $content .= '</div></div>';
            return $content;
        }

        protected function renderSaveLayoutButton($notificationBarId)
        {
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.views.assets')
                    ) . '/FormUtils.js',
                CClientScript::POS_END
            );
            $htmlOptions             = array();
            $htmlOptions['id']       = 'saveLayout';
            $htmlOptions['name']     = 'saveLayout';
            $htmlOptions['class']    = 'attachLoading z-button';
            $aContent                = ZurmoHtml::wrapLink(Yii::t('Default', 'Save Layout'));
            return ZurmoHtml::ajaxLink($aContent, '#', array(
                    'data' => 'js:designer.prepareSaveLayout("edit-form")',
                    'dataType' => 'json',
                    'type' => 'POST',
                    'beforeSend' => 'js:function(){attachLoadingOnSubmit("edit-form");}',
                    'complete'   => 'js:function(){detachLoadingOnSubmit("edit-form");}',
                    'success' => 'function(data){designer.updateFlashBarAfterSaveLayout(data, "' . $notificationBarId . '")}', // Not Coding Standard
                    'error' => 'function(data){ ' . // Not Coding Standard
                        'var data = {' . // Not Coding Standard
                        '   "message" : "' . Yii::t('Default', 'There was an error processing your request'). '",
                            "type"    : "error"
                        };
                        designer.updateFlashBarAfterSaveLayout(data, "' . $notificationBarId . '")
                    }',
                ), $htmlOptions);
        }

        protected function renderCancelLink()
        {
            $route = Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/moduleLayoutsList/',
                                                 array('moduleClassName' => $this->moduleClassName));
            return ZurmoHtml::link(ZurmoHtml::wrapLabel(Yii::t('Default', 'Cancel')), $route);
        }

        /**
         * If the metadata's designer rules support a panel configuration type, display that dropdown.
         */
        protected function renderLayoutPanelsType($form)
        {
            $formModel = PanelsDisplayTypeLayoutMetadataUtil::makeFormFromEditableMetadata($this->editableMetadata);
            //$this->editableMetadata populate if it exists.
            $content = null;
            $element  = new LayoutPanelsTypeStaticDropDownElement($formModel, 'type', $form);
            $element->editableTemplate = '{content}';
            $content .= $element->render();
            DropDownUtil::registerScripts();
            return $content;
        }

        protected function renderDesignerLayoutEditorWidget()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("designerLayoutEditor");
            $cClipWidget->widget('application.core.widgets.DesignerLayoutEditor', array(
                'designerLayoutAttributes'      => $this->designerLayoutAttributes,
                'canAddRows'                    => $this->designerRules->canAddRows(),
                'canMoveRows'                   => $this->designerRules->canMoveRows(),
                'canRemoveRows'                 => $this->designerRules->canRemoveRows(),
                'canAddPanels'                  => $this->designerRules->canAddPanels(),
                'canModifyPanelSettings'        => $this->designerRules->canModifyPanelSettings(),
                'canRemovePanels'               => $this->designerRules->canRemovePanels(),
                'canMovePanels'                 => $this->designerRules->canMovePanels(),
                'canModifyCellSettings'         => $this->designerRules->canModifyCellSettings(),
                'canMergeAndSplitCells'         => $this->designerRules->canMergeAndSplitCells(),
                'maxCellsPerRow'                => $this->designerRules->maxCellsPerRow(),
                'viewMetadata'                  => $this->designerRules->formatEditableMetadataForLayoutParsing($this->editableMetadata),
                'mergeRowAndAttributePlacement' => $this->designerRules->mergeRowAndAttributePlacement(),
                'showRequiredAttributeSpan'     => $this->designerRules->requireAllRequiredFieldsInLayout(),
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['designerLayoutEditor'];
        }

        protected function renderStickyAnchorScript()
        {
            Yii::app()->getClientScript()->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.views.assets') . '/StickyUtils.jquery.js'
                ),
                CClientScript::POS_END
            );
$script = <<<EOD
    $(function()
    {
        $(window).scroll({canvasId:'MetadataViewEditView'}, sticky_relocate);
        sticky_relocate({data: { canvasId : 'MetadataViewEditView'}});
    }
    );
EOD;
            Yii::app()->getClientScript()->registerScript('DesignerCanvasScript', $script);
        }
    }
?>