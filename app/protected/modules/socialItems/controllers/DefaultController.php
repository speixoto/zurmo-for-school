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

    class SocialItemsDefaultController extends ZurmoBaseController
    {
        /**
         * Action for saving a new social item inline edit form.
         * @param string or array $redirectUrl
         */
        public function actionInlineCreateSave($redirectUrl = null)
        {
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'inline-edit-form')
            {
                $this->actionInlineEditValidate(new SocialItem(), 'SocialItem');
            }
            $_POST['SocialItem']['explicitReadWriteModelPermissions']['type'] = ExplicitReadWriteModelPermissionsUtil::
                                                                                MIXED_TYPE_EVERYONE_GROUP;
            $this->attemptToSaveModelFromPost(new SocialItem(), $redirectUrl);
        }

        protected function actionInlineEditValidate($model)
        {
            $postData                      = PostUtil::getData();
            $postFormData                  = ArrayUtil::getArrayValue($postData, get_class($model));
            $sanitizedPostData             = PostUtil::
                                             sanitizePostByDesignerTypeForSavingModel($model, $postFormData);
            $model->setAttributes($sanitizedPostData);
            $model->validate();
            $errorData = array();
            foreach ($model->getErrors() as $attribute => $errors)
            {
                    $errorData[ZurmoHtml::activeId($model, $attribute)] = $errors;
            }
            echo CJSON::encode($errorData);
            Yii::app()->end(0, false);
        }

        protected static function getZurmoControllerUtil()
        {
            $getData                  = GetUtil::getData();
            $relatedUserId           = ArrayUtil::getArrayValue($getData, 'relatedUserId');
            if ($relatedUserId == null)
            {
                throw new NotSupportedException();
            }
            $relatedUser = User::getById((int)$relatedUserId);
            return new SocialItemZurmoControllerUtil($relatedUser);
        }
    }
?>
