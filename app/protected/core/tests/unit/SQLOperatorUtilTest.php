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

    class SQLOperatorUtilTest extends BaseTest
    {
        public function testIsValidOperatorTypeByValue()
        {
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('startsWith', 'abc'));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('doesNotStartsWith', 'abc'));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('doesNotEndsWith', 'abc'));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('doesNotContains', 'abc'));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('greaterThan', 'abc'));
            $this->assertFalse(SQLOperatorUtil::isValidOperatorTypeByValue ('startsWith', 5));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('greaterThan', 5));
            $this->assertFalse(SQLOperatorUtil::isValidOperatorTypeByValue ('doesNotMatter', null));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('oneOf', array(4, 5, 6)));
            $this->assertFalse(SQLOperatorUtil::isValidOperatorTypeByValue ('oneOf', null));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('greaterThanOrEqualTo', 'abc'));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('lessThanOrEqualTo', 'abc'));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('isNull', null));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('isNotNull', null));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('isEmpty', null));
            $this->assertTrue(SQLOperatorUtil::isValidOperatorTypeByValue  ('isNotEmpty', null));
        }

        public function testGetOperatorByType()
        {
            $this->assertEquals('>', SQLOperatorUtil::getOperatorByType('greaterThan'));
            $this->assertEquals('not like', SQLOperatorUtil::getOperatorByType('doesNotStartsWith'));
            $this->assertEquals('not like', SQLOperatorUtil::getOperatorByType('doesNotEndsWith'));
            $this->assertEquals('not like', SQLOperatorUtil::getOperatorByType('doesNotContains'));
        }

        public function testResolveOperatorAndValueForOneOf()
        {
            $queryPart = SQLOperatorUtil::resolveOperatorAndValueForOneOf('oneOf', array(5, 6, 7));
            $compareQueryPart = "IN(5,6,7)"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
            $queryPart = SQLOperatorUtil::resolveOperatorAndValueForOneOf('oneOf', array('a', 'b', 'c'));
            $compareQueryPart = "IN('a','b','c')"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
        }

        public function testResolveOperatorAndValueForNullOrEmpty()
        {
            $queryPart = SQLOperatorUtil::resolveOperatorAndValueForNullOrEmpty('isNull');
            $compareQueryPart = "IS NULL"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
            $queryPart = SQLOperatorUtil::resolveOperatorAndValueForNullOrEmpty('isNotNull');
            $compareQueryPart = "IS NOT NULL"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
            $queryPart = SQLOperatorUtil::resolveOperatorAndValueForNullOrEmpty('isEmpty');
            $compareQueryPart = "= ''"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
            $queryPart = SQLOperatorUtil::resolveOperatorAndValueForNullOrEmpty('isNotEmpty');
            $compareQueryPart = "!= ''"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testResolveOperatorAndValueForOneOfUnsupportedValue()
        {
            SQLOperatorUtil::resolveOperatorAndValueForOneOf('oneOf', array(array()));
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testGetOperatorByTypeForNullIsUnsupported()
        {
            SQLOperatorUtil::GetOperatorByType('isNull');
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testGetOperatorByTypeForNotNullIsUnsupported()
        {
            SQLOperatorUtil::GetOperatorByType('isNotNull');
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testGetOperatorByTypeForEmptyIsUnsupported()
        {
            SQLOperatorUtil::GetOperatorByType('isEmpty');
        }

        /**
         * @expectedException NotSupportedException
         */
        public function testGetOperatorByTypeForNotEmptyIsUnsupported()
        {
            SQLOperatorUtil::GetOperatorByType('isNotEmpty');
        }

        public function testResolveOperatorAndValueForOneOfWithEscapedContent()
        {
            $queryPart = SQLOperatorUtil::resolveOperatorAndValueForOneOf('oneOf', array('a', "b'd", 'c'));
            $compareQueryPart = "IN('a','b\'d','c')"; // Not Coding Standard
            $this->assertEquals($compareQueryPart, $queryPart);
        }

        public function testDoesOperatorTypeAllowNullValues()
        {
            $this->assertTrue(SQLOperatorUtil::doesOperatorTypeAllowNullValues('isNull'));
            $this->assertTrue(SQLOperatorUtil::doesOperatorTypeAllowNullValues('isEmpty'));
            $this->assertTrue(SQLOperatorUtil::doesOperatorTypeAllowNullValues('isNotNull'));
            $this->assertTrue(SQLOperatorUtil::doesOperatorTypeAllowNullValues('isNotEmpty'));
            $this->assertFalse(SQLOperatorUtil::doesOperatorTypeAllowNullValues('startsWith'));
        }

        public function testResolveValueLeftSideLikePartByOperatorType()
        {
            $this->assertEquals(null, SQLOperatorUtil::resolveValueLeftSideLikePartByOperatorType('doesNotStartsWith'));
            $this->assertEquals('%',  SQLOperatorUtil::resolveValueLeftSideLikePartByOperatorType('doesNotEndsWith'));
            $this->assertEquals('%',  SQLOperatorUtil::resolveValueLeftSideLikePartByOperatorType('doesNotContains'));
            $this->assertEquals('%',  SQLOperatorUtil::resolveValueLeftSideLikePartByOperatorType('contains'));
        }

        public function testResolveValueRightSideLikePartByOperatorType()
        {
            $this->assertEquals('%',  SQLOperatorUtil::resolveValueRightSideLikePartByOperatorType('doesNotStartsWith'));
            $this->assertEquals(null, SQLOperatorUtil::resolveValueRightSideLikePartByOperatorType('doesNotEndsWith'));
            $this->assertEquals('%',  SQLOperatorUtil::resolveValueRightSideLikePartByOperatorType('doesNotContains'));
            $this->assertEquals('%',  SQLOperatorUtil::resolveValueRightSideLikePartByOperatorType('contains'));
        }

        public function testResolveValidationForATemplateSqlStatementAndReturnErrorMessage()
        {
            $this->assertEquals('The structure is invalid. Please fix conditions.', SQLOperatorUtil::
                                    resolveValidationForATemplateSqlStatementAndReturnErrorMessage('', 1));
            $this->assertEquals('The structure is invalid. Please fix conditions.', SQLOperatorUtil::
                                    resolveValidationForATemplateSqlStatementAndReturnErrorMessage('dumb structure', 1));
            $this->assertEquals('The structure is invalid. Please use only integers less than 2.', SQLOperatorUtil::
                                    resolveValidationForATemplateSqlStatementAndReturnErrorMessage('4', 1));
            $this->assertEquals('The structure is invalid. Please fix your parenthesis.', SQLOperatorUtil::
                                    resolveValidationForATemplateSqlStatementAndReturnErrorMessage('()))', 1));
            $this->assertEquals('The structure is invalid. Please fix your parenthesis around the not operator.', SQLOperatorUtil::
                                    resolveValidationForATemplateSqlStatementAndReturnErrorMessage('(not) 2 and 1', 2));
            $this->assertEquals('The structure is invalid. Please fix conditions.', SQLOperatorUtil::
                                    resolveValidationForATemplateSqlStatementAndReturnErrorMessage('or 2 and 1', 2));
            $this->assertEquals('The structure is invalid. Please, only use one of the operators: not, and, or.', SQLOperatorUtil::
                                    resolveValidationForATemplateSqlStatementAndReturnErrorMessage('1 e 2 ou 1', 2));
            $this->assertEquals('', SQLOperatorUtil::
                                    resolveValidationForATemplateSqlStatementAndReturnErrorMessage('not (not (2 and 4) or 1 and not 4)', 4));
        }
    }
?>