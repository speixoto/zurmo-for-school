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

    Yii::import('zii.widgets.jui.CJuiWidget');

    /**
     * Widget for showing email template editor user interface.
     */
    class EmailTemplateEditor extends CJuiWidget
    {
        /**
         *  Initialize the class
         */
        public function init()
        {
            $this->registerScripts();
            parent::init();
        }

        protected function registerScripts()
        {
            $baseScriptUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.modules.emailTemplates.widjets.assets'));
            $cs = Yii::app()->getClientScript();
            $cs->registerScriptFile($baseScriptUrl . '/EmailTemplateEditor.js', CClientScript::POS_END);
        }

        protected function renderElementsToolbar()
        {
            $content  = ZurmoHtml::openTag('ul', array('id' => 'building-blocks', 'class' => 'clearfix'));
            //Put here all element to be draggable
            $content .= ZurmoHtml::tag('li', array('data-class' => 'TextElement'), $this->renderPlacebleElement('Text Element', 'z'));
            $content .= ZurmoHtml::tag('li', array('data-class' => 'ImageElement'), $this->renderPlacebleElement('Image Element', '¿'));
            $content .= ZurmoHtml::tag('li', array('data-class' => 'SocialItemsElement'), $this->renderPlacebleElement('Social Items Element', '“'));
            $content .= ZurmoHtml::closeTag('ul');
            return $content;
        }

        protected function renderPlacebleElement($captionName, $icon)
        {
            $span = ZurmoHtml::tag('span', array(), $captionName);
            $icon = ZurmoHtml::tag('i', array('class' => 'icon-z'), $icon);
            return ZurmoHtml::tag('div', array('class' => 'clearfix'), $icon . $span);
        }

        protected function renderLayout()
        {
            $iframeUrl = Yii::app()->createUrl('emailTemplates/default/renderCanvas');
            $content   = "<iframe id='preview-template' width='100%' height='100%' frameborder='0' src='{$iframeUrl}'>";
            $content  .= "</iframe>";
            return $content;
        }

        /**
         * Run this widget.
         * This method registers necessary javascript and renders the needed HTML code.
         */
        public function run()
        {
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->id,
                "$(document).ready(function(){
                    emailTemplateEditor.init(
                        '#building-blocks',
                        '{$this->getRowWrapper()}',
                        '{$this->getNewElementUrl()}'
                    );
                });");
            echo ZurmoHtml::openTag('div', array('id' => 'builder', 'class' => 'strong-right clearfix'));
            echo ZurmoHtml::tag('span', array('class' => 'z-spinner'), '');
            echo ZurmoHtml::tag('div', array('class' => 'left-column'), $this->renderElementsToolbar());
            echo ZurmoHtml::tag('div', array('class' => 'right-column'), $this->renderLayout());
            echo ZurmoHtml::closeTag('div');
        }

        protected function getRowWrapper()
        {
            return '<table class="container"><tr><td><table class="row" data-row-id="2"><tr>' .
                   '<td class="wrapper last"><table class="twelve columns" data-column-id="new"><tr>' .
                   '<td class="sortable-elements"></td><td class="expander"></td></tr></table></td>' .
                   '</tr></table></td></tr></table>';
        }

        protected function getNewElementUrl()
        {
            return Yii::app()->createUrl('emailTemplates/default/renderElementNonEditable');
        }
    }
?>
