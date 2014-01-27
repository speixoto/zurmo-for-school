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
            $content  = ZurmoHtml::openTag('div', array('class' => 'draggable-elements-toolbar'));
            $content .= ZurmoHtml::openTag('table');
            $content .= ZurmoHtml::openTag('tr');
            //Put here all element to be draggable
            $image = ZurmoHtml::image('http://zurmo.org/wp-content/themes/Zurmo/images/Zurmo-logo.png');
            $content .= ZurmoHtml::tag('td', array('class' => 'elementToPlace'), $image);
            $content .= ZurmoHtml::closeTag('tr');
            $content .= ZurmoHtml::closeTag('table');
            $content .= ZurmoHtml::closeTag('div');
            return $content;
        }

        protected function renderLayout()
        {
            $content = '
                <table class="container">
                      <tr>
                        <td>

                          <table class="row">
                            <tr>
                              <td class="wrapper">

                                <table class="eight columns">
                                  <tr class="sortable">
                                    <td>

                                      Eight Columns

                                    </td>
                                  </tr>
                                </table>

                              </td>
                              <td class="wrapper last">

                                <table class="four columns">
                                  <tr class="sortable">
                                    <td>

                                      Four Columns

                                    </td>
                                  </tr>
                                </table>

                              </td>
                            </tr>
                          </table>

                        </td>
                      </tr>
                </table>
            ';
            return $content;
        }

        /**
         * Run this widget.
         * This method registers necessary javascript and renders the needed HTML code.
         */
        public function run()
        {
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->id,
                "emailTemplateEditor.init();");
            echo ZurmoHtml::openTag('div', array('id' => 'email-template-editor-container'));
            echo $this->renderElementsToolbar();
            echo $this->renderLayout();
            echo ZurmoHtml::closeTag('div');
        }
    }
?>
