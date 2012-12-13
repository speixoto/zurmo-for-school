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

    /**
     * A view that displays a list of supported languages in the application.
     *
     */
    class LanguagesCollectionView extends MetadataView
    {
        protected $controllerId;

        protected $moduleId;

        protected $languagesData;

        public function __construct($controllerId, $moduleId, $languagesData, $messageBoxContent = null)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_array($languagesData)');
            assert('$messageBoxContent == null || is_string($messageBoxContent)');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->languagesData           = $languagesData;
            $this->messageBoxContent      = $messageBoxContent;
        }

        protected function renderContent()
        {
            $content = '<div>';
            $content .= $this->renderTitleContent();
            $content .= '<div class="wide form">';
            $clipWidget = new ClipWidget();
            list($form, $formStart) = $clipWidget->renderBeginWidget(
                                                                'ZurmoActiveForm',
                                                                array('id' => 'language-collection-form',
                                                                        'htmlOptions' =>
                                                                            array('onSubmit' =>
                                                                                        'js:return attachLoadingOnSubmit("language-collection-form")')
                                                                )
                                                            );
            $content .= $formStart;

            if ($this->messageBoxContent != null)
            {
                $content .= $this->messageBoxContent;
                $content .= '<br/>';
            }
            $content .= $this->renderFormLayout($form);
            $content .= '<div class="view-toolbar-container clearfix"><div class="form-toolbar">';
            $content .= $this->renderActionElementBar(true);
            $content .= '</div></div>';
            $content .= $clipWidget->renderEndWidget();
            $content .= '</div></div>';
            return $content;
        }

        public function getTitle()
        {
            return Yii::t('Default', 'Languages');
        }

        /**
         * Render a form layout.
         * @param $form If the layout is editable, then pass a $form otherwise it can
         * be null.
         * @return A string containing the element's content.
          */
        protected function renderFormLayout(ZurmoActiveForm $form)
        {
            $content  = '<table>';
            $content .= '<colgroup>';
            $content .= '<col style="width:15%" /><col />';
            $content .= '</colgroup>';
            $content .= '<tbody>';
            $content .= '<tr><th>' . $this->renderActiveHeaderContent() . '</th>';
            $content .= '<th>' . Yii::t('Default', 'Language') . '</th>';
            $content .= '</tr>';
            foreach ($this->languagesData as $language => $languageData)
            {
                assert('is_string($languageData["label"])');
                assert('is_bool($languageData["active"])');
                assert('is_bool($languageData["canInactivate"])');
                $route = $this->moduleId . '/' . $this->controllerId . '/delete/';
                $content .= '<tr>';
                $content .= '<td class="checkbox-column">' . self::renderActiveCheckBoxContent($form, $language,
                                                                       $languageData['active'],
                                                                       $languageData['canInactivate']) . '</td>';
                $content .= '<td>' . $languageData['label'] . '</td>';
                $content .= '</tr>';
            }
            $content .= '</tbody>';
            $content .= '</table>';
            return $content;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'  => 'ConfigurationLink',
                                  'label' => "eval:Yii::t('Default', 'Cancel')"),
                            array('type'  => 'SaveButton',
                                  'htmlOptions' => array('id' => 'save-collection', 'name' => 'save-collection')),
                        ),
                     ),
                ),
            );
            return $metadata;
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        protected static function renderActiveCheckBoxContent(ZurmoActiveForm $form, $language, $active, $canInactivate)
        {
            assert('is_string($language)');
            assert('is_bool($active)');
            assert('is_bool($canInactivate)');
            $name                = 'LanguageCollection[' . $language . '][active]';
            $htmlOptions         = array();
            $htmlOptions['id']   = 'LanguageCollection_' . $language . '_active';

            if (!$canInactivate)
            {
                $htmlOptions['disabled'] = 'disabled';
                $htmlOptions['uncheckValue'] = '1';
            }
            return ZurmoHtml::checkBox($name, $active, $htmlOptions);
        }

        protected static function renderActiveHeaderContent()
        {
            $title      = Yii::t('Default', 'Active languages can be used by users. The system language and any language in use by a user cannot be inactivated');
            $content    = Yii::t('Default', 'Active');
            $content   .= '<span id="active-languages-tooltip" class="tooltip" title="' . $title . '">?</span>';
            $qtip = new ZurmoTip();
            $qtip->addQTip("#active-languages-tooltip");
            return $content;
        }
    }
?>