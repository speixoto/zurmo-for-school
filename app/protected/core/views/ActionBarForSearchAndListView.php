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

    /**
     * Renders an action bar specifically for the search and listview.
     */
    class ActionBarForSearchAndListView extends ActionBarConfigurableMetadataView
    {
        /**
         * Typically the model used for the list view.
         * @var Object
         */
        protected $model;

        /**
         * The unique id of the list view grid.
         * @var string
         */
        protected $listViewGridId;

        /**
         * The variable name used for the pagination of the list view.
         * @var string
         */
        protected $pageVarName;

        /**
         * True false whether the list view rows are selectable and will display a checkbox next to each row.
         * @var boolean
         */
        protected $listViewRowsAreSelectable;

        /**
         * If set an intro view will be rendered
         * @var null|IntroView
         */
        protected $introView;

        public function __construct($controllerId, $moduleId, RedBeanModel $model, $listViewGridId,
                                    $pageVarName, $listViewRowsAreSelectable, $activeActionElementType = null,
                                    IntroView $introView = null)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($listViewGridId)');
            assert('is_string($pageVarName)');
            assert('is_bool($listViewRowsAreSelectable)');
            assert('$activeActionElementType == null || is_string($activeActionElementType)');
            $this->controllerId              = $controllerId;
            $this->moduleId                  = $moduleId;
            $this->model                     = $model;
            $this->listViewGridId            = $listViewGridId;
            $this->pageVarName               = $pageVarName;
            $this->listViewRowsAreSelectable = $listViewRowsAreSelectable;
            $this->activeActionElementType   = $activeActionElementType;
            $this->introView                 = $introView;
        }

        protected function renderContent()
        {
            $content  = '<div class="view-toolbar-container clearfix">';
            $content .= '<div class="view-toolbar">' . $this->renderActionElementBar(false) . '</div>';
            if (!Yii::app()->userInterface->isMobile() &&
                null != $secondActionElementBarContent = $this->renderSecondActionElementBar(false))
            {
                $content .= '<div class="view-toolbar">' . $secondActionElementBarContent . '</div>';
            }
            $content .= '</div>';
            if (isset($this->introView))
            {
                $content .= $this->introView->render();
            }
            return $content;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type'  => 'CreateLink',
                                'htmlOptions' => array('class' => 'icon-create'),
                            ),
                            array('type'  => 'MassEditLink',
                                  'htmlOptions' => array('class' => 'icon-edit'),
                                  'listViewGridId' => 'eval:$this->listViewGridId',
                                  'pageVarName' => 'eval:$this->pageVarName'),
                            array('type'  => 'ExportLink',
                                  'htmlOptions' => array('class' => 'icon-export'),
                                  'listViewGridId' => 'eval:$this->listViewGridId',
                                  'pageVarName' => 'eval:$this->pageVarName'),
                            array('type'  => 'MassDeleteLink',
                                  'htmlOptions' => array('class' => 'icon-delete'),
                                  'listViewGridId' => 'eval:$this->listViewGridId',
                                  'pageVarName' => 'eval:$this->pageVarName'),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        /**
         * Override to check for for MassEdit link. This link should only be rendered if there are selectable rows.
         * @return boolean
         */
        protected function shouldRenderToolBarElement($element, $elementInformation)
        {
            assert('$element instanceof ActionElement');
            assert('is_array($elementInformation)');
            if (!parent::shouldRenderToolBarElement($element, $elementInformation))
            {
                return false;
            }
            if ($this->activeActionElementType == ListViewTypesToggleLinkActionElement::TYPE_KANBAN_BOARD &&
                ($elementInformation['type'] == 'MassEditLink' ||
                $elementInformation['type'] == 'MassDeleteLink' ||
                $elementInformation['type'] == 'ExportLink'))
            {
                return false;
            }
            if ($elementInformation['type'] == 'MassEditLink' && !$this->listViewRowsAreSelectable)
            {
                return false;
            }
            return true;
        }

        protected function resolveActionElementInformationDuringRender(& $elementInformation)
        {
            parent::resolveActionElementInformationDuringRender($elementInformation);
            if ($elementInformation['type'] == $this->activeActionElementType)
            {
                $elementInformation['htmlOptions']['class'] .= ' active';
            }
        }
    }
?>