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
     * Modal window for create/edit task
     */
    class TaskModalEditAndDetailsView extends TaskEditAndDetailsView
    {
         /**
          * @return array
          */
         public static function getDefaultMetadata()
         {
            $metadata = parent::getDefaultMetadata();
            $metadata['global']['toolbar']['elements'][1] = array('type'            => 'ModalCancelLink',
                                                                  'htmlOptions'     => 'eval:static::resolveHtmlOptionsForCancel()',
                                                                  );
            return $metadata;
         }

         /**
          * @return string
          */
         protected function getNewModelTitleLabel()
         {
             return null;
         }

        /**
         * @return string
         */
        protected static function getFormId()
        {
            return 'task-modal-edit-form';
        }

        /**
         * @return array
         */
        protected static function resolveHtmlOptionsForCancel()
        {
            return array(
                'onclick' => '$("#ModalView").parent().dialog("close");'
            );

        }

        /**
         * Resolves ajax validation option for save button
         * @return array
         */
        protected function resolveActiveFormAjaxValidationOptions()
        {
            $id             = Yii::app()->request->getParam('id', null);
            $getData        = GetUtil::getData();
            if(GetUtil::resolveParamFromRequest('modalTransferInformation', null) != null)
            {
                $relationAttributeName   = GetUtil::resolveModalTransferInformationParamFromRequest('relationAttributeName', null);
                $relationModelId         = GetUtil::resolveModalTransferInformationParamFromRequest('relationModelId', null);
                $relationModuleId        = GetUtil::resolveModalTransferInformationParamFromRequest('relationModuleId', null);
                $modalId                 = GetUtil::resolveModalTransferInformationParamFromRequest('modalId', null);
                $portletId               = GetUtil::resolveModalTransferInformationParamFromRequest('portletId', null);
                $uniqueLayoutId          = GetUtil::resolveModalTransferInformationParamFromRequest('uniqueLayoutId', null);

                $action                  = Yii::app()->request->getParam('action', null);
                if($action == null)
                {
                    $params = array('id' => $id);
                }
                else
                {
                    $params = array('id' => null);
                }
                $url = Yii::app()->createUrl('tasks/default/modalSaveFromRelation',
                                                                        array_merge(array('relationAttributeName' => $relationAttributeName,
                                                                                          'relationModelId'       => $relationModelId,
                                                                                          'relationModuleId'      => $relationModuleId,
                                                                                          'portletId'             => $portletId,
                                                                                          'uniqueLayoutId'        => $uniqueLayoutId
                                                                                          ), $params
                                                                                        ));
            }
            else
            {
                $url = Yii::app()->createUrl('tasks/default/modalSave', array('id' => $id));
            }
            $errorInProcess = CJavaScript::quote(Zurmo::t('Core', 'There was an error processing your request'));
            return array('enableAjaxValidation' => true,
                        'clientOptions' => array(
                            'beforeValidate'    => 'js:$(this).beforeValidateAction',
                            'afterValidate'     => 'js:function(form, data, hasError){
                                                        if(hasError)
                                                        {
                                                            form.find(".attachLoading:first").removeClass("loading");
                                                            form.find(".attachLoading:first").removeClass("loading-ajax-submit");
                                                        }
                                                        else
                                                        {
                                                            js:saveTaskFromRelation("' . $url . '", "'. $errorInProcess . '","' . TasksUtil::getModalTitleForViewTask() . '");
                                                        }
                                                        return false;
                                                    }',
                            'validateOnSubmit'  => true,
                            'validateOnChange'  => false,
                            'inputContainer'    => 'td'
                        )
            );
        }
    }
?>