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

    class EmailTemplateEditAndDetailsView extends SecuredEditAndDetailsView
    {
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'    => 'EmailTemplateCancelLink', 'renderType' => 'Edit'),
                            array('type'    => 'SaveButton', 'renderType' => 'Edit'),
                            array('type'    => 'EditLink', 'renderType' => 'Details'),
                            array('type'    => 'EmailTemplateDeleteLink'),
                        ),
                    ),
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'type', 'type' => 'EmailTemplateType'),
                                            ),
                                        ),
                                    ),
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                /*
                                                 * // TODO: @Shoaibi Extracting model names:
                                                 * You can loop getAllModules or something like that in Module.php,
                                                 *  then from there for each module ::getPrimaryModelName,
                                                 *   then from there you have the model name and can use the label
                                                 *  function in model to get the translated, correct model
                                                 *  names for labels
                                                 *
                                                 * // TODO: @Shoaibi/@Jason dependent dropdown? DropDownDependencyElement, example?
                                                 * Dependendent DDL:
                                                 *  moduleClassName one has a public wraper that takes type and returns array
                                                 *  this wrapper calls specific method depending on type
                                                 *  these sub functions keep value to english label but option content to translated
                                                 *  the action does cjson::encode
                                                 *  on first run select the one user selected on generation, generate default depending on first option of the other ddl.
                                                 *  type ddl registers js to call that action and update ddl.
                                                 */
                                                array('attributeName' => 'modelClassName', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'name', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'subject', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                // TODO: @Shoaibi/@Jason: Why don't we use separate elements for text and html and display them here?
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        protected function renderRightSideFormLayoutForEdit($form)
        {
            $content = null;
            return $content;
        }

        protected function renderAfterFormLayout($form)
        {
            $content = null;
            $content .= '<div class="email-template-content"></div>' . "\n";
            $content .= '<div>' . "\n";
            $element  = new EmailTemplateHtmlAndTextContentElement($this->model, null , $form);
            $content .= $element->render();
            $content .= '</div>' . "\n";
            return $content;
        }

        protected function getNewModelTitleLabel()
        {
            return Zurmo::t('Default', 'Create EmailTemplatesModuleSingularLabel',
                                     LabelUtil::getTranslationParamsForAllModules());
        }

        protected function renderAfterFormLayoutForDetailsContent()
        {
            $content  = null;
            $content .= '<div class="email-template-content"></div>' . "\n";
            $content .= '<div>' . "\n";
            $element  = new EmailTemplateHtmlAndTextContentElement($this->model, null , null);
            $content .= $element->render();
            $content .= '</div>' . "\n";
            $content .= parent::renderAfterFormLayoutForDetailsContent();
            return $content;
        }
    }
?>
