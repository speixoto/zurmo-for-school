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
     * Accounts Module Super User Walkthrough.
     * Walkthrough for the super user of all possible controller actions.
     * Since this is a super user, he should have access to all controller actions
     * without any exceptions being thrown.
     */
    class EmailTemplatesSuperUserWalkthroughTest extends ZurmoWalkthroughBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;

            //Setup test data owned by the super user.
            EmailTemplateTestHelper::createEmailTemplateByName(1, 'Test Subject', 'Test Name', 'Test HtmlContent', 'Test TextContent');
            EmailTemplateTestHelper::createEmailTemplateByName(1, 'Test Subject1', 'Test Name1', 'Test HtmlContent1', 'Test TextContent1');
        }

        public function testSuperUserAllDefaultControllerActions()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');

            //Test all default controller actions that do not require any POST/GET variables to be passed.
            //This does not include portlet controller actions.
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default');
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/index');
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/create');

            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/list');
            $this->assertFalse(strpos($content, 'anyMixedAttributes') === false);
            //Test the search or paging of the listview.
            Yii::app()->clientScript->reset(); //to make sure old js doesn't make it to the UI
            $this->setGetArray(array('ajax' => 'list-view'));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/list');
            $this->assertTrue(strpos($content, 'anyMixedAttributes') === false);
            $this->resetGetArray();

            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(2, count($emailTemplates));
            
            $emailTemplateNameId = self::getModelIdByModelNameAndName ('EmailTemplate', 'Test Name');
            $this->setGetArray(array('id' => $emailTemplateNameId));
            $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/edit');

            //Save emailTemplate.
            $emailTemplateName = EmailTemplate::getById($emailTemplateNameId);
            $this->assertEquals('Test Subject', $emailTemplateName->subject);
            $this->setPostArray(array('EmailTemplate' => array('subject' => 'Test Subject')));
            //Test having a failed validation on the emailTemplate during save.
            $this->setGetArray (array('id'      => $emailTemplateNameId));
            $this->setPostArray(array('EmailTemplate' => array('name' => '')));
            $content = $this->runControllerWithNoExceptionsAndGetContent('emailTemplates/default/edit');
            $this->assertFalse(strpos($content, 'Name cannot be blank') === false);
        }

         
        public function testSuperUserDeleteAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            $emailTemplateId = self::getModelIdByModelNameAndName ('EmailTemplate', 'Test Name');

            //Delete an emailTemplate.
            $this->setGetArray(array('id' => $emailTemplateId));
            $this->resetPostArray();
            $this->runControllerWithRedirectExceptionAndGetContent('emailTemplates/default/delete');
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(1, count($emailTemplates));
            try
            {
                EmailTemplate::getById($emailTemplates);
                $this->fail();
            }
            catch (NotFoundException $e)
            {
                //success
            }
        }

        public function testSuperUserCreateAction()
        {
            $super = $this->logoutCurrentUserLoginNewUserAndGetByUsername('super');
            //Create a new emailTemplate.
            $this->resetGetArray();
            $this->setPostArray(array('EmailTemplate' => array(
                                            'type'        => 1,
                                            'name'        => 'New Test EmailTemplate'
                                            'subject'     => 'New Test Subject',
                                            'textContent' => 'New Text Content')));
            $redirectUrl = $this->runControllerWithRedirectExceptionAndGetUrl('emailTemplates/default/create');
            $emailTemplate = EmailTemplate::getByName('New Test EmailTemplate');
            $this->assertEquals(1, count($emailTemplate));
            $this->assertTrue  ($emailTemplate[0]->id > 0);
            $compareRedirectUrl = Yii::app()->createUrl('emailTemplates/default/details', array('id' => $emailTemplate[0]->id));
            $this->assertEquals($compareRedirectUrl, $redirectUrl);
            $this->assertEquals('New Test Subject', $emailTemplate[0]->subject);
            $this->assertTrue  ($emailTemplate[0]->owner == $super);
            $emailTemplates = EmailTemplate::getAll();
            $this->assertEquals(2, count($emailTemplate));
        }

    }
?>
