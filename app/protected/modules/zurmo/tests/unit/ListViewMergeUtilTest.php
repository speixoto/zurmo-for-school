<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    class ListViewMergeUtilTest extends ZurmoBaseTest
    {
        public $selectedModels          = array();

        public $modelClass;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        /**
         * @covers ListViewMergeUtil::getSelectedModelsListForMerge
         * @covers ListViewMergeUtil::setPrimaryModelForListViewMerge
         */
        public function testSetPrimaryModelForListViewMerge()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $this->setFirstModel();
            $this->setSecondModel();
            $modelsList = ListViewMergeUtil::getSelectedModelsListForMerge($this->modelClass,
                                                        array('selectedIds' => $this->selectedModels[0]->id .
                                                                                ',' .
                                                                                $this->selectedModels[1]->id));
            $formModel  = new ModelsListDuplicateMergedModelForm();
            $formModel->selectedModels = $modelsList;
            ListViewMergeUtil::setPrimaryModelForListViewMerge($formModel,
                                                               array('primaryModelId' => $this->selectedModels[0]->id));
            $this->primaryModel = $formModel->primaryModel;
            $this->assertEquals($this->selectedModels[0]->id, $formModel->primaryModel->id);
        }

        /**
         * @depends testSetPrimaryModelForListViewMerge
         */
        public function testProcessCopyRelationsAndDeleteNonPrimaryModelsInMerge()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $this->setSelectedModels();
            $this->setRelatedModels();
            ListViewMergeUtil::processCopyRelationsAndDeleteNonPrimaryModelsInMerge($this->getPrimaryModel(),
                                                                                    array('selectedIds' => $this->selectedModels[0]->id .
                                                                                            ',' .
                                                                                            $this->selectedModels[1]->id));
            $this->validatePrimaryModelData();
        }

        protected function validatePrimaryModelData()
        {

        }

        protected function setFirstModel()
        {

        }

        protected function setSecondModel()
        {

        }

        protected function setRelatedModels()
        {

        }

        protected function setSelectedModels()
        {

        }

        protected function getPrimaryModel()
        {
            return $this->selectedModels[0];
        }
    }
?>