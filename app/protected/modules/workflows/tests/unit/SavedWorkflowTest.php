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

    class SavedWorkflowTest extends ZurmoBaseTest
    {
        public function testSetAndGetModel()
        {
            $savedWorkflow                  = new SavedWorkflow();
            $savedWorkflow->name            = 'some workflow';
            $savedWorkflow->description     = 'description';
            $savedWorkflow->moduleClassName = 'moduleClassName';
            $savedWorkflow->triggerOn       = Workflow::TRIGGER_ON_NEW;
            $savedWorkflow->type            = 'some type';
            $savedWorkflow->serializedData  = serialize(array('something'));
            $saved                        = $savedWorkflow->save();
            $this->assertTrue($saved);
            $savedWorkflowId                = $savedWorkflow->id;
            $savedWorkflow->forget();

            $savedWorkflow                  = SavedWorkflow::getById($savedWorkflowId);
            $this->assertEquals('some workflow',               $savedWorkflow->name);
            $this->assertEquals('description',                 $savedWorkflow->description);
            $this->assertEquals('moduleClassName',             $savedWorkflow->moduleClassName);
            $this->assertEquals(Workflow::TRIGGER_ON_NEW,      $savedWorkflow->triggerOn);
            $this->assertEquals('some type',                   $savedWorkflow->type);
            $this->assertEquals(serialize(array('something')), $savedWorkflow->serializedData);
        }

        public function testGetActiveByModuleClassNameAndIsNewModel()
        {
            //todo:
            //getActiveByModuleClassNameAndIsNewModel($moduleClassName, $isNewModel)
            $this->fail();
        }
    }
?>