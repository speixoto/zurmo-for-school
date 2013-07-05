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

    class RedBeanModelToTableSchemaAdapterTest extends BaseTest
    {
        protected static $messageLogger;

        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            SecurityTestHelper::createSuperAdmin();
            $super = User::getByUsername('super');
            Yii::app()->user->userModel = $super;
            static::$messageLogger = new MessageLogger();
        }

        public function testResolveWithEmptyModelClassName()
        {
            $modelClassName     = null;
            $schema             = RedBeanModelToTableSchemaAdapter::resolve($modelClassName, static::$messageLogger);
            $this->assertFalse($schema);
        }

        /**
         * @depends testResolveWithEmptyModelClassName
         */
        public function testResolveWithInexistentModelClassName()
        {
            $modelClassName     = 'ModelClass';
            $schema             = RedBeanModelToTableSchemaAdapter::resolve($modelClassName, static::$messageLogger);
            $this->assertFalse($schema);
        }

        /**
         * @depends testResolveWithInexistentModelClassName
         */
        public function testResolveForModelClassWithNoOwnMetadataAndCannotHaveBean()
        {
            $modelClassName     = 'MashableActivity';
            $schema             = RedBeanModelToTableSchemaAdapter::resolve($modelClassName, static::$messageLogger);
            $this->assertFalse($schema);
        }

        /**
         * @depends testResolveForModelClassWithNoOwnMetadataAndCannotHaveBean
         */
        public function testResolveForModelClassWithNoOwnMetadataAndCanHaveBean()
        {
            $modelClassName     = 'OwnedSecurableItem';
            $expectedSchema     = array(
                'ownedsecurableitem' => array(
                    'columns' => array(
                        array(
                            'name' => 'securableitem_id',
                            'type' => 'INT(11)',
                            'unsigned' => 'UNSIGNED',
                            'notNull' => 'NULL',
                            'collation' => null,
                            'default' => 'DEFAULT NULL',
                        ),
                        array(
                            'name' => 'owner__user_id',
                            'type' => 'INT(11)',
                            'unsigned' => 'UNSIGNED',
                            'notNull' => 'NULL',
                            'collation' => null,
                            'default' => 'DEFAULT NULL',
                        ),
                    ),
                    'indexes' => array(),
                ),
            );
            $schema             = RedBeanModelToTableSchemaAdapter::resolve($modelClassName, static::$messageLogger);
            $this->assertNotEmpty($schema);
            $this->assertEquals($expectedSchema, $schema);
        }

        /**
         * @depends testResolveForModelClassWithNoOwnMetadataAndCanHaveBean
         */
        public function testResolve()
        {
            $expectedSchema     = array('_user' => array(
                'columns' => array(
                    array(
                        'name' => 'hash',
                        'type' => 'VARCHAR(32)',
                        'unsigned' => null,
                        'notNull' => 'NULL',
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL',
                    ),
                    array(
                        'name' => 'language',
                        'type' => 'VARCHAR(10)',
                        'unsigned' => null,
                        'notNull' => 'NULL',
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL',
                    ),
                    array(
                        'name' => 'locale',
                        'type' => 'VARCHAR(10)',
                        'unsigned' => null,
                        'notNull' => 'NULL',
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL',
                    ),
                    array(
                        'name' => 'timezone',
                        'type' => 'VARCHAR(64)',
                        'unsigned' => null,
                        'notNull' => 'NULL',
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL',
                    ),
                    array(
                        'name' => 'username',
                        'type' => 'VARCHAR(64)',
                        'unsigned' => null,
                        'notNull' => 'NULL',
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL',
                    ),
                    array(
                        'name' => 'serializedavatardata',
                        'type' => 'TEXT',
                        'unsigned' => null,
                        'notNull' => 'NULL',
                        'collation' => 'COLLATE utf8_unicode_ci',
                        'default' => 'DEFAULT NULL',
                    ),
                    array(
                        'name' => 'isactive',
                        'type' => 'TINYINT(1) UNSIGNED',
                        'unsigned' => null,
                        'notNull' => 'NULL',
                        'collation' => null,
                        'default' => 'DEFAULT NULL',
                    ),
                    array(
                        'name' => 'lastlogindatetime',
                        'type' => 'DATETIME',
                        'unsigned' => null,
                        'notNull' => 'NULL',
                        'collation' => null,
                        'default' => 'DEFAULT NULL',
                    ),
                    array(
                        'name' => 'permitable_id',
                        'type' => 'INT(11)',
                        'unsigned' => 'UNSIGNED',
                        'notNull' => 'NULL',
                        'collation' => null,
                        'default' => 'DEFAULT NULL',
                    ),
                    array(
                        'name' => 'person_id',
                        'type' => 'INT(11)',
                        'unsigned' => 'UNSIGNED',
                        'notNull' => 'NULL',
                        'collation' => null,
                        'default' => 'DEFAULT NULL',
                    ),
                    array(
                        'name' => 'currency_id',
                        'type' => 'INT(11)',
                        'unsigned' => 'UNSIGNED',
                        'notNull' => 'NULL',
                        'collation' => null,
                        'default' => 'DEFAULT NULL',
                    ),
                    array(
                        'name' => 'manager__user_id',
                        'type' => 'INT(11)',
                        'unsigned' => 'UNSIGNED',
                        'notNull' => 'NULL',
                        'collation' => null,
                        'default' => 'DEFAULT NULL',
                    ),
                    array(
                        'name' => 'role_id',
                        'type' => 'INT(11)',
                        'unsigned' => 'UNSIGNED',
                        'notNull' => 'NULL',
                        'collation' => null,
                        'default' => 'DEFAULT NULL',
                    ),
                ),
                'indexes' => array(
                            'unique_username_Index' => array(
                                'columns' => array('username'),
                                'unique' => true
                            )
                        )
                    )
                );
            $modelClassName     = 'User';
            $schema             = RedBeanModelToTableSchemaAdapter::resolve($modelClassName, static::$messageLogger);
            $this->assertNotEmpty($schema);
            $this->assertEquals($expectedSchema, $schema);
        }
    }
?>