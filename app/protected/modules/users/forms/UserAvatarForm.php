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
     * Form to edit the user avatar
     * @param integer       (0=noAvatar, 1=primaryEmail, 2=customEmail, 3=galleryAvatar)
     * @param string        custom email for gravatar
     * @param galleryAvatar id of the gallery avatar
     */
    class UserAvatarForm extends ModelForm
    {
        
        public $avatar;                

        public function __construct(User $model)
        {
            $this->model = $model;
        }
        
        public function rules()
        {
            return array(
                array('avatar', 'required'),
                array('avatar', 'validateAvatar'),                
            );
        }

        public function attributeLabels()
        {
            return array(
                'avatar'              => Yii::t('Default', 'User Avatar'),                
            );
        }
        
        public function afterValidate()
        {
            parent::afterValidate();
            $this->model->setAvatar($this->avatar);
        }
        
        public function validateAvatar($attribute, $params)
        {
            $avatar = $this->$attribute;
            if ($avatar['type'] == '2' && $avatar['customAvatarEmail'] !== '')
            {
                // make sure string length is limited to avoid DOS attacks
                $pattern = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';
                $valid=is_string($avatar['customAvatarEmail']) && strlen($avatar['customAvatarEmail'])<=254 && (preg_match($pattern,$avatar['customAvatarEmail']));
                if (!$valid)
                {
                    $this->addError('avatar',
                        Yii::t('Default', 'Your did not chose a valid email address, please fix it.'));
                }
            }
        }
    }
?>