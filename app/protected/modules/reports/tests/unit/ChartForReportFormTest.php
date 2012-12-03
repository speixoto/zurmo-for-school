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

    class ChartForReportFormTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setup()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testSetAndGetChart()
        {
            $chart                              = new ChartForReportForm();
            $chart->type         = 'Bar2D';
            $chart->firstSeries  = 'dropDown';
            $chart->firstRange   = 'float__Summation';
            $chart->secondSeries = 'radioDropDown';
            $chart->secondRange  = 'integer__Summation';
            $this->assertEquals('Bar2D',              $chart->type);
            $this->assertEquals('dropDown',           $chart->firstSeries);
            $this->assertEquals('float__Summation',   $chart->firstRange);
            $this->assertEquals('radioDropDown',      $chart->secondSeries);
            $this->assertEquals('integer__Summation', $chart->secondRange);
        }

        /**
         * @depends testSetAndGetChart
         */
        public function testValidate()
        {
            $chart                              = new ChartForReportForm();
            $validated = $chart->validate();
            $this->assertTrue($validated);

            $chart->type                         = 'Bar2D';
            $validated                           = $chart->validate();
            $this->assertFalse($validated);
            $errors                              = $chart->getErrors();
            $compareErrors                       = array('firstSeries'  => array('First Series cannot be blank.'),
                                                         'firstRange'   => array('First Range cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            $chart->firstSeries                  = 'dropDown';
            $chart->firstRange                   = 'float__Summation';
            $validated                           = $chart->validate();
            $this->assertTrue($validated);

            //When the type is a StackedColumn2D, it should require the second series and range
            $chart->type                         = 'StackedColumn2D';
            $validated                           = $chart->validate();
            $this->assertFalse($validated);
            $errors                              = $chart->getErrors();
            $compareErrors                       = array('secondSeries'  => array('Second Series cannot be blank.'),
                                                         'secondRange'   => array('Second Range cannot be blank.'));
            $this->assertEquals($compareErrors, $errors);

            $chart->secondSeries                 = 'integer';
            $chart->secondRange                  = 'integer__Summation';
            $validated                           = $chart->validate();
            $this->assertTrue($validated);
        }
    }
?>