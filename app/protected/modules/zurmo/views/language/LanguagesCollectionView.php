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
     * A view that displays a list of supported languages in the application.
     *
     */
    class LanguagesCollectionView extends MetadataView
    {
        protected $controllerId;

        protected $moduleId;

        protected $languagesList;

        protected $messageBoxContent;

        const LANGUAGE_STATUS_ACTIVE   = 1;
        const LANGUAGE_STATUS_INACTIVE = 2;

        public function __construct($controllerId, $moduleId, $messageBoxContent = null)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('$messageBoxContent == null || is_string($messageBoxContent)');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->messageBoxContent      = $messageBoxContent;
        }

        public function getTitle()
        {
            return Zurmo::t('ZurmoModule', 'Languages');
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        protected function renderContent()
        {
            $content  = ZurmoHtml::openTag('div');
            $content .= $this->renderTitleContent();
            $content .= $this->renderMessageBoxContent();
            $content .= ZurmoHtml::openTag('ul', array('class' => 'configuration-list'));
            $content .= $this->renderLanguagesList(self::LANGUAGE_STATUS_ACTIVE);
            $content .= $this->renderLanguagesList(self::LANGUAGE_STATUS_INACTIVE);
            $content .= ZurmoHtml::closeTag('ul');
            $content .= ZurmoHtml::closeTag('div');
            return $content;
        }

        protected function renderMessageBoxContent()
        {
            if (empty($this->messageBoxContent))
            {
                return;
            }

            return ZurmoHtml::tag('div', array(), $this->messageBoxContent);
        }

        protected function renderLanguagesList($languageStatus)
        {
            $languagesList = $this->getLanguagesList($languageStatus);

            if (empty($languagesList))
            {
                return;
            }

            $content = '';
            foreach ($languagesList as $languageCode => $languageData)
            {
                $content .= $this->renderLanguageRow($languageCode, $languageData);
            }

            return $content;
        }

        public function renderLanguageRow($languageCode, $languageData=null)
        {

            if (!$languageData)
            {
                $languageData = $this->getLanguageDataByLanguageCode($languageCode);
            }

            $content = ZurmoHtml::openTag(
                'li',
                array('id'=>'language-row-' . $languageCode)
            );
            $content .= ZurmoHtml::tag('h4', array(), $languageData['label']);
            if ($languageData['active'])
            {
                $metaData = Yii::app()->languageHelper->getActiveLanguageMetaData($languageCode);
                if (!empty($metaData) && isset($metaData['lastUpdate']))
                {
                    $content .= ' - ' . Zurmo::t(
                        'ZurmoModule', 'Last updated on {date}',
                        array('{date}'=>DateTimeUtil::convertTimestampToDbFormatDateTime($metaData['lastUpdate']))
                    );
                }
                $content .= $this->renderUpdateButton($languageCode, $languageData);
                $content .= $this->renderInactivateButton($languageCode, $languageData);
            }
            else
            {
                $content .= $this->renderActivateButton($languageCode, $languageData);
            }
            $content .= ZurmoHtml::closeTag('li');

            return $content;
        }

        protected function renderUpdateButton($languageCode, $languageData)
        {
            assert('is_string($languageCode)');
            assert('is_array($languageData)');
            $linkHtml = array('class' => 'update-link');
            return ZurmoHtml::ajaxLink(
                ZurmoHtml::tag(
                    'span',
                    array('class'=>'z-label'),
                    Zurmo::t('ZurmoModule', 'Update')
                ),
                Yii::app()->createUrl('zurmo/language/update/languageCode/' . $languageCode),
                array('replace' => '#language-row-' . $languageCode),
                $linkHtml
            );
        }

        protected function renderInactivateButton($languageCode, $languageData)
        {
            assert('is_string($languageCode)');
            assert('is_array($languageData)');

            $linkHtml = array('class' => 'inactivate-link');
            if (!$languageData['canInactivate'])
            {
                $linkHtml['class'] .= ' disabled';
            }

            return ZurmoHtml::ajaxLink(
                ZurmoHtml::tag(
                    'span',
                    array('class'=>'z-label'),
                    Zurmo::t('ZurmoModule', 'Inactivate')
                ),
                Yii::app()->createUrl('zurmo/language/inactivate/languageCode/' . $languageCode),
                array('replace' => '#language-row-' . $languageCode),
                $linkHtml
            );
        }

        protected function renderActivateButton($languageCode, $languageData)
        {
            assert('is_string($languageCode)');
            assert('is_array($languageData)');
            $linkHtml = array(
                'class' => 'activate-link attachLoading z-button',
                'onclick' => "attachLoadingOnSubmit('language-row-$languageCode');"
            );
            return ZurmoHtml::ajaxLink(
                ZurmoHtml::tag('span', array('class'=>'z-spinner'), '') .
                ZurmoHtml::tag('span', array('class'=>'z-icon'), '') . 
                ZurmoHtml::tag(
                    'span',
                    array('class'=>'z-label'),
                    Zurmo::t('ZurmoModule','Activate')
                ),
                Yii::app()->createUrl('zurmo/language/activate/languageCode/' . $languageCode),
                array('replace' => '#language-row-' . $languageCode),
                $linkHtml
            );
        }

        protected function getLanguagesList($languageStatus=null)
        {
            if (is_array($this->languagesList) && !empty($this->languagesList))
            {
                switch ($languageStatus)
                {
                    case self::LANGUAGE_STATUS_ACTIVE:
                        return $this->languagesList[self::LANGUAGE_STATUS_ACTIVE];
                        break;
                    case self::LANGUAGE_STATUS_INACTIVE:
                        return $this->languagesList[self::LANGUAGE_STATUS_INACTIVE];
                        break;
                    case null:
                        return $this->languagesList;
                        break;
                }
            }

            $languagesList = array(
                self::LANGUAGE_STATUS_ACTIVE   => array(),
                self::LANGUAGE_STATUS_INACTIVE => array()
            );
            $languagesData = self::getLanguagesData();
            foreach ($languagesData as $languageCode => $languageData)
            {
                if ($languageData['active'])
                {
                    $status = self::LANGUAGE_STATUS_ACTIVE;
                }
                else
                {
                    $status = self::LANGUAGE_STATUS_INACTIVE;
                }

                $languagesList[$status][$languageCode] = $languageData;
            }

            $languagesList[self::LANGUAGE_STATUS_ACTIVE] = ArrayUtil::subValueSort(
                $languagesList[self::LANGUAGE_STATUS_ACTIVE],
                'label',
                'asort'
            );
            $languagesList[self::LANGUAGE_STATUS_INACTIVE] = ArrayUtil::subValueSort(
                $languagesList[self::LANGUAGE_STATUS_INACTIVE],
                'label',
                'asort'
            );

            $this->languagesList = $languagesList;
            return $this->getLanguagesList($languageStatus);
        }

        public static function getLanguageDataByLanguageCode($languageCode)
        {
            $languagesData = self::getLanguagesData();
            if (isset($languagesData[$languageCode]))
            {
                return $languagesData[$languageCode];
            }

            return false;
        }

        public static function getLanguagesData()
        {
            $activeLanguages    = Yii::app()->languageHelper->getActiveLanguages();
            $languagesData       = array();
            foreach (Yii::app()->languageHelper->getSupportedLanguagesData() as $language => $label)
            {
                $languagesData[$language] = array('label'         => $label,
                                                 'active'        => in_array($language, $activeLanguages),
                                                 'canInactivate' =>
                                                        Yii::app()->languageHelper->canInactivateLanguage($language));
            }
            return $languagesData;
        }
    }
?>