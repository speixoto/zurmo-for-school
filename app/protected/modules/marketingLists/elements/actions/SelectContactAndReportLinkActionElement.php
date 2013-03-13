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


    class SelectContactAndReportLinkActionElement extends LinkActionElement
    {
        // TODO: @Shoaibi: High: This also refreshes grid.
        public function getActionType()
        {
            return 'Details';
        }

        protected function getDefaultLabel()
        {
            return Zurmo::t('Default', 'Select');
        }

        protected function getDefaultRoute()
        {
          // TODO: @Shoaibi: Medium+: have to add the action for this
        }

        public function render()
        {
            $cClipWidget    = new CClipWidget();
            $cClipWidget->beginClip("ActionMenu");
            $cClipWidget->widget('application.core.widgets.MbMenu', array(
                                'htmlOptions' => array('id' => 'ListViewSelectContactAndReportMenu'),
                                'items'                   => array($this->renderMenuItem()),
                            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['ActionMenu'];
        }

        public function renderMenuItem()
        {
            $this->registerScripts();
            return array('label' => $this->getLabel(), 'url' => null,
                'items' => array(
                    array(  'label'   => Zurmo::t('Default', 'Contact/Lead'),
                        'url'     => '#',
                        'itemOptions' => array( 'id'   => $this->getSelectContactAndLeadId())),
                    array(  'label'   => Zurmo::t('Default', 'Reports'),
                        'url'     => '#',
                        'itemOptions' => array( 'id'   => $this->getSelectReportId()))));

        }

        protected function registerScripts()
        {
            // TODO: @Shoaibi: Medium+: Write JS code to handle events from here and use variableStateController
            Yii::app()->clientScript->registerScript($this->getListViewGridId() . '-listViewContactAndLead', "
                $('#" . $this->getSelectContactAndLeadId() . "').unbind('click.action').bind('click.action', function()
                    {
                    }
                );
            ");
            Yii::app()->clientScript->registerScript($this->getListViewGridId() . '-listViewReport', "
                $('#"  . $this->getSelectReportId() . "').unbind('click.action').bind('click.action', function()
                    {
                    }
                );
            ");
        }

        protected function getSelectContactAndLeadId()
        {
            return $this->getListViewGridId() . '-selectContactAndLead';
        }

        protected function getSelectReportId()
        {
            return $this->getListViewGridId() . '-selectReport';
        }

        protected function getListViewGridId()
        {
            // TODO: @Shoaibi: Low: should be probably ported to parent, throws exception, work on why?
            if (!isset($this->params['listViewGridId']))
            {
                throw new NotSupportedException();
            }
            return $this->params['listViewGridId'];
        }
    }
?>
