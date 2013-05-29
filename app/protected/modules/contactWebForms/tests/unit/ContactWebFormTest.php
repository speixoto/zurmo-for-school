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

    class ContactWebFormTest extends ZurmoBaseTest
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
        }

        public function setUp()
        {
            parent::setUp();
            Yii::app()->user->userModel = User::getByUsername('super');
        }

        public function testCreateAndGetContactWebFormById()
        {
            $contact     = new Contact();
            $adapter     = new ModelAttributesAdapter($contact);
            $attributes  = $adapter->getAttributes();
            $attributes  = ArrayUtil::subValueSort($attributes, 'attributeLabel', 'asort');
            $attributes  = array_keys($attributes);

            $this->assertTrue(ContactsModule::loadStartingData());
            $contactStates                      = ContactState::getByName('New');
            $contactWebForm                     = new ContactWebForm();
            $contactWebForm->name               = 'Test Form';
            $contactWebForm->redirectUrl        = 'http://google.com';
            $contactWebForm->submitButtonLabel  = 'Save';
            $contactWebForm->defaultState       = $contactStates[0];
            $contactWebForm->serializedData     = serialize($attributes);
            $contactWebForm->defaultOwner       = Yii::app()->user->userModel;
            $this->assertTrue($contactWebForm->save());
            $id                                 = $contactWebForm->id;
            unset($contactWebForm);
            $contactWebForm = ContactWebForm::getById($id);
            $this->assertEquals('Test Form', $contactWebForm->name);
            $this->assertEquals('http://google.com', $contactWebForm->redirectUrl);
            $this->assertEquals('Save', $contactWebForm->submitButtonLabel);
            $this->assertEquals('New', $contactWebForm->defaultState->name);
            $this->assertEquals($attributes, unserialize($contactWebForm->serializedData));
        }

        public function testGetMetadataFromContactWebFormById()
        {
            $id = 1;
            $contactWebForm = ContactWebForm::getById(intval($id));
            $contactWebFormAttributes = unserialize($contactWebForm->serializedData);
            $layout     = array();
            $panels	    = array();
            $rows		= array();

            foreach ($contactWebFormAttributes as $attribute)
            {
                $rows[]['cells'][] = array('detailViewOnly' => 1,
                                           'element'        => $attribute);
            }
            $panels[]['rows'] = $rows;
            $layout['panels'] = $panels;

            $viewClassName            = 'ContactEditAndDetailsView';
            $moduleClassName          = 'ContactsModule';
            $modelClassName           = $moduleClassName::getPrimaryModelName();
            $editableMetadata         = $viewClassName::getMetadata();
            $designerRulesType        = $viewClassName::getDesignerRulesType();
            $designerRulesClassName   = $designerRulesType . 'DesignerRules';
            $designerRules            = new $designerRulesClassName();
            $modelAttributesAdapter   = DesignerModelToViewUtil::getModelAttributesAdapter($viewClassName, $modelClassName);
            $derivedAttributesAdapter = new DerivedAttributesAdapter($modelClassName);
            $attributeCollection      = array_merge($modelAttributesAdapter->getAttributes(),
                                                    $derivedAttributesAdapter->getAttributes());
            $attributesLayoutAdapter  = AttributesLayoutAdapterUtil::makeAttributesLayoutAdapter(
                $attributeCollection,
                $designerRules,
                $editableMetadata
            );

            $layoutMetadataAdapter = new LayoutMetadataAdapter(
                $viewClassName,
                $moduleClassName,
                $editableMetadata,
                $designerRules,
                $attributesLayoutAdapter->getPlaceableLayoutAttributes(),
                $attributesLayoutAdapter->getRequiredDerivedLayoutAttributeTypes()
            );
            $savableMetadata = array();
            $metadata = $layoutMetadataAdapter->resolveMetadataFromLayout($layout, $savableMetadata);

            foreach ($metadata['panels'][0]['rows'] as $cell)
            {
                $this->assertTrue(in_array($cell['cells'][0]['elements'][0]['attributeName'], $contactWebFormAttributes));
                $this->assertNotEquals('', $cell['cells'][0]['elements'][0]['attributeName']);
            }
        }
    }
?>