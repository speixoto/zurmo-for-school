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
     * Helper class for working with report results grid data and views
     */
    class ReportResultsGridUtil
    {
        /**
         * @param string $attribute
         * @param ReportResultsRowData $data
         * @return null | string
         */
        public static function makeUrlForLink($attribute, ReportResultsRowData $data)
        {
            assert('is_string($attribute)');
            if (null == $model = $data->getModel($attribute))
            {
                return null;
            }
            $moduleClassName = self::resolveModuleClassName($attribute, $data);
            return static::makeUrl($data->getModel($attribute)->id, $moduleClassName);
        }

        protected static function resolveModuleClassName($attribute, ReportResultsRowData $data)
        {
            if (get_class($data->getModel($attribute)) == 'Contact' &&
                LeadsUtil::isStateALead($data->getModel($attribute)->state))
            {
                return 'LeadsModule';
            }
            else
            {
                return $data->getModel($attribute)->getModuleClassName();
            }
        }

        public static function makeStringForLinkOrLinks($attribute, ReportResultsRowData $data,
                                                        $shouldRenderMultipleLinks, $attributeString)
        {
            assert('is_string($attribute)');
            if (null == $model = $data->getModel($attribute))
            {
                return null;
            }
            $modelClassName  = get_class($data->getModel($attribute));
            $moduleClassName = self::resolveModuleClassName($attribute, $data);
            if (!$shouldRenderMultipleLinks)
            {
                $url = static::makeUrl($model->id, $moduleClassName);
                return ZurmoHtml::link($attributeString, $url, array("target" => "new"));
            }
            return static::makeStringForMultipleLinks($attributeString,
                                                      $modelClassName,
                                                      $moduleClassName);
        }

        public static function makeStringForMultipleLinks($value, $modelClassName, $moduleClassName)
        {
            assert('is_string($modelClassName)');
            $models          = $modelClassName::getByName(strval($value));
            if (count($models) <= 1)
            {
                $url = static::makeUrl($models[0]->id, $moduleClassName);
                return ZurmoHtml::link($value, $url, array("target" => "new"));
            }
            else
            {
                $qtipContent = null;
                $count       = 1;
                foreach ($models as $model)
                {
                    if ($count > 10)
                    {
                        $qtipContent    .= Zurmo::t('ReportsModule', 'There are more results. ' .
                                                    'Only displaying the first {count}.', array('{count}' => $count - 1));
                        break;
                    }
                    $id              = $model->id;
                    $url             = static::makeUrl($id, $moduleClassName);
                    $qtipContent    .= ZurmoHtml::link('Link ' . $count++, $url, array("target" => "new")) . '<br />';
                }
                $content     = '<span id="report-multiple-link-' . $moduleClassName . '-' . $id . '">';
                $content    .= $value;
                $content    .= '<span class="tooltip">' . count($models) . '</span>';
                $content    .= '</span>';
                $options     = array('content'  => array(
                                                    'title' => $value,
                                                    'text'  => $qtipContent,
                                                  ),
                                     'position' => array('my'  => 'bottom right',
                                                         'at'  => 'top right'),
                                     'hide'     => array('event' => 'click unfocus'),
                                     'show'     => array('event' => 'click', 'solo' => true),
                                     );
                $qtip        = new ZurmoTip();
                $qtip->addQTip("#report-multiple-link-" . $moduleClassName . "-" . $id, $options);
                return $content;
            }
        }

        protected static function makeUrl($id, $moduleClassName)
        {
            assert('is_int($id)');
            assert('is_string($moduleClassName)');
            return Yii::app()->createUrl('/' . $moduleClassName::getDirectoryName() . '/default/details',
                                         array('id' => $id));
        }
    }
?>