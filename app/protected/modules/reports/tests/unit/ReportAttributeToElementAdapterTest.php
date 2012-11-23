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

    class ReportAttributeToElementAdapterTest extends ZurmoBaseTest
    {
        public function testGetFilterContentForRowsAndColumns()
        {
            $inputPrefixData      = array('something');
            $reportType           = Report::TYPE_ROWS_AND_COLUMNS;
            $moduleClassName      = 'ReportsTestModule';
            $modelClassName       = 'ReportModelTestItem';
            $treeType             = ReportRelationsAndAttributesTreeView::TREE_TYPE_FILTERS;
            $modelToReportAdapter = ModelRelationsAndAttributesToReportAdapter::make($moduleClassName,
                                                                                     $modelClassName,
                                                                                     $reportType);
            $model                = new FilterForReportForm($modelClassName, $reportType);

            $form                 = new ZurmoActiveForm();

            //Test a string attribute
            $model->attributeIndexOrDerivedType = 'string';
            $adapter                            = new ReportAttributeToElementAdapter($modelToReportAdapter,
                                                      $inputPrefixData, $model, $form, $treeType);
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertFalse(strpos($link, 'operator') === false);

            //Test a boolean attribute which does not have an attribute
            $model->attributeIndexOrDerivedType = 'boolean';
            $adapter                            = new ReportAttributeToElementAdapter($modelToReportAdapter,
                                                      $inputPrefixData, $model, $form, $treeType);
            $content                            = $adapter->getContent();
            $this->assertNotNull($content);
            $this->assertTrue(strpos($link, 'operator') === false);

            //we could test each attribute type.this will instantiate and test through the various elements
            //but we still have to test like between vs. not between.  Also existing data populating vs no data.

            //yes we should do them all testing here.
            //test relationship scenarios? hasOne___name

        }

        /**
         * @depends testGetFilterContentForRowsAndColumns
         */
        public function testGetGroupByContentForRowsAndColumns()
        {
            //todo: think about group by axis check check no axis here.
            $this->fail();
        }

        /**
         * @depends testGetGroupByContentForRowsAndColumns
         */
        public function testGetOrderByContentForRowsAndColumns()
        {
            //todo:
            $this->fail();
        }

        /**
         * @depends testGetOrderByContentForRowsAndColumns
         */
        public function testGetDisplayAttributeContentForRowsAndColumns()
        {
            //todo:
            $this->fail();
        }

        /**
         * @depends testGetDisplayAttributeContentForRowsAndColumns
         */
        public function testGetDrillDownDisplayAttributeContentForRowsAndColumns()
        {
            //todo:
            $this->fail();
        }

        //etc. etc.


        public function testGetContentForSummation()
        {
            //todo:
            //$this->fail();
        }

        public function testGetContentForMatrix()
        {
            //todo:
           //$this->fail();
        }
    }
?>