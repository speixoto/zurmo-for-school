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
     * Base View for showing a selection of 'type'.  Workflow and Reporting for example extend this class
     */
    abstract class WizardTypeView extends MetadataView
    {
        /**
         * @return array
         */
        abstract protected function getTypeData();

        /**
         * @return string
         */
        protected function renderContent()
        {
            $content  = $this->renderTitleContent();
            $categoryData = $this->getTypeData();
            foreach ($categoryData as $notUsed => $categoryItems)
            {
                $content .= $this->renderMenu($categoryItems);
            }
            return $content;
        }

        /**
         * @param array $items
         * @return string
         */
        protected function renderMenu($items)
        {
            $content = '<ul class="configuration-list">';
            foreach ($items as $item)
            {
                $content .= '<li>';
                $content .= '<h4>' . $item['titleLabel'] . '</h4>';
                if(isset($item['descriptionLabel']))
                {
                    $content .= ' - ' . $item['descriptionLabel'];
                }
                $content .= ZurmoHtml::link(ZurmoHtml::tag('span', array('class' => 'z-label'), $this->getLinkText()),
                                        Yii::app()->createUrl($item['route']));
                $content .= '</li>';
            }
            $content .= '</ul>';
            return $content;
        }

        /**
         * @param $text
         */
        protected function setLinkText($text)
        {
            $this->linkText = $text;
        }

        /**
         * @return string
         */
        protected function getLinkText()
        {
            return Zurmo::t('ZurmoModule', 'Create');
        }
    }
?>