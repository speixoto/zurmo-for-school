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

    class MarketingListDetailsView extends SecuredDetailsView
    {
        public static function assertModelIsValid($model)
        {
            assert('$model instanceof MarketingList');
        }

        protected function renderContent()
        {
            $content = $this->renderTitleContent();
//todo: any security things to think about?  shouldRenderToolBarElement like in SecuredActionBarForSearchAndListView
            $content .= '<div class="view-toolbar-container clearfix"><div class="view-toolbar">';
            $content .= $this->renderActionElementBar(false);
            $content .= '</div></div>';
            return $content;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'  => 'MarketingListsDetailsLink',
                                'htmlOptions' => array('class' => 'icon-details')),
                            array('type'  => 'MarketingListsOptionsLink',
                                'htmlOptions' => array('class' => 'icon-edit')),
                            //todo: also: see that all UL's are created with same ID - this is not valid html
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        public function getTitle()
        {
            if ($this->model->id > 0)
            {
                $moduleClassName = $this->model->moduleClassName;
                $typesAndLabels  = Report::getTypeDropDownArray();
                return strval($this->model) . ' - ' .
                    Yii::t('Default', '{moduleLabel} {typeLabel} MarketingList',
                        array('{moduleLabel}' => $moduleClassName::getModuleLabelByTypeAndLanguage('Singular'),
                            '{typeLabel}'   => $typesAndLabels[$this->model->type]));
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function renderActionElementBar($renderedInForm)
        {
            $selectContactAndReportLinkActionElement  = new SelectContactAndReportLinkActionElement(
                $this->controllerId,
                $this->moduleId,
                $this->modelId,
                array('htmlOptions' => array('class'   => 'icon-select')));
            $updateMarketingListsLinkActionElement  = new UpdateMarketingListsLinkActionElement(
                $this->controllerId,
                $this->moduleId,
                $this->modelId,
                array('htmlOptions' => array('class'   => 'icon-update')));
            $content .= $selectContactAndReportLinkActionElement->render();
            $content .= $updateMarketingListsLinkActionElement->render();
            return $content;
        }
    }
?>
