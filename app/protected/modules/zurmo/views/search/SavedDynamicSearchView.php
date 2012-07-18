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

        protected function renderDynamicAdvancedSearchRows($panel, $maxCellsPerRow,  $form)
        {
            $content  = $this->renderSavedSearchList();
            $content .= parent::renderDynamicAdvancedSearchRows($panel, $maxCellsPerRow,  $form);
            return $content;
        }

        protected function renderSavedSearchList()
        {
            $savedSearches = SavedSearch::getByOwnerAndViewClassName(Yii::app()->user->userModel, get_class($this));
            if(count($savedSearches) > 0)
            {
                $idOrName      = static::getSavedSearchListDropDown();
                $htmlOptions   = array('id' => $idOrName, 'empty' => Yii::t('Default', 'Load a saved search'));
                $content       = CHtml::dropDownList($idOrName,
                                                     $this->model->savedSearchId,
                                                     self::resolveSavedSearchesToIdAndLabels($savedSearches),
                                                     $htmlOptions);
                $this->renderSavedSearchDropDownOnChangeScript($idOrName, $this->model->loadSavedSearchUrl);
                return $content;
            }
        }

        protected static function getSavedSearchListDropDown()
        {
            return 'savedSearchId';
        }

        protected static function resolveSavedSearchesToIdAndLabels($savedSearches)
        {
            $data = array();
            foreach($savedSearches as $savedSearch)
            {
                $data[$savedSearch->id] = strval($savedSearch);
            }
            return $data;
        }

        protected function renderSavedSearchDropDownOnChangeScript($id, $onChangeUrl)
        {
            //Currently supports only if there is no additional get params. Todo, merge if there is an existing get param.
            Yii::app()->clientScript->registerScript('savedSearchLoadScript', "
                $('#" . $id . "').unbind('change'); $('#" . $id . "').bind('change', function()
                {
                    if($(this).val() != '')
                    {
                        window.location = '" . $onChangeUrl . "?savedSearchId=' + $(this).val();
                    }
                });");
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
            $content .= $this->renderDeleteLinkContent();
            return $content;
        }

        protected function renderDeleteLinkContent()
        {
            // Begin Not Coding Standard
            Yii::app()->clientScript->registerScript('deleteSavedSearchAndRemoveFromViewScript', "
                function deleteSavedSearchAndRemoveFromView(modelId)
                {
                        $.ajax({
                            url : '" . Yii::app()->createUrl('zurmo/default/deleteSavedSearch') . "?id=' + modelId,
                            type : 'GET',
                            dataType : 'json',
                            success : function(data)
                            {
                               var inputId = '" . static::getSavedSearchListDropDown() . "';
                               $('#' + inputId + ' > option').each(function(){
                                   if (this.value == modelId)
                                   {
                                       $('#' + inputId + ' option[value=\'' + this.value + '\']').remove();
                                   }
                               });
                               $('#' + inputId).removeData('dropkick');
                               $('#dk_container_' + inputId).remove();
                               $('#' + inputId).dropkick();
                               $('#' + inputId).dropkick('rebindToggle');
                               $('#removeSavedSearch').remove();
                            },
                            error : function()
                            {
                                //todo: error call
                            }
                        });
                }
            ", CClientScript::POS_END);
            // End Not Coding Standard
            if($this->model->savedSearchId != null)
            {
                $label = Yii::t('Default', 'Delete') . "<span class='icon'></span>";
                return CHtml::link($label, "#", array( 'id'		 => 'removeSavedSearch',
                                                       'class'   => 'remove',
                                                       'onclick' => "deleteSavedSearchAndRemoveFromView('" . $this->model->savedSearchId . "')"));
            }
        }

        protected function getExtraRenderForClearSearchLinkScript()
        {
            return parent::getExtraRenderForClearSearchLinkScript() .
                    "$('#" . static::getSavedSearchListDropDown() . "').val();
                     $('#" . static::getSavedSearchListDropDown() . "').removeData('dropkick');
                     $('#dk_container_" . static::getSavedSearchListDropDown() . "').remove();
                     $('#" . static::getSavedSearchListDropDown() . "').dropkick();
                     $('#" . static::getSavedSearchListDropDown() . "').dropkick('rebindToggle');
                     $('#save-search-area').hide();
                     $('#save-as-advanced-search').show();
                     jQuery.yii.submitForm(this, '', {}); return false;
            ";           
        }
    }
?>
