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
     * Extend this class if the data provider should support DataAnalyzer classes.  These abstract methods
     * act as an interface for any methods requiring the data provider to be an AnalyzerSupportedDataProvider
     */
    abstract class AnalyzerSupportedDataProvider extends CDataProvider
    {
        /**
         * Get the count of rows in a table and filter by a where sql part if supplied.
         * @param string $where
         */
        abstract public function getCountByWhere($where);

        /**
         * Get the grouped count by a column name and filter by a where sql part if supplied.  Each row has 2
         * resulting elements, 'count' and the column named the $groupbyColumnName
         * @param string $groupbyColumnName
         * @param string $where
         */
        abstract public function getCountDataByGroupByColumnName($groupbyColumnName, $where = null);
    }
?>