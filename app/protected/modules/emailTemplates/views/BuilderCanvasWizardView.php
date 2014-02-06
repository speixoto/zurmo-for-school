<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    class BuilderCanvasWizardView extends ComponentForEmailTemplateWizardView
    {
        const REFRESH_CANVAS_FROM_SAVED_TEMPLATE_LINK_ID = 'refresh-canvas-from-saved-template';

        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return Zurmo::t('EmailTemplatesModule', 'Canvas');
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'builderCanvasPreviousLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'builderCanvasSaveLink';
        }

        /**
         * @return string
         */
        public static function getFinishLinkId()
        {
            return 'builderCanvasFinishLink';
        }

        protected function renderNextPageLinkLabel()
        {
            return Zurmo::t('Core', 'Save');
        }

        protected function renderFinishLinkLabel()
        {
            return Zurmo::t('Core', 'Finish');
        }

        /**
         * @return string
         */
        protected function renderFormContent()
        {
            // TODO: @Shoaibi: Critical1: Load left sidebar and canvas here.
            // TODO: @Shoaibi: Critical1: Hidden elements for all serializedData Indexes.
            $leftSideContent                            =  null;
            $hiddenElements                             = null;

            $this->renderRefreshCanvasLink($leftSideContent);
            $this->renderHiddenElements($hiddenElements, $leftSideContent);

            $content                                    = $this->renderLeftAndRightSideBarContentWithWrappers($leftSideContent);
            return $content;
        }

        protected function renderRefreshCanvasLink(& $content)
        {
            $linkContent    = ZurmoHtml::link('Reload Canvas', '#', array(
                                                            'id' => static::REFRESH_CANVAS_FROM_SAVED_TEMPLATE_LINK_ID,
                                                            'style' => 'display:none',
                                                        ));
            $this->wrapContentInTableCell($linkContent, array('colspan' => 2));
            $this->wrapContentInTableRow($linkContent);
            $content            .= $linkContent;
        }

        protected function renderActionLinksContent()
        {
            $content    = parent::renderActionLinksContent();
            $content    .= $this->renderFinishLinkContent();
            return $content;
        }

        protected function renderFinishLinkContent()
        {
            $params                = array();
            $params['label']       = $this->renderFinishLinkLabel();
            $params['htmlOptions'] = array('id' => static::getFinishLinkId(),
                                            'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $element               = new SaveButtonActionElement(null, null, null, $params);
            return $element->render();
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            $this->registerRefreshCanvasFromSavedTemplateScript();
            $this->registerSetIsDraftToZeroOnClickingFinishScript();
        }

        protected function registerRefreshCanvasFromSavedTemplateScript()
        {
            Yii::app()->clientScript->registerScript('refreshCanvasFromSavedTemplateScript', "
                $('#" . static::REFRESH_CANVAS_FROM_SAVED_TEMPLATE_LINK_ID . "').unbind('click');
                $('#" . static::REFRESH_CANVAS_FROM_SAVED_TEMPLATE_LINK_ID . "').bind('click', function()
                {
                    // TODO: @Shoaibi: Critical2: Implement to refresh canvas div by making ajax to a url with templateId
                    return false;
                });
                ", CClientScript::POS_READY);
        }

        protected function registerSetIsDraftToZeroOnClickingFinishScript()
        {
            Yii::app()->clientScript->registerScript('setIsDraftToZeroOnClickingFinishScript', "
                $('#" . static::getFinishLinkId() . "').unbind('click.setIsDraftToZero');
                $('#" . static::getFinishLinkId() . "').bind('click.setIsDraftToZero', function()
                {
                    setIsDraftToZero();
                });
                ", CClientScript::POS_END);
        }
    }
?>