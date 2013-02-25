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

    class MashableInboxMassActionElement extends LinkActionElement
    {
        public function getActionType()
        {
            return 'MassEdit';
        }

        public function render()
        {
            $defaultMassOptions  = array(
                          'markRead'      => array('label' => Zurmo::t('MashableInboxModule', 'Mark selected as read'),
                                                   'isActionForAll' => false),
                          'markUnread'    => array('label' => Zurmo::t('MashableInboxModule', 'Mark selected as unread'),
                                                   'isActionForAll' => false),
                    );
            $massOptions = $defaultMassOptions;
            if ($this->getModelClassName() !== null)
            {
                $mashableUtilRules  = MashableUtil::createMashableInboxRulesByModel($this->getModelClassName());
                $massOptions        = array_merge($defaultMassOptions, $mashableUtilRules->getMassOptions());
            }
            $gridId             = $this->getListViewGridId();
            $formName           = $this->getFormName();
            $formClassName      = $this->modelId;
            $ajaxSubmitScript   = ZurmoHtml::ajax(array(
                        "type"       => "GET",
                        "data"       => "js:$('#{$formName}').serialize()",
                        "update"     => "#MashableInboxListViewWrapper",
                        "beforeSend" => "js:function(){makeSmallLoadingSpinner(); $('#MashableInboxListViewWrapper').addClass('loading');}",
                        "complete"   => "js:function()
                                            {
                                                $('#MashableInboxListViewWrapper').removeClass('loading');
                                                processListViewSummaryClone('MashableInboxListViewWrapper', 'summary');
                                            }"
                    ));
            $script = '';
            $items  = array();
            foreach ($massOptions as $massOption => $massOptionParams)
            {
                $selectedName = $gridId . '-' . $massOption;
                $items[]      = array('label' => $massOptionParams['label'],
                                      'url'   => '#',
                                      'itemOptions' => array( 'id'   => $selectedName));
                $isActionForEachScript = '';
                if (!$massOptionParams['isActionForAll'])
                {
                    $isActionForEachScript = "
                            if ($('#" . $gridId . "-selectedIds').val() == '')
                            {
                                alert('" . Zurmo::t('MashableInboxModule', 'You must select at least one record') . "');
                                $(this).val('');
                                return false;
                            }";
                }
                $script      .= "
                    $('#{$selectedName}').unbind('click.action');
                    $('#{$selectedName}').bind('click.action', function()
                        {
                            {$isActionForEachScript}
                            $('#{$formClassName}_massAction').val('{$massOption}');
                            $('#{$formClassName}_selectedIds').val($('#{$gridId}-selectedIds').val());
                            {$ajaxSubmitScript};
                        }
                    );
                ";
            }
            Yii::app()->clientScript->registerScript($gridId . 'ScriptForMashableInboxMassAction', $script);
            $menuItems      = array('label' => $this->getLabel(), 'url' => null,
                                    'items' => $items);

            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ActionMenu");
            $cClipWidget->widget('application.core.widgets.MbMenu', array(
                'htmlOptions' => array('id' => 'MashableInboxMassActionMenu'),
                'items'       => array($menuItems),
            ));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['ActionMenu'];
        }

        protected function getDefaultLabel()
        {
            return Zurmo::t('MashableInbox', 'Options');
        }

        protected function getListViewGridId()
        {
            if (!isset($this->params['listViewGridId']))
            {
                throw new NotSupportedException();
            }
            return $this->params['listViewGridId'];
        }

        protected function getModelClassName()
        {
            return $this->params['modelClassName'];
        }

        protected function getFormName()
        {
            if (!isset($this->params['formName']))
            {
                throw new NotSupportedException();
            }
            return $this->params['formName'];
        }

        protected function getDefaultRoute()
        {
            return $this->moduleId . '/' . $this->controllerId . '/list/';
        }
    }
?>