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

    class BuilderCanvasView
    {
        public function render()
        {
            static::registerScripts();
            $content = $this->renderCanvasContent();
            Yii::app()->getClientScript()->render($content);
            return $content;
        }

        protected function renderCanvasContent()
        {
            $canvasContent = '
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml">
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                    <meta name="viewport" content="width=device-width"/>

                        <link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
                        <style type="text/css">
                             /*{ margin: 0; padding: 0; }*/
                             .sortable-rows > table{ background: pink }
                             .sortable-elements{ background: gold }
                             table{ border:1px solid black; width: 100%}
                             .ui-state-hover{border: 2px solid blue}
                        </style>
                      </head>
                      <body>
                        <table class="body">
                            <tr>
                                <td class="sortable-rows" align="center" valign="top">

                                        <table class="row header">
                                            <tr>
                                                <td>
                                                    <span class="ui-icon-arrow-4"></span>
                                                    <span class="ui-icon-wrench"></span >
                                                    <span class="ui-icon-trash"></span>
                                                    <table class="container" data-row-id="1">
                                                        <tr>
                                                            <td class="wrapper last">
                                                                <table class="twelve columns">
                                                                    <tr>
                                                                        <td  class="sortable-elements">
                                                                            <div>
                                                                                <span class="ui-icon-arrow-4"></span>
                                                                                <span class="ui-icon-wrench"></span >
                                                                                <span class="ui-icon-trash"></span>
                                                                                <img alt="" src="http://storage7.static.itmages.com/i/14/0128/h_1390936062_8252966_876506e8ff.png">
                                                                            </div>
                                                                        </td>
                                                                        <td class="expander"></td>
                                                                    </tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>

                                        <table class="container">
                                              <tr>
                                                 <td>
                                                  <span class="ui-icon-arrow-4"></span>
                                                  <span class="ui-icon-wrench"></span >
                                                  <span class="ui-icon-trash"></span>
                                                  <table class="row" data-row-id="2">
                                                    <tr>

                                                      <td class="wrapper">

                                                        <table class="six columns" data-column-id="1">
                                                          <tr>
                                                            <td class="sortable-elements">
                                                                <div>
                                                                    <span class="ui-icon-arrow-4"></span>
                                                                    <span class="ui-icon-wrench"></span >
                                                                    <span class="ui-icon-trash"></span>
                                                                    <img alt="" src="http://storage7.static.itmages.com/i/14/0128/h_1390936062_8252966_876506e8ff.png">
                                                                </div>
                                                            </td>
                                                            <td class="expander"></td>
                                                          </tr>
                                                        </table>

                                                      </td>

                                                      <td class="wrapper last">

                                                        <table class="six columns" data-column-id="2">
                                                          <tr>
                                                            <td class="sortable-elements">
                                                                <div>
                                                                    <span class="ui-icon-arrow-4"></span>
                                                                    <span class="ui-icon-wrench"></span >
                                                                    <span class="ui-icon-trash"></span>
                                                                    <img alt="" src="http://storage7.static.itmages.com/i/14/0128/h_1390936062_8252966_876506e8ff.png">
                                                                </div>
                                                            </td>
                                                            <td class="expander"></td>
                                                          </tr>
                                                        </table>

                                                      </td>

                                                    </tr>
                                                  </table>

                                                </td>
                                              </tr>
                                        </table>
                                </td>
                            </tr>
                        </table>
                    </html>
            ';
            return $canvasContent;
        }

        public static function registerScripts()
        {
            Yii::app()->clientScript->registerCoreScript('jquery');
            Yii::app()->clientScript->registerCoreScript('jquery.ui');
        }
    }
?>