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

    class MashableUtilTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            Yii::app()->user->userModel = User::getByUsername('super');
            $billy = UserTestHelper::createBasicUser('billy');
        }

        public function testCreateMashableInboxRulesByModel()
        {
            $mashableInboxRules = MashableUtil::createMashableInboxRulesByModel('conversation');
            $this->assertEquals('ConversationMashableInboxRules', get_class($mashableInboxRules));
        }

        public function testGetModelDataForCurrentUserByInterfaceName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $mashableModelData = MashableUtil::getModelDataForCurrentUserByInterfaceName('MashableInboxInterface');
            $this->assertEquals(3, count($mashableModelData));
            Yii::app()->user->userModel = User::getByUsername('billy');
            $mashableModelData = MashableUtil::getModelDataForCurrentUserByInterfaceName('MashableInboxInterface');
            $this->assertEquals(1, count($mashableModelData));
        }

        public function testGetUnreadCountForCurrentUserByModelClassName()
        {
            Yii::app()->user->userModel = User::getByUsername('super');
            $rules = $this->getMock('ConversationMashableInboxRules', array('getUnreadCountForCurrentUser'));
            $rules->expects($this->once())
                  ->method('getUnreadCountForCurrentUser')
                  ->will($this->returnValue(100));
            $mashableUtil = $this->getMockClass('MashableUtil', array('createMashableInboxRulesByModel'));
            $mashableUtil::staticExpects($this->once())
                ->method('createMashableInboxRulesByModel')
                ->will($this->returnValue($rules));
            $count = $mashableUtil::getUnreadCountForCurrentUserByModelClassName('Conversation');
            $this->assertEquals(100, $count);
        }

        public function testGetUnreadCountMashableInboxForCurrentUser()
        {
            $mashableInboxModels = array(
                'Conversation'  => 'conversationLabel',
                'Mission'       => 'missionLabel',
            );
            $mashableUtil = $this->getMockClass('MashableUtil', array('getModelDataForCurrentUserByInterfaceName',
                                                                      'getUnreadCountForCurrentUserByModelClassName'));
            $mashableUtil::staticExpects($this->once())
                ->method('getModelDataForCurrentUserByInterfaceName')
                ->will($this->returnValue($mashableInboxModels));
            $mashableUtil::staticExpects($this->exactly(2))
                ->method('getUnreadCountForCurrentUserByModelClassName')
                ->will($this->onConsecutiveCalls(27, 11));
            $count = $mashableUtil::GetUnreadCountMashableInboxForCurrentUser();
            $this->assertEquals(38, $count);
        }

    }
?>