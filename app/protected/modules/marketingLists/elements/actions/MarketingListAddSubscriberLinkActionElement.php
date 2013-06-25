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

    class MarketingListAddSubscriberLinkActionElement extends LinkActionElement
    {
        public function getActionType()
        {
            return 'Create';
        }

        public function render()
        {
            $this->registerScripts();
            $items          = array($this->renderMenuItem());
            $clipName       = get_class($this);
            $cClipWidget    = new CClipWidget();
            $cClipWidget->beginClip($clipName);
            $cClipWidget->widget('application.core.widgets.MinimalDynamicLabelMbMenu', array(
                                                                            'htmlOptions'   => array(
                                                                                'id' => $clipName,
                                                                                'class' => 'clickable-mbmenu'
                                                                            ),
                                                                            'items'         => $items,
                                                                        ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips[$clipName];
        }

        public function renderMenuItem()
        {
            return array(
                'label' => $this->getLabel(),
                'url'   => $this->getRoute(),
                'items' => array(
                                array(
                                    'label'                 => '',
                                    'dynamicLabelContent'   => $this->renderSelectContactOrReport(),
                                ),
                            ),
                    );
        }

        protected function getDefaultLabel()
        {
            return Zurmo::t('MarketingListsModule', 'Add ContactsModuleSingularLabel/LeadsModuleSingularLabel',
                                                                        LabelUtil::getTranslationParamsForAllModules());
        }

        protected function getDefaultRoute()
        {
            return null;
        }

        protected function renderSelectContactOrReport()
        {
            $formName                       = 'marketing-list-member-select-contact-or-report-form';
            $clipWidget                     = new ClipWidget();
            list($form, $formStart)         = $clipWidget->renderBeginWidget(
                                                                            'ZurmoActiveForm',
                                                                            array(
                                                                                'id' => $formName,
                                                                            )
                                                                        );
            $content                        = $formStart;
            $content                       .= $this->renderCloseButton();
            $selectContactOrReportElement   = new SelectContactOrReportElement(new MarketingListMemberSelectForm(),
                                                            null,
                                                            $form,
                                                            array(
                                                                'pageVarName'       => $this->getPageVarName(),
                                                                'listViewGridId'    => $this->getListViewGridId(),
                                                                'marketingListId'   => $this->modelId,
                                                            )
                                                        );
            $content                        .= $selectContactOrReportElement->render();
            $content                        .= $clipWidget->renderEndWidget();
            return $content;
        }

        protected function getListViewGridId()
        {
            // TODO: @Shoaibi/@Jason: Low: Create a common parent for Element and ActionElement, put this there.
            return ArrayUtil::getArrayValueWithExceptionIfNotFound($this->params, 'listViewGridId');
        }

        protected function getPageVarName()
        {
            // TODO: @Shoaibi/@Jason: Low: Create a common parent for Element and ActionElement, put this there.
            return ArrayUtil::getArrayValueWithExceptionIfNotFound($this->params, 'pageVarName');
        }

        protected function renderCloseButton()
        {
            return ZurmoHtml::tag('a', array('class' => 'close-flyout'), 'Z');
        }

        protected function registerScripts()
        {
            $script = "$('close-flyout').click(function()
                        {
                            $(this).parentsUntil(li).parent().hide();
                        });";
            Yii::app()->clientScript->registerScript(get_class() . 'CloseFlyoutScript', $script);
        }
    }
?>