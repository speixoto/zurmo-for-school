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


    /**
     * Layout for the business card view for a person.
     */
    class PersonCardViewLayout
    {
        protected $person;

        public function __construct($person)
        {
            assert('$person instanceof User || $person instanceof Person');
            $this->person = $person;
        }

        public function renderContent()
        {

            $content  = $this->renderFrontOfCardContent();
            $content .= $this->renderBackOfCardContent();
            return $content;
        }


        protected function renderFrontOfCardContent()
        {
            $content  = $this-> resolveAvatarContent();
            $content .= $this->resolveNameContent();
            $content .= $this->resolveBackOfCardLinkContent();
            $content .= $this->resolveJobTitleContent();
            $content .= $this->resolveDepartmentAndAccountContent();
            $content .= $this->resolveGenderAndAgeContent();
            $content .= $this->resolveSocialConnectorsContent();
            $content .= $this->resolvePhoneAndEmailContent();
            $content .= $this->resolveAddressContent();
            return $content;
        }

        protected function resolveAvatarContent()
        {
            //todo: override for user layout to do the gravatar avatar
            return Yii::app()->dataEnhancer->getPersonAvatar($this->person);
        }

        protected function resolveNameContent()
        {
            $element                       = new DropDownElement($this->person, 'title', null);
            $element->nonEditableTemplate  = '{content}';
            $spanContent                   = ZurmoHtml::tag('span', array('class' => 'salutation'), $element->render());
            return ZurmoHtml::tag('h2', array(), $spanContent . strval($this->person));
        }

        protected function resolveBackOfCardLinkContent()
        {
            if(Yii::app()->dataEnhancer->personHasBackOfCard($this->person))
            {
                static::registerBackOfCardScript();
                $spanContent = ZurmoHtml::tag('span', array(), Yii::app()->dataEnhancer->getPersonBackOfCardLabel());
                $content = ZurmoHtml::link($spanContent, '#', array('class' => 'toggle-back-of-card-link mini-button clearfix'));
                return $content;
            }
        }

        protected function resolveJobTitleContent()
        {
            if($this->person->jobTitle != null)
            {
                $content  = ZurmoHtml::tag('h3', array('class' => 'position'), $this->person->jobTitle);
                return $content;
            }
        }


        //todo: only check acount if contact, not user or person , which means we need to refactor to properly deal with this decoupling
        protected function resolveDepartmentAndAccountContent()
        {
            $departmentAndAccountContent = null;
            if($this->person->department != null)
            {
                $departmentAndAccountContent = $this->person->department;
            }
            if($this->person->account->id > 0) //todo: security on account.
            {
                if($departmentAndAccountContent != null)
                {
                    $departmentAndAccountContent .=  ' / ';
                }
                $departmentAndAccountContent .= strval($this->person->account);//todo: account should be linkable?
            }
            if($departmentAndAccountContent != null)
            {
                return ZurmoHtml::tag('h4', array('class' => 'position'), $departmentAndAccountContent);
            }
        }

        protected function resolveGenderAndAgeContent()
        {
            return '                                <div class="demographic-details">
                                                    <span class="sex male">male</span>
                                                    <span>Age: 33<br />Range: 25-35</span>
                                                </div>';
        }

        protected function resolveSocialConnectorsContent()
        {
            //todo: facebook, linkedin twitter need to go somewhere. move to enrichment
            //todo: basic link utility for these 3 probably should be in open source...
            return '<div class="social-details">
                                                    <a href="" class="social-icon icon-facebook" title="Facebook">Facebook</a>
                                                    <a href="" class="social-icon icon-twitter" title="Twitter">Twitter</a>
                                                    <a href="" class="social-icon icon-linkedin" title="Linkedin">Linkedin</a> (qtip with user name on hover)
                                                </div>';
        }

        protected function resolvePhoneAndEmailContent()
        {
            $content = null;
            if($this->person->officePhone != null)
            {
                //todo: resolve linkable using tel:
                $content .= ZurmoHtml::link($this->person->officePhone, '#',
                                array('class' => 'icon-office-phone', 'title' => '')); //todo: Click to call Office Phone (resolve for getAttributeLabel
            }
            if($this->person->officePhone != null)
            {
                //todo: resolve linkable using tel:
                $content .= ZurmoHtml::link($this->person->mobilePhone, '#',
                                array('class' => 'icon-mobile-phone', 'title' => '')); //todo: title Click to call Mobile Phone (resolve for getAttributeLabel
            }
            if($this->person->officePhone != null)
            {
                //todo: resolve linkable, based on how we do it in DV
                $content .= ZurmoHtml::link($this->person->primaryEmail->emailAddress, '#',
                                array('class' => 'icon-email', 'title' => '')); //todo: title Click to Email (resolve for getAttributeLabel
            }
            if($content != null)
            {
                return ZurmoHtml::tag('div', array('class' => 'contact-details'), $content);
            }

        }

        protected function resolveAddressContent()
        {
            $element                       = new AddressElement($this->person, 'primaryAddress', null);
            $element->breakLines           = false;
            $element->nonEditableTemplate  = '{content}';
            $spanContent                   = ZurmoHtml::tag('span', array('class' => 'salutation'), $element->render());
            return ZurmoHtml::tag('div', array('class' => 'address'), $spanContent);
        }

        protected function renderBackOfCardContent()
        {
            //todo: remember don't specify rapleaf here, but rely on dataEnricher, just call it sub area or something like that...

            return '<div class="back-of-card clearfix">
                                                    <h3>Demographic Data</h3>
                                                    <ul class="complex-data clearfix">
                                                        <li>Household Income:<span>100K</span></li>
                                                        <li>Marital Status:<span>Married</span></li>
                                                        <li>Home Market Value:<span>50K-75K</span></li>
                                                        <li>Occupation:<span>Blue Collar Worker</span></li>
                                                        <li>Education:<span>Completed High Scgool</span></li>
                                                    </ul>
                                                    <div class="half">
                                                        <h3>Interests</h3>
                                                        <ul class="simple-data clearfix">
                                                            <li><span>Blogging</span></li>
                                                            <li><span>Books</span></li>
                                                            <li><span>Business</span></li>
                                                            <li><span>CRM</span></li>
                                                            <li><span>PHP</span></li>
                                                            <li><span>Yii & TDD</span></li>
                                                        </ul>
                                                    </div>
                                                    <div class="half">
                                                        <h3>Purchases</h3>
                                                        <ul class="simple-data clearfix">
                                                            <li><span>Cars</span></li>
                                                            <li><span>Beuty</span></li>
                                                            <li><span>Cooking</span></li>
                                                            <li><span>Pets</span></li>
                                                            <li><span>Sports</span></li>
                                                            <li><span>Technology</span></li>
                                                            <li><span>Outdoor and Adventure</span></li>
                                                            <li><span>Luxury and Jewels</span></li>
                                                        </ul>
                                                    </div>
                                                </div>';
        }

        protected static function registerBackOfCardScript()
        {
            $script = "
            $('.toggle-back-of-card-link').click(function(){
                $('span', this).slideToggle();
                $('.back-of-card').slideToggle();
                return false;
            });";
            Yii::app()->getClientScript()->registerScript('backOfCardScript', $script);
        }
    }
?>