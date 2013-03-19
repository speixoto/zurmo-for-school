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
     * Update Marketing List.
     */
    // TODO: @Shoaibi: Not used?
    class MarketingListsUpdateLinkActionElement extends LinkActionElement
    {
        // TODO: @Shoaibi: High: This also refreshes grid.
        public function getActionType()
        {
            return 'Details';
        }

        protected function getDefaultLabel()
        {
            return Zurmo::t('Default', 'Update');
        }

        protected function getDefaultRoute()
        {
          // TODO: @Shoaibi: High: action has to be added.
        }

       public function render()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ActionMenu");
            $cClipWidget->widget('application.core.widgets.MbMenu', array(
                'htmlOptions' => array('id' => 'ListViewUpdateMarketingListsMenu'),
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
                    array(  'label'   => Zurmo::t('Default', 'Unsubscribe Selected'),
                        'url'     => '#',
                        'itemOptions' => array( 'id'   => $this->getUnsubscribeSelectedId())),
                    array(  'label'   => Zurmo::t('Default', 'Unsubscribe All'),
                        'url'     => '#',
                        'itemOptions' => array( 'id'   => $this->getUnsubscribeAllId())),
                    array(  'label'   => Zurmo::t('Default', 'Subscribe Selected'),
                        'url'     => '#',
                        'itemOptions' => array( 'id'   => $this->getSubscribeSelectedId())),
                    array(  'label'   => Zurmo::t('Default', 'Subscribe All'),
                        'url'     => '#',
                        'itemOptions' => array( 'id'   => $this->getSubscribeAllId()))));

        }

        protected function registerScripts()
        {
            // TODO: @Shoaibi: High: Implement scripts to handle user operations
        }

        protected function getUnsubscribeSelectedId()
        {
            return $this->getListViewGridId() . '-unsubscribeSelectedMarketingListMembers';
        }

        protected function getUnsubscribeAllId()
        {
            return $this->getListViewGridId() . '-unsubscribeAllMarketingListMembers';
        }

        protected function getSubscribeSelectedId()
        {
            return $this->getListViewGridId() . '-subscribeSelectedMarketingListMembers';
        }

        protected function getSubscribeAllId()
        {
            return $this->getListViewGridId() . '-subscribeAllMarketingListMembers';
        }

        protected function getListViewGridId()
        {
            // TODO: @Shoaibi/@Jason: Low: should be probably ported to parent
            if (!isset($this->params['listViewGridId']))
            {
                throw new NotSupportedException();
            }
            return $this->params['listViewGridId'];
        }
    }
?>
