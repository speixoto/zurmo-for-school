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
     * Class to render a fly-out link from the report detailsview.  When this link is clicked, a little div will
     * open up that display additional information about the marketing list
     */
    class MarketingListsDetailsLinkActionElement extends LinkActionElement
    {
        public function getActionType()
        {
            return 'Details';
        }

        protected function getDefaultLabel()
        {
            return Zurmo::t('MarketingListsModule', 'Details');
        }

        protected function getDefaultRoute()
        {
            return null;
        }

        public function render()
        {
            $items = array($this->renderMenuItem());
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("MarketingListDetailsMenu");
            $cClipWidget->widget('application.core.widgets.MinimalDynamicLabelMbMenu', array(
                'htmlOptions' => array('id' => 'ListViewDetailsActionMenu'),
                'items'                   => $items,
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['MarketingListDetailsMenu'];
        }

        public function renderMenuItem()
        {
            $detailsOverlayView = new MarketingListDetailsOverlayView($this->controllerId,
                                                                        $this->moduleId,
                                                                        $this->params['model']
                                                                        );
            return array('label'        => $this->getLabel(),
                         'url'          => null,
                         'itemOptions'  => null,
                         'items'        => array(
                                                array(
                                                'label'                 => '',
                                                'dynamicLabelContent'   => $detailsOverlayView->render(),
                                                )
                                            )
                            );
        }
    }
?>
