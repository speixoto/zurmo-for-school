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
     * View class for the  email alerts component for the workflow wizard user interface
     */
    class EmailAlertsForWorkflowWizardView extends ComponentForWorkflowWizardView
    {
        const ADD_EMAIL_ALERT_LINK_ID   ='AddEmailAlertLink';

        /**
         * @return string
         */
        public static function getWizardStepTitle()
        {
            return Zurmo::t('WorkflowsModule', 'Select Alerts');
        }

        /**
         * @return string
         */
        public static function getPreviousPageLinkId()
        {
            return 'emailAlertsPreviousLink';
        }

        /**
         * @return string
         */
        public static function getNextPageLinkId()
        {
            return 'emailAlertsNextLink';
        }

        /**
         * @return string
         */
        public static function getZeroComponentsClassName()
        {
            return 'ZeroEmailAlerts';
        }

        public function registerScripts()
        {
            parent::registerScripts();
            $this->registerRemoveEmailAlertScript();
            $this->registerRemoveEmailAlertRecipientScript();

        }

        /**
         * @return bool
         */
        protected function isListContentSortable()
        {
            return true;
        }

        /**
         * @return string
         */
        protected function renderFormContent()
        {
            $content  = '<div>'; //todo: is this div necessary?
            $content .= $this->renderZeroComponentsContentAndWrapper();
            $content .= $this->renderEmailAlertsContentAndWrapper();
            $content .= $this->renderAddEmailAlertLinkContentAndWrapper();
            $content .= '</div>';
            $this->registerScripts();
            return $content;
        }

        /**
         * @return string
         */
        protected function getZeroComponentsMessageContent()
        {
            return '<div class="large-icon"></div><h2>' . Zurmo::t('WorkflowsModule', 'Set an alert') . '</h2>';
        }
        protected function renderZeroComponentsContentAndWrapper()
        {
            return ZurmoHtml::tag('div', array('class' => 'zero-components-view ' .
                   ComponentForWorkflowForm::TYPE_EMAIL_ALERTS), $this->getZeroComponentsContent());
        }

        protected function renderAddEmailAlertLinkContentAndWrapper()
        {
            $content  = $this->renderAddEmailAlertLink(Zurmo::t('WorkflowsModule', 'Add Email Alert'));
            return ZurmoHtml::tag('div', array('class' => 'add-email-alert-button-container'), $content);
        }

        protected function renderEmailAlertsContentAndWrapper()
        {
            //todo: still seems strange we call it droppable even though it is only draggable here. maybe not a big deal
            $rowCount                    = 0;
            $items                       = $this->getItemsContent($rowCount);
            $itemsContent                = $this->getNonSortableListContent($items);
            $idInputHtmlOptions          = array('id' => static::resolveRowCounterInputId(ComponentForWorkflowForm::TYPE_EMAIL_ALERTS));
            $hiddenInputName             = ComponentForWorkflowForm::TYPE_EMAIL_ALERTS . 'RowCounter';
            $droppableAttributesContent  = ZurmoHtml::tag('div', array('class' => 'dynamic-rows'), $itemsContent);
            $content                     = ZurmoHtml::hiddenField($hiddenInputName, $rowCount, $idInputHtmlOptions);
            $content                    .= ZurmoHtml::tag('div', array('class' => 'droppable-dynamic-rows-container ' .
                                           ComponentForWorkflowForm::TYPE_EMAIL_ALERTS), $droppableAttributesContent);
            return $content;
        }

        protected function renderAddEmailAlertLink($label)
        {
            //Zurmo::t('WorkflowsModule', 'Add Email Alert'); //self::ADD_EMAIL_ALERT_LINK_NAME
            assert('is_string($label)');
            $rowCounterInputId = static::resolveRowCounterInputId(ComponentForWorkflowForm::TYPE_EMAIL_ALERTS);
            $moduleClassNameId = get_class($this->model) . '[moduleClassName]';
            $url               = Yii::app()->createUrl('workflows/default/addEmailAlert',
                                 array_merge($_GET, array('type' => $this->model->type)));
            $aContent          = ZurmoHtml::wrapLink($label);
            return  ZurmoHtml::ajaxLink($aContent, $url,
                    array(
                        'type'    => 'GET',
                        'data'    => 'js:\'moduleClassName=\' + $("input:radio[name=\"' .
                            $moduleClassNameId . '\"]:checked").val() + ' .
                            '\'&rowNumber=\' + $(\'#' . $rowCounterInputId. '\').val()',
                        'url'     =>  $url,
                        'beforeSend' => 'js:function(){ makeOrRemoveLoadingSpinner(true, "#" + $(this).attr("id")); }',
                        'success' => 'js:function(data){
                        $(\'#' . $rowCounterInputId. '\').val(parseInt($(\'#' . $rowCounterInputId . '\').val()) + 1);
                        $(".droppable-dynamic-rows-container.' . ComponentForWorkflowForm::TYPE_EMAIL_ALERTS
                            . '").find(".dynamic-rows").find("ul:first").first().append(data);
                        rebuildWorkflowEmailAlertRowNumbers("' . get_class($this) . '");
                        $(".' . static::getZeroComponentsClassName() . '").hide();
                        }',
                    ),
                    array('id' => self::ADD_EMAIL_ALERT_LINK_ID,
                          'class'      => 'attachLoading z-button ')
                      //'onclick'   => 'js:$(this).addClass("loading").addClass("loading-ajax-submit");
                      //                                  makeOrRemoveLoadingSpinner(true, "#" + $(this).attr("id"));')
            );
        }

        /**
         * @return int
         */
        protected function getItemsCount()
        {
            return count($this->model->emailAlerts);
        }

        /**
         * @param int $rowCount
         * @return array|string
         */
        protected function getItemsContent(& $rowCount)
        {
            return $this->renderEmailAlerts($rowCount, $this->model->emailAlerts);
        }

        protected function renderEmailAlerts(& $rowCount, $emailAlerts)
        {
            assert('is_int($rowCount)');
            assert('is_array($emailAlerts)');
            $items                      = array();
            foreach($emailAlerts as $emailAlert)
            {
                $inputPrefixData   = array(get_class($this->model), ComponentForWorkflowForm::TYPE_EMAIL_ALERTS, (int)$rowCount);
                $rowCounterInputId = ComponentForWorkflowWizardView::
                                     resolveRowCounterInputId(ComponentForWorkflowForm::TYPE_EMAIL_ALERTS);
                $view              = new EmailAlertRowForWorkflowComponentView($emailAlert, $rowCount, $inputPrefixData,
                                        $this->form, get_class($this->model), $rowCounterInputId);
                $view->addWrapper  = false;
                $items[]           = array('content' => $view->render());
                $rowCount ++;
            }
            return $items;
        }

        protected function registerRemoveEmailAlertScript()
        {
            $script = '
                $(".remove-dynamic-row-link").live("click", function(){
                    size = $(this).parent().parent().parent().find("li").size();
                    $(this).parent().parent().remove(); //removes the <li>
                    if(size < 2)
                    {
                        $(".' . static::getZeroComponentsClassName() . '").show();
                    }
                    rebuildWorkflowEmailAlertRowNumbers("' . get_class($this) . '");
                    return false;
                });
            ';
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('removeEmailAlertScript', $script);
        }

        protected function registerRemoveEmailAlertRecipientScript()
        {
            $script = '
                $(".' . EmailAlertRecipientRowForWorkflowComponentView::REMOVE_LINK_CLASS_NAME . '").live("click", function(){
                    div = $(this).parentsUntil(".' .
                            EmailAlertRowForWorkflowComponentView::RECIPIENTS_CONTAINER_CLASS_NAME . '").parent()
                            .find(".' . EmailAlertRowForWorkflowComponentView::EMAIL_ALERT_RECIPIENTS_ROW_CLASS_NAME .
                            '");
                    $(this).parent().parent().remove(); //removes the <li>
                    rebuildWorkflowEmailAlertRecipientRowNumbers(div);
                    return false;
                });
            ';
            // End Not Coding Standard
            Yii::app()->clientScript->registerScript('removeEmailAlertRecipientScript', $script);
        }
    }
?>