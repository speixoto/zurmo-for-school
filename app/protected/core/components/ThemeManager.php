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
     * Extension of CThemeManager to help manage the theme colors and background textures
     */
    class ThemeManager extends CThemeManager
    {
        const DEFAULT_THEME_COLOR = 'blue';

        public function resolveAndGetThemeColorValue(User $user)
        {
            assert('$user instanceOf User && $user->id > 0');
            if ( null != $themeColor = ZurmoConfigurationUtil::getByUserAndModuleName($user, 'ZurmoModule', 'themeColor'))
            {
                return $themeColor;
            }
            else
            {
                return $this->getDefaultThemeColor();
            }
        }

        public function resolveAndGetBackgroundTextureValue(User $user)
        {
            assert('$user instanceOf User && $user->id > 0');
            if ( null != $themeColor = ZurmoConfigurationUtil::getByUserAndModuleName($user, 'ZurmoModule', 'backgroundTexture'))
            {
                return $themeColor;
            }
            else
            {
                return null;
            }
        }

        public function getActiveThemeColor()
        {
            if (Yii::app()->user->userModel == null)
            {
                return $this->getDefaultThemeColor();
            }
            else
            {
                return $this->resolveAndGetThemeColorValue(Yii::app()->user->userModel);
            }
        }

        public function getActiveBackgroundTexture()
        {
            if (Yii::app()->user->userModel == null)
            {
                return null;
            }
            else
            {
                return $this->resolveAndGetBackgroundTextureValue(Yii::app()->user->userModel);
            }
        }

        public function setThemeColorValue(User $user, $value)
        {
            assert('is_string($value)');
            ZurmoConfigurationUtil::setByUserAndModuleName($user, 'ZurmoModule', 'themeColor', $value);
        }

        public function setBackgroundTextureValue(User $user, $value)
        {
            assert('is_string($value) || $value == null');
            ZurmoConfigurationUtil::setByUserAndModuleName($user, 'ZurmoModule', 'backgroundTexture', $value);
        }

        public function getDefaultThemeColor()
        {
            return self::DEFAULT_THEME_COLOR;
        }

        public function getThemeColorNamesAndLabels()
        {
            $data = array('blue'        => Zurmo::t('Core', 'Blue'),
                          'brown'       => Zurmo::t('Core', 'Brown'),
                          'cherry'      => Zurmo::t('Core', 'Cherry'),
                          'honey'       => Zurmo::t('Core', 'Honey'),
                          'lime'        => Zurmo::t('Core', 'Lime'),
                          'turquoise'   => Zurmo::t('Core', 'Turquoise'),
                          'violet'      => Zurmo::t('Core', 'Violet'),
                          'sunrise'     => Zurmo::t('Core', 'Sunrise'),
                          'marble'      => Zurmo::t('Core', 'Marble'),
                          'purple-haze' => Zurmo::t('Core', 'Purple Haze'),
                          'flat-cement' => Zurmo::t('Core', 'Flat Cement'),
                          'amazon'      => Zurmo::t('Core', 'Amazon'),
                          'sweden'      => Zurmo::t('Core', 'Sweden'),
                          'pink'        => Zurmo::t('Core', 'Pink'));
            return $data;
        }

        public function getBackgroundTextureNamesAndLabels()
        {
            $data = array('exclusive-paper'       => Zurmo::t('Core', 'Exclusive Paper'),
                          'french-stucco'         => Zurmo::t('Core', 'French Stucco'),
                          'light-noise-diagonal'  => Zurmo::t('Core', 'Light Noise'),
                          'light-toast'           => Zurmo::t('Core', 'Light Toast'),
                          'diagonal-noise'        => Zurmo::t('Core', 'Noise'),
                          'paper'                 => Zurmo::t('Core', 'Paper'),
                          'circles'               => Zurmo::t('Core', 'Circle'),
                          'whitey'                => Zurmo::t('Core', 'White Fabric'),
                          'fabric-plaid'          => Zurmo::t('Core', 'Fabric Plaid'),
                          'cartographer-light'    => Zurmo::t('Core', 'Cartographer Light'),
                          'wood'                  => Zurmo::t('Core', 'Wood'),
                          'black-linen-2'         => Zurmo::t('Core', 'Black Linen'),
                          'carbon-fibre'          => Zurmo::t('Core', 'Carbon Fibre'),
                          'cartographer'          => Zurmo::t('Core', 'Cartographer'),
                          'concrete-wall'         => Zurmo::t('Core', 'Concrete Wall'),
                          'denim'                 => Zurmo::t('Core', 'Denim'),
                          'low-contrast-linen'    => Zurmo::t('Core', 'Low Contrast Linen'));
            return $data;
        }

        public function getThemeColorNamesAndUnlockedAtLevel()
        {
            $data = array('blue'        => 1,
                          'brown'       => 1,
                          'cherry'      => 1,
                          'honey'       => 1,
                          'lime'        => 1,
                          'turquoise'   => 1,
                          'violet'      => 1,
                          'sunrise'     => 2,
                          'marble'      => 3,
                          'purple-haze' => 4,
                          'flat-cement' => 5,
                          'amazon'      => 6,
                          'sweden'      => 7,
                          'pink'        => 8);
            return $data;
        }

        public function getBackgroundTextureNamesAndUnlockedAtLevel()
        {
            $data = array('exclusive-paper'       => 1,
                          'french-stucco'         => 1,
                          'light-noise-diagonal'  => 1,
                          'light-toast'           => 1,
                          'diagonal-noise'        => 1,
                          'paper'                 => 1,
                          'circles'               => 4,
                          'whitey'                => 5,
                          'fabric-plaid'          => 6,
                          'cartographer-light'    => 7,
                          'wood'                  => 8,
                          'black-linen-2'         => 9,
                          'carbon-fibre'          => 10,
                          'carbon-fibre-big'      => 11,
                          'cartographer'          => 12,
                          'concrete-wall'         => 13,
                          'denim'                 => 14,
                          'low-contrast-linen'    => 15);
            return $data;
        }

        public function getThemeColorNamesAndColors()
        {
            $data = array('blue'        => array('#545454', '#282A76', '#7CB830', '#97c43d', '#464646'),
                          'brown'       => array('#ff0000', '#282A76', '#7CB830', '#97c43d', '#464646'),
                          'cherry'      => array('#545454', '#282A76', '#7CB830', '#97c43d', '#464646'),
                          'honey'       => array('#545454', '#282A76', '#7CB830', '#97c43d', '#464646'),
                          'lime'        => array('#545454', '#282A76', '#7CB830', '#97c43d', '#464646'),
                          'turquoise'   => array('#545454', '#282A76', '#7CB830', '#97c43d', '#464646'),
                          'violet'      => array('#545454', '#282A76', '#7CB830', '#97c43d', '#464646'),
                          'sunrise'     => array('#545454', '#282A76', '#7CB830', '#97c43d', '#464646'),
                          'marble'      => array('#545454', '#282A76', '#7CB830', '#97c43d', '#464646'),
                          'purple-haze' => array('#545454', '#282A76', '#7CB830', '#97c43d', '#464646'),
                          'flat-cement' => array('#545454', '#282A76', '#7CB830', '#97c43d', '#464646'),
                          'amazon'      => array('#545454', '#282A76', '#7CB830', '#97c43d', '#464646'),
                          'sweden'      => array('#545454', '#282A76', '#7CB830', '#97c43d', '#464646'),
                          'pink'        => array('#545454', '#282A76', '#7CB830', '#97c43d', '#464646')
            );
            return $data;
        }
    }
?>