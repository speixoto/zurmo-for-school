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
     * Renders an action bar specifically for the search and listview.
     */
    class ActionBarForUserEditAndDetailsView extends ConfigurableMetadataView
    {
        protected $controllerId;

        protected $moduleId;

        protected $model;

        protected $activeActionElementType;

        public function __construct($controllerId, $moduleId, User $model, $activeActionElementType)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_string($activeActionElementType)');
            $this->controllerId              = $controllerId;
            $this->moduleId                  = $moduleId;
            $this->modelId                   = $model->id;
            $this->model                     = $model;
            $this->activeActionElementType   = $activeActionElementType;
        }

        protected function renderContent()
        {
            $content  = '<div class="view-toolbar-container clearfix"><div class="view-toolbar">';
            $content .= $this->renderActionElementBar(false);
            $content .= '</div></div>';
            return $content;
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'DetailsLink',
                                'label' => "eval:Yii::t('Default', 'Profile')",
                                'htmlOptions' => array( 'class' => 'icon-user-details' )
                            ),
                            array('type' => 'EditLink',
                                'htmlOptions' => array( 'class' => 'icon-edit' )
                            ),
                            array('type' => 'AuditEventsModalListLink',
                                'htmlOptions' => array( 'class' => 'icon-audit' )
                            ),
                            array('type' => 'ChangePasswordLink',
                                'htmlOptions' => array( 'class' => 'icon-password' )
                            ),
                            array('type' => 'UserConfigurationLink',
                                'htmlOptions' => array( 'class' => 'icon-user-config' ),
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
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