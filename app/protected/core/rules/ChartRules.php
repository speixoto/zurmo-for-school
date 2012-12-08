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
     * Rules for working with charts that can be used for reporting and dashboards
     */
    class ChartRules
    {
        const TYPE_BAR_2D                = 'Bar2D';

        const TYPE_BAR_3D                = 'Bar3D';

        const TYPE_COLUMN_2D             = 'Column2D';

        const TYPE_COLUMN_3D             = 'Column3D';

        const TYPE_DONUT_2D              = 'Donut2D';

        const TYPE_DONUT_3D              = 'Donut3D';

        const TYPE_PIE_2D                = 'Pie2D';

        const TYPE_PIE_3D                = 'Pie3D';

        const TYPE_STACKED_BAR_2D        = 'StackedBar2D';

        const TYPE_STACKED_BAR_3D        = 'StackedBar3D';

        const TYPE_STACKED_COLUMN_2D     = 'StackedColumn2D';

        const TYPE_STACKED_COLUMN_3D     = 'StackedColumn3D';

        /**
         * @return array of chart types that require a second series and range to render.
         */
        public static function getChartTypesRequiringSecondInputs()
        {
            return array(self::TYPE_STACKED_BAR_2D, self::TYPE_STACKED_BAR_3D, self::TYPE_STACKED_COLUMN_2D,
                         self::TYPE_STACKED_COLUMN_3D);
        }

            public static function getTranslatedTypeLabel($type)
        {
            assert('is_string($type)');
            $labels             = self::translatedTypeLabels();
            if(isset($labels[$type]))
            {
                return $labels[$type];
            }
            throw new NotSupportedException();
        }

        public static function translatedTypeLabels()
        {
            return array(ChartRules::TYPE_BAR_2D             => Yii::t('Default', '2D Horizontal Bar Graph'),
                         ChartRules::TYPE_BAR_3D             => Yii::t('Default', '3D Horizontal Bar Graph'),
                         ChartRules::TYPE_COLUMN_2D          => Yii::t('Default', '2D Vertical Bar Graph'),
                         ChartRules::TYPE_COLUMN_3D          => Yii::t('Default', '3D Vertical Bar Graph'),
                         ChartRules::TYPE_DONUT_2D           => Yii::t('Default', 'Donut 2D'),
                         ChartRules::TYPE_DONUT_3D           => Yii::t('Default', 'Donut 3D'),
                         ChartRules::TYPE_PIE_2D             => Yii::t('Default', 'Pie 2D'),
                         ChartRules::TYPE_PIE_3D             => Yii::t('Default', 'Pie 3D'),
                         ChartRules::TYPE_STACKED_BAR_2D     => Yii::t('Default', 'Stacked Bar 2D'),
                         ChartRules::TYPE_STACKED_BAR_3D     => Yii::t('Default', 'Stacked Bar 3D'),
                         ChartRules::TYPE_STACKED_COLUMN_2D  => Yii::t('Default', 'Stacked Column 2D'),
                         ChartRules::TYPE_STACKED_COLUMN_3D  => Yii::t('Default', 'Stacked Column 3D'),
            );
        }

        public static function availableTypes()
        {
            return array(ChartRules::TYPE_BAR_2D,
                         ChartRules::TYPE_BAR_3D,
                         ChartRules::TYPE_COLUMN_2D,
                         ChartRules::TYPE_COLUMN_3D,
                         ChartRules::TYPE_DONUT_2D,
                         ChartRules::TYPE_DONUT_3D,
                         ChartRules::TYPE_PIE_2D,
                         ChartRules::TYPE_PIE_3D,
                         ChartRules::TYPE_STACKED_BAR_2D,
                         ChartRules::TYPE_STACKED_BAR_3D,
                         ChartRules::TYPE_STACKED_COLUMN_2D,
                         ChartRules::TYPE_STACKED_COLUMN_3D,
            );
        }

        public static function getSingleSeriesDataAndLabels()
        {
            $translatedLabels = static::translatedTypeLabels();
            return array(
                ChartRules::TYPE_COLUMN_2D => $translatedLabels[ChartRules::TYPE_COLUMN_2D],
                ChartRules::TYPE_COLUMN_3D => $translatedLabels[ChartRules::TYPE_COLUMN_3D],
                ChartRules::TYPE_BAR_2D    => $translatedLabels[ChartRules::TYPE_BAR_2D],
                ChartRules::TYPE_BAR_3D    => $translatedLabels[ChartRules::TYPE_BAR_3D],
                ChartRules::TYPE_DONUT_2D  => $translatedLabels[ChartRules::TYPE_DONUT_2D],
                ChartRules::TYPE_DONUT_3D  => $translatedLabels[ChartRules::TYPE_DONUT_3D],
                ChartRules::TYPE_PIE_2D    => $translatedLabels[ChartRules::TYPE_PIE_2D],
                ChartRules::TYPE_PIE_3D    => $translatedLabels[ChartRules::TYPE_PIE_3D],
            );
        }
    }
?>