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
            static::registerCss();
            $content = $this->renderCanvasContent();
            Yii::app()->getClientScript()->render($content);
            return $content;
        }

        protected function renderCanvasContent()
        {
            $image = Yii::app()->themeManager->baseUrl . '/default/images/zurmo-zapier.png';
            $canvasContent = '
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml">
                      <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <meta name="viewport" content="width=device-width"/>
                        <link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">' .
                        $this->renderIconFont() .
                        $this->renderLess() .
                        '<style type="text/css">
                             /*{ margin: 0; padding: 0; }
                             .sortable-rows > table{ background: pink }
                             .sortable-elements{ background: gold }
                             table{ border:1px solid black; width: 100%}
                             .state-hover{border: 2px solid blue}*/
                        </style>
                      </head>
                      <body>
                        <table class="body">
                            <tr>
                                <td class="sortable-rows" align="center" valign="top">

                                        <table class="row header">
                                            <tr>
                                                <td>
                                                    <div class="email-template-container-tools">
                                                        <span><i class="icon-move"></i></span>
                                                        <span><i class="icon-gear"></i></span>
                                                        <span><i class="icon-trash"></i></span>
                                                    </div>
                                                    <table class="container" data-row-id="1">
                                                        <tr>
                                                            <td class="wrapper last">
                                                                <table class="twelve columns">
                                                                    <tr>
                                                                        <td  class="sortable-elements">
                                                                            <div>
                                                                                <div class="email-template-container-tools">
                                                        <span><i class="icon-move"></i></span>
                                                        <span><i class="icon-gear"></i></span>
                                                        <span><i class="icon-trash"></i></span>
                                                    </div>
                                                                                <img alt="" src="'.$image.'">
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
                                                  <div class="email-template-container-tools">
                                                        <span><i class="icon-move"></i></span>
                                                        <span><i class="icon-gear"></i></span>
                                                        <span><i class="icon-trash"></i></span>
                                                    </div>
                                                  <table class="row" data-row-id="2">
                                                    <tr>

                                                      <td class="wrapper">

                                                        <table class="six columns" data-column-id="1">
                                                          <tr>
                                                            <td class="sortable-elements">
                                                                <div>
                                                                    <div class="email-template-container-tools">
                                                        <span><i class="icon-move"></i></span>
                                                        <span><i class="icon-gear"></i></span>
                                                        <span><i class="icon-trash"></i></span>
                                                    </div>
                                                                    <img alt="" src="'.$image.'">
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
                                                                   <div class="email-template-container-tools">
                                                        <span><i class="icon-move"></i></span>
                                                        <span><i class="icon-gear"></i></span>
                                                        <span><i class="icon-trash"></i></span>
                                                    </div>
                                                                    <img alt="" src="'.$image.'">
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

        public static function registerCss()
        {
            $baseScriptUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.modules.emailTemplates.widjets.assets'));
            Yii::app()->getClientScript()->registerCssFile($baseScriptUrl . '/EmailTemplateEditor.css');
        }

        protected function renderIconFont(){
            $publishedAssetsPath = Yii::app()->assetManager->publish(Yii::getPathOfAlias("application.core.views.assets.fonts"));
            $iconsFont = "<style>" .
                "@font-face" .
                "{" .
                "font-family: 'zurmo_gamification_symbly_rRg';" .
                "src: url('{$publishedAssetsPath}/zurmogamificationsymblyregular-regular-webfont.eot');" .
                "src: url('{$publishedAssetsPath}/zurmogamificationsymblyregular-regular-webfont.eot?#iefix') format('embedded-opentype'), " .
                "url('{$publishedAssetsPath}/zurmogamificationsymblyregular-regular-webfont.woff') format('woff'), " .
                "url('{$publishedAssetsPath}/zurmogamificationsymblyregular-regular-webfont.ttf') format('truetype'), " .
                "url('{$publishedAssetsPath}/zurmogamificationsymblyregular-regular-webfont.svg#zurmo_gamification_symbly_rRg') format('svg');" .
                "font-weight: normal;" .
                "font-style: normal;" .
                "unicode-range: U+00-FFFF;" . // Not Coding Standard
                "}" .
                "</style>";
            return $iconsFont;
        }

        protected function renderLess(){
            $baseUrl = Yii::app()->themeManager->baseUrl . '/default';
            $publishedAssetsPath = Yii::app()->assetManager->publish(Yii::getPathOfAlias("application.core.views.assets"));
            $less = '<link rel="stylesheet/less" type="text/css" id="default-theme" href="' . $baseUrl . '/less/builder-iframe-tools.less"/>
                     <script type="text/javascript" src="' . $publishedAssetsPath . '/less-1.2.0.min.js"></script>';
            return $less;
        }

    }
?>