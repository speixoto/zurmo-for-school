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

        protected $languagesData;

        protected $languagesList;

        protected $messageBoxContent;

        const LANGUAGE_STATUS_ACTIVE   = 1;
        const LANGUAGE_STATUS_INACTIVE = 2;

        public function __construct($controllerId, $moduleId, $languagesData, $messageBoxContent = null)
        {
            assert('is_string($controllerId)');
            assert('is_string($moduleId)');
            assert('is_array($languagesData)');
            assert('$messageBoxContent == null || is_string($messageBoxContent)');
            $this->controllerId           = $controllerId;
            $this->moduleId               = $moduleId;
            $this->languagesData          = $languagesData;
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
            $content .= $this->renderActiveLanguagesList();
            $content .= $this->renderInactiveLanguagesList();
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

        protected function renderActiveLanguagesList()
        {
            $languagesList = $this->getLanguagesList();

            if (empty($languagesList[self::LANGUAGE_STATUS_ACTIVE]))
            {
                return;
            }

            $content = '';
            foreach ($languagesList[self::LANGUAGE_STATUS_ACTIVE] as $languageCode => $languageData)
            {
                $content .= ZurmoHtml::openTag('li');
                $content .= ZurmoHtml::tag('h4', array(), $languageData['label']);

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
                $content .= ZurmoHtml::closeTag('li');
            }

            return $content;
        }

        protected function renderInactiveLanguagesList()
        {
            $languagesList = $this->getLanguagesList();

            if (empty($languagesList[self::LANGUAGE_STATUS_INACTIVE]))
            {
                return;
            }

            $content = '';
            foreach ($languagesList[self::LANGUAGE_STATUS_INACTIVE] as $languageCode => $languageData)
            {
                $content .= ZurmoHtml::openTag('li');
                $content .= ZurmoHtml::tag('h4', array(), $languageData['label']);
                $content .= $this->renderActivateButton($languageCode, $languageData);
                $content .= ZurmoHtml::closeTag('li');
            }

            return $content;
        }

        protected function renderUpdateButton($languageCode, $languageData)
        {
            assert('is_string($languageCode)');
            assert('is_array($languageData)');
            return ZurmoHtml::link(
                ZurmoHtml::tag(
                    'span',
                    array('class'=>'z-label'),
                    Zurmo::t('ZurmoModule', 'Update')
                ),
                Yii::app()->createUrl('zurmo/language/update/' . $languageCode)
            );
        }

        protected function renderInactivateButton($languageCode, $languageData)
        {
            assert('is_string($languageCode)');
            assert('is_array($languageData)');
            return ZurmoHtml::link(
                ZurmoHtml::tag(
                    'span',
                    array('class'=>'z-label'),
                    Zurmo::t('ZurmoModule', 'Inactivate')
                    ),
                Yii::app()->createUrl('zurmo/language/inactivate/' . $languageCode)
            );
        }

        protected function renderActivateButton($languageCode, $languageData)
        {
            assert('is_string($languageCode)');
            assert('is_array($languageData)');
            return ZurmoHtml::link(
                ZurmoHtml::tag(
                    'span',
                    array('class'=>'z-label'),
                    Zurmo::t('ZurmoModule','Activate')
                ),
                Yii::app()->createUrl('zurmo/language/activate/' . $languageCode)
            );
        }

        protected function getLanguagesList()
        {
            if (is_array($this->languagesList) && !empty($this->languagesList))
            {
                return $this->languagesList;
            }

            $languagesList = array(
                self::LANGUAGE_STATUS_ACTIVE   => array(),
                self::LANGUAGE_STATUS_INACTIVE => array()
            );
            foreach ($this->languagesData as $languageCode => $languageData)
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

            usort($languagesList[self::LANGUAGE_STATUS_ACTIVE],
                  array($this, 'compareLanguagesListElements'));
            usort($languagesList[self::LANGUAGE_STATUS_INACTIVE],
                  array($this, 'compareLanguagesListElements'));

            $this->languagesList = $languagesList;
            return $this->languagesList;
        }

        protected function compareLanguagesListElements($a, $b)
        {
            return strcmp($a['label'], $b['label']);
        }
    }
?>