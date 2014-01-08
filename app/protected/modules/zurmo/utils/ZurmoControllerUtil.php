<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    /**
     * Collection of helper methods for working with models, posts, and gets in conjunction with controller actions.
     */
    class ZurmoControllerUtil
    {
        /**
         * @param SecurableItem $model
         * @param User $user
         * @throws NotSupportedException
         */
        public static function updatePermissionsWithDefaultForModelByUser(SecurableItem $model, User $user)
        {
            if ($model instanceof SecurableItem && count($model->permissions) === 0)
            {
                // we use a dummy SecurableItem here because we don't care about 'owner' in permission array;
                // using SecurableItem here even when the actual model is OwnedSecurableItem would not cause
                // any unintended behavior.
                // If we use $model here and $model is SecurableItem but not OwnedSecurableItem we might
                // would have to unset($postData['owner']);
                $postData           = static::resolveUserDefaultPermissionsForCurrentUser(new SecurableItem());
                $explicitReadWritePermissions = self::resolveAndMakeExplicitReadWriteModelPermissions($postData, $model);
                $updated    = ExplicitReadWriteModelPermissionsUtil::resolveExplicitReadWriteModelPermissions($model,
                                                                                        $explicitReadWritePermissions);
                if (!$updated)
                {
                    throw new NotSupportedException();
                }
            }
        }

        public static function resolveUserDefaultPermissionsForCurrentUser(RedBeanModel $model = null)
        {
            return static::resolveUserDefaultPermissionsByUser(Yii::app()->user->userModel, $model);
        }

        public static function resolveUserDefaultPermissionsByUser(User $user, RedBeanModel $model = null)
        {
            $defaultPermissionSettings  = UserConfigurationFormAdapter::resolveAndGetDefaultPermissionSetting($user);
            $nonEveryoneGroup           = UserConfigurationFormAdapter::resolveAndGetValue($user,
                                                                                'defaultPermissionGroupSetting', false);
            $type                       = DerivedExplicitReadWriteModelPermissionsElement::resolveUserPermissionConfigurationToPermissionType(
                                                                                            $defaultPermissionSettings);
            $explicitReadWriteModelPermissions  = compact('type', 'nonEveryoneGroup');
            $permissions                        = compact('explicitReadWriteModelPermissions');
            if ($model === null || $model instanceof OwnedSecurableItem)
            {
                $owner                              = array('id' => $user->id);
                $permissions                        = compact('owner', 'explicitReadWriteModelPermissions');
            }
            return $permissions;
        }

        /**
         * @param SecurableItem $model
         */
        public static function updatePermissionsWithDefaultForModelByCurrentUser(SecurableItem $model)
        {
            static::updatePermissionsWithDefaultForModelByUser($model, Yii::app()->user->userModel);
        }

        /*
         * @param array $postData
         * @param $model
         * @param bool $savedSuccessfully
         * @param string $modelToStringValue
         * @return OwnedSecurableItem
         */
        public function saveModelFromPost($postData, $model, & $savedSuccessfully, & $modelToStringValue, $returnOnValidate = false)
        {
            $dataSanitizerClassName             = $this->getDataSanitizerUtilClassName();
            $sanitizedPostData                  = $dataSanitizerClassName::sanitizePostByDesignerTypeForSavingModel(
                                                                                                    $model, $postData);
            return $this->saveModelFromSanitizedData($sanitizedPostData, $model, $savedSuccessfully, $modelToStringValue, $returnOnValidate);
        }

        /**
         * @param $sanitizedData
         * @param object $model
         * @param bool $savedSuccessfully
         * @param string $modelToStringValue
         * @return OwnedSecurableItem
         */
        public function saveModelFromSanitizedData($sanitizedData, $model, & $savedSuccessfully, & $modelToStringValue, $returnOnValidate)
        {
            //note: the logic for ExplicitReadWriteModelPermission might still need to be moved up into the
            //post method above, not sure how this is coming in from API.
            $explicitReadWriteModelPermissions = static::resolveAndMakeExplicitReadWriteModelPermissions($sanitizedData,
                                                                                                         $model);
            $readyToUseData                     = ExplicitReadWriteModelPermissionsUtil::
                                                    removeIfExistsFromPostData($sanitizedData);

            $dataSanitizerClassName             = $this->getDataSanitizerUtilClassName();
            $sanitizedOwnerData                 = $dataSanitizerClassName::sanitizePostDataToJustHavingElementForSavingModel(
                                                                                        $readyToUseData, 'owner');
            $sanitizedDataWithoutOwner          = $dataSanitizerClassName::removeElementFromPostDataForSavingModel(
                                                                                        $readyToUseData, 'owner');
            $model->setAttributes($sanitizedDataWithoutOwner);
            $this->afterSetAttributesDuringSave($model, $explicitReadWriteModelPermissions);
            if ($explicitReadWriteModelPermissions instanceof ExplicitReadWriteModelPermissions)
            {
               $model->setExplicitReadWriteModelPermissionsForWorkflow($explicitReadWriteModelPermissions);
            }
            $isDataValid = $model->validate();
            if ($returnOnValidate)
            {
                return $model;
            }
            elseif ($isDataValid)
            {
                $modelToStringValue = strval($model);
                if ($sanitizedOwnerData != null)
                {
                    $model->setAttributes($sanitizedOwnerData);
                }
                if ($model instanceof OwnedSecurableItem)
                {
                    $passedOwnerValidation = $model->validate(array('owner'));
                }
                else
                {
                    $passedOwnerValidation = true;
                }
                if ($passedOwnerValidation && $model->save(false))
                {
                    if ($model instanceof SecurableItem)
                    {
                        $model->clearExplicitReadWriteModelPermissionsForWorkflow();
                    }
                    if ($explicitReadWriteModelPermissions != null)
                    {
                        $success = ExplicitReadWriteModelPermissionsUtil::
                        resolveExplicitReadWriteModelPermissions($model, $explicitReadWriteModelPermissions);
                        //todo: handle if success is false, means adding/removing permissions save failed.
                    }
                    $savedSuccessfully = true;
                    $this->afterSuccessfulSave($model);
                }
            }
            else
            {
            }
            return $model;
        }

        protected static function resolveAndMakeExplicitReadWriteModelPermissions($sanitizedData, $model)
        {
            if ($model instanceof SecurableItem)
            {
                return ExplicitReadWriteModelPermissionsUtil::resolveByPostDataAndModelThenMake($sanitizedData, $model);
            }
            else
            {
                return null;
            }
        }

        protected function afterSetAttributesDuringSave($model, $explicitReadWriteModelPermissions)
        {
        }

        protected function afterSuccessfulSave($model)
        {
        }

        /**
         * Validates post data in the ajax call
         * @param RedBeanModel $model
         * @param string $postVariableName
         */
        public function validateAjaxFromPost($model, $postVariableName)
        {
            $savedSuccessfully = false;
            $modelToStringValue = null;
            if (isset($_POST[$postVariableName]))
            {
                $postData         = $_POST[$postVariableName];
                $model            = $this->saveModelFromPost($postData, $model, $savedSuccessfully,
                                                                             $modelToStringValue, true);
                $errorData        = ZurmoActiveForm::makeErrorsDataAndResolveForOwnedModelAttributes($model);
                echo CJSON::encode($errorData);
                Yii::app()->end(0, false);
            }
        }

        protected function getDataSanitizerUtilClassName()
        {
            return 'PostUtil';
        }
    }
?>