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

    class ReportRelationsAndAttributesTreeView extends View
    {
        const TREE_TYPE_FILTERS                       = 'Filters';

        const TREE_TYPE_DISPLAY_ATTRIBUTES            = 'DisplayAttributes';

        const TREE_TYPE_ORDER_BYS                     = 'OrderBys';

        const TREE_TYPE_GROUP_BYS                     = 'GroupBys';

        const TREE_TYPE_DRILL_DOWN_DISPLAY_ATTRIBUTES = 'DrillDownDisplayAttributes';

        protected $treeType;

        protected $formName;

        public function __construct($treeType, $formName)
        {
            assert('is_string($treeType)');
            assert('is_string($formName)');
            $this->treeType = $treeType;
            $this->formName = $formName;
        }

        public function isUniqueToAPage()
        {
            return false;
        }

        protected function renderContent()
        {
            $content      = null;
            $cClipWidget  = new CClipWidget();
            $cClipWidget->beginClip("ZurmoTreeView");
            $cClipWidget->widget('application.core.widgets.ZurmoTreeView', array(
            'id'          => $this->getTreeId(),
            'url'         => $this->getDataUrl(),
            'options' => array('formName' => $this->formName),
            'htmlOptions' => array(
                'class'   => 'treeview-red' //todo: use different theme class.
            )));
            $cClipWidget->endClip();
            $content .= $cClipWidget->getController()->clips['ZurmoTreeView'];

            //todo: move into JS file
            $script = '
                $( ".attribute-to-place" ).draggable({
                helper: "clone",
                revert: "invalid",
                snap: ".droppable-cell-container",
                snapMode: "inner",
                cursor: "pointer",
                start: function(event,ui)
                {
                    //$(ui.helper).attr("id", $(this).attr("id"));
                    //$(ui.helper).css("height", "20px");
                    //$(ui.helper).css("width", "260px");
                },
                stop: function(event, ui){
                    document.body.style.cursor = "auto";
                }});
            ';
            Yii::app()->getClientScript()->registerScript('reportTreeViewScript', $script);
            return $content;
        }

        protected function getDataUrl()
        {
            return  Yii::app()->createUrl('reports/default/relationsAndAttributesTree',
                        array_merge($_GET, array('treeType' => $this->treeType)));
        }

        protected function getTreeId()
        {
            return $this->treeType . 'TreeView';
        }
    }
?>