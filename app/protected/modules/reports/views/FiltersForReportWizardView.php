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

    class FiltersForReportWizardView extends ComponentWithTreeForReportWizardView
    {
        protected function renderExtraDroppableAttributesContent()
        {
            return $this->renderStructureContent();
        }

        public static function getTreeType()
        {
            return ComponentForReportForm::TYPE_FILTERS;
        }

        public static function getWizardStepTitle()
        {
            return Yii::t('Default', 'Select Filters');
        }

        public static function getPreviousPageLinkId()
        {
            return 'filterBysPreviousLink';
        }

        public static function getNextPageLinkId()
        {
            return 'filterBysNextLink';
        }

        protected function getAddAttributeUrl()
        {
            return  Yii::app()->createUrl('reports/default/addAttributeFromTree',
                        array_merge($_GET, array('type'                       => $this->model->type,
                                                 'treeType'                   => static::getTreeType(),
                                                 'trackableStructurePosition' => true)));
        }

        protected function registerScripts()
        {
            parent::registerScripts();
            Yii::app()->clientScript->registerScript('showStructurePanels' . $this->form->getId(), "
                $('#show-filters-structure-div-link').click( function()
                    {
                        $('#show-filters-structure-div').show();
                        $('#show-filters-structure-div-link').hide();
                        return false;
                    }
                );");
        }

        protected function renderStructureContent()
        {
            $style1 = '';
            $style2 = 'display:none;';
            if (count($this->model->filters) > 0)
            {
                $style3 = '';
            }
            else
            {
                $style3 = 'display:none;';
            }
            $content  = ZurmoHtml::link(Yii::t('Default', 'Modify Structure'), '#',
                            array('id'    => 'show-filters-structure-div-link',
                                  'style' => $style1));
            $content .= ZurmoHtml::tag('div',
                            array('id'    => 'show-filters-structure-div',
                                  'class' => 'has-lang-label',
                                  'style' => $style2), $this->renderStructureInputContent());
            $content  = ZurmoHtml::tag('div', array('id'    => 'show-filters-structure-wrapper',
                                                     'style' => $style3), $content);
            return $content;
        }

        protected function renderStructureInputContent()
        {
            $idInputHtmlOptions  = array('id'    => $this->getStructureInputId(),
                                         'name'  => $this->getStructureInputName(),
                                         'class' => 'filters-structure-input');
            $content             = $this->form->textField($this->model, 'filtersStructure', $idInputHtmlOptions);
            $content            .= ZurmoHtml::tag('span', array(), Yii::t('Default', 'Search Operator'));
            $content            .= $this->form->error($this->model, 'filtersStructure');
            return $content;
        }

        protected function getStructureInputId()
        {
            return get_class($this->model) . '_filtersStructure';
        }

        protected function getStructureInputName()
        {
            return get_class($this->model) . '[filtersStructure]';
        }

        protected function getReportAttributeRowAddOrRemoveExtraScript()
        {
            return 'rebuildReportFiltersAttributeRowNumbersAndStructureInput("' . get_class($this) . '");';
        }

        protected function getItems(& $rowCount)
        {
            return $this->renderItems($rowCount, $this->model->filters, true);
        }
    }
?>