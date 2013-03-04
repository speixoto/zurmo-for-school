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
        const SUBSCRIBERS_PORTLET_CLASS     = 'marketing-list-subscribers-portlet-container';

        const AUTORESPONDERS_PORTLET_CLASS  = 'marketing-list-autoresponder-portlet-container';

        public static function assertModelIsValid($model)
        {
            assert('$model instanceof MarketingList');
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
                            array('type'  => 'MarketingListsTogglePortletsLink',
                                'htmlOptions' => array('class' => 'hasCheckboxes'),
                                'subscribersPortletClass'           => static::SUBSCRIBERS_PORTLET_CLASS,
                                'autorespondersPortletClass'        => static::AUTORESPONDERS_PORTLET_CLASS,),
                            // TODO: @Shoaibi: also: see that all UL's are created with same ID - this is not valid html
                            // TODO: @Shoaibi: add panels and Both portlets in leftBottom.
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        public function getTitle()
        {
            return strval($this->model);
        }

        protected function renderContent()
        {
            $actionElementBarContent        = $this->renderActionElementBar(false);
            // TODO: @Shoaibi: any security things to think about?  shouldRenderToolBarElement like in SecuredActionBarForSearchAndListView
            $content                        = $this->renderTitleContent();
            $content                       .= ZurmoHtml::tag('div', array('class' => 'view-toolbar-container clearfix'),
                                                ZurmoHtml::tag('div', array('class' => 'view-toolbar'),
                                                                                    $actionElementBarContent)
                                                );
            $content                       .= $this->renderSubscriberPortlet() . $this->renderAutorespondersPortlet();
            return $content;
        }

        protected function renderSubscriberPortlet()
        {
            // TODO: @Shoaibi: Where do we get the parameters?
            $subscribersViewData            = false;
            $subscribersParams              = array(
                                                'controllerId' => $this->getId(),
                                                'relationModuleId' => $this->getModuleLabel('PluralLowerCase'),
                                                'relationModel' => $this->model,
                                                'redirectUrl' => Yii::app()->request->url,
                                                'portletId' => 53, // TODO: @Shoaibi: From where do we get this?
                                            );
            $subscriberLayoutId             = __CLASS__ . '_' . 53;
            $subscribersPortlet             = new MarketingListSubscribersForPortletView($subscribersViewData,
                                                                                $subscribersParams, $subscriberLayoutId);
            return ZurmoHtml::tag('div', array('class' => static::SUBSCRIBERS_PORTLET_CLASS), $subscribersPortlet->render());
        }

        protected function renderAutorespondersPortlet()
        {
            // TODO: @Shoaibi: Implement
            $autorespondersPortletContent  = 'Dummy Autoresponders Portlet content';
            return ZurmoHtml::tag('div', array('class' => static::AUTORESPONDERS_PORTLET_CLASS), $autorespondersPortletContent);
        }

        protected function getModuleClassName()
        {
            // TODO: @Shoaibi this could be ported to parent.
            if ($this->modelId > 0)
            {
                $modelClassName = get_class($this->model);
                return $modelClassName::getModuleClassName();
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        protected function getModuleLabel($type, $language = null)
        {
            // TODO: @Shoaibi this could be ported to parent.
            $moduleClassName = $this->getModuleClassName();
            return $moduleClassName::getModuleLabelByTypeAndLanguage($type, $language);
        }
    }
?>
