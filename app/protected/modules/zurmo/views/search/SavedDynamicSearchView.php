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
     * The Zurmo base search view for a module's search view.  Includes extra pieces like filtered lists.
     */
    abstract class SavedDynamicSearchView extends DynamicSearchView
    {
        public function __construct($model,
            $listModelClassName,
            $gridIdSuffix = null,
            $hideAllSearchPanelsToStart = false
            )
        {
            assert('$model instanceof SavedDynamicSearchForm');
            parent::__construct($model, $listModelClassName, $gridIdSuffix = null, $hideAllSearchPanelsToStart);
        }

        protected function getExtraRenderFormBottomPanelScriptPart()
        {
            return parent::getExtraRenderFormBottomPanelScriptPart() .
                    "$('#save-as-advanced-search').click( function()
                    {
                        $('#save-search-area').show();
                        $('#save-as-advanced-search').hide();
                        return false;
                    }
                );";
        }

        protected function renderConfigSaveAjax($formName)
        {
            return     "$('#" . $formName . "').find('.attachLoadingTarget').removeClass('loading');
                        $('#" . $formName . "').find('.attachLoadingTarget').removeClass('loading-ajax-submit');
                        $('#" . $formName . "').find('.attachLoadingTarget').removeClass('attachLoadingTarget');" .
                       parent::renderConfigSaveAjax($formName);
        }

        protected function renderViewToolBarLinksForAdvancedSearch($form)
        {
            $content  = CHtml::link(Yii::t('Default', 'Close'), '#', array('id' => 'cancel-advanced-search'));
            $content .= CHtml::link(Yii::t('Default', 'Save As'), '#', array('id' => 'save-as-advanced-search'));
            $params = array();
            $params['label']       = Yii::t('Default', 'Search');
            $params['htmlOptions'] = array('id' => 'search-advanced-search');
            $searchElement = new SaveButtonActionElement(null, null, null, $params);
            $content .= $searchElement->render();
            $content .= '<div id="save-search-area" class="view-toolbar-container clearfix" style="display:none;">';
            $content .= $this->renderSaveInputAndSaveButtonContentForAdvancedSearch($form);
            $content .= '</div>';
            return $content;
        }

        protected function renderSaveInputAndSaveButtonContentForAdvancedSearch($form)
        {
            $content               = $form->textField($this->model, 'savedSearchName');
            $content              .= $form->error($this->model, 'savedSearchName');
            $content              .= $form->hiddenField($this->model, 'savedSearchId');
            $params['label']       = Yii::t('Default', 'Save');
            $params['htmlOptions'] = array('id'      => 'save-advanced-search',
                                           'value'   => 'saveSearch',
                                           'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $searchElement = new SaveButtonActionElement(null, null, null, $params);
            $content .= $searchElement->render();
            return $content;
        }
    }
?>
