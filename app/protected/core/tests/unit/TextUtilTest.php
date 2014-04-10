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

    class TextUtilTest extends BaseTest
    {
        public function testStrToLowerWithDefaultEncoding()
        {
            $string = "Mary Had A Little Lamb and She LOVED It So.";
            $lowercaseString = TextUtil::strToLowerWithDefaultEncoding($string);
            $this->assertEquals('mary had a little lamb and she loved it so.', $lowercaseString);

            // Confirm that string will stay same, if we call this function on lowercase string.
            $lowercaseString = TextUtil::strToLowerWithDefaultEncoding($lowercaseString);
            $this->assertEquals('mary had a little lamb and she loved it so.', $lowercaseString);

            $string = "Τάχιστη αλώπηξ βαφής ψημένη γη, δρασκελίζει υπέρ νωθρού κυνός";
            $lowercaseString = TextUtil::strToLowerWithDefaultEncoding($string);
            $this->assertEquals('τάχιστη αλώπηξ βαφής ψημένη γη, δρασκελίζει υπέρ νωθρού κυνός', $lowercaseString);

            $lowercaseString = TextUtil::strToLowerWithDefaultEncoding($lowercaseString);
            $this->assertEquals('τάχιστη αλώπηξ βαφής ψημένη γη, δρασκελίζει υπέρ νωθρού κυνός', $lowercaseString);

            $string = "ĄĆĘŁŃÓŚŹŻABCDEFGHIJKLMNOPRSTUWYZQXVЁЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮÂÀÁÄÃÊÈÉËÎÍÌÏÔÕÒÓÖÛÙÚÜÇ";
            $correctLowercase = "ąćęłńóśźżabcdefghijklmnoprstuwyzqxvёйцукенгшщзхъфывапролджэячсмитьбюâàáäãêèéëîíìïôõòóöûùúüç";
            $lowercaseString = TextUtil::strToLowerWithDefaultEncoding($string);
            $this->assertEquals($correctLowercase, $lowercaseString);

            $lowercaseString = TextUtil::strToLowerWithDefaultEncoding($lowercaseString);
            $this->assertEquals($correctLowercase, $lowercaseString);
        }

        public function testTextWithUrlToTextWithLink()
        {
            $textWithUrl = "Do you know the guys who made http://www.zurmo.com. They are awsome.";
            $textWithLink = TextUtil::textWithUrlToTextWithLink($textWithUrl);
            $this->assertEquals('Do you know the guys who made <a href="http://www.zurmo.com">http://www.zurmo.com</a>. They are awsome.',
                                $textWithLink);

            $textWithUrl = "Do you know the guys who made https://www.zurmo.com. They are awsome.";
            $textWithLink = TextUtil::textWithUrlToTextWithLink($textWithUrl);
            $this->assertEquals('Do you know the guys who made <a href="https://www.zurmo.com">https://www.zurmo.com</a>. They are awsome.',
                $textWithLink);

            $textWithUrl = "Do you know the guys who made www.zurmo.com. They are awsome.";
            $textWithLink = TextUtil::textWithUrlToTextWithLink($textWithUrl);
            $this->assertEquals('Do you know the guys who made <a href="http://www.zurmo.com">www.zurmo.com</a>. They are awsome.',
                $textWithLink);

            $textWithUrl = "Please, send an email to jonny@zurmo.com";
            $textWithLink = TextUtil::textWithUrlToTextWithLink($textWithUrl);
            $this->assertEquals('Please, send an email to <a href="mailto:jonny@zurmo.com">jonny@zurmo.com</a>',
                $textWithLink);

            $textWithUrl  = "Please, send an email to jonny@zurmo.com
with a newline";
            $textWithLink = TextUtil::textWithUrlToTextWithLink($textWithUrl);
            $expectedResult  = 'Please, send an email to <a href="mailto:jonny@zurmo.com">jonny@zurmo.com</a><br />
with a newline';
            $this->assertEquals($expectedResult, $textWithLink);
        }
    }
?>