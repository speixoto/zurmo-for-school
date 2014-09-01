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

    class ContactsModule extends SecurableModule
    {
        const RIGHT_CREATE_CONTACTS = 'Create Contacts';
        const RIGHT_DELETE_CONTACTS = 'Delete Contacts';
        const RIGHT_ACCESS_CONTACTS = 'Access Contacts Tab';

        public function getDependencies()
        {
            return array(
                'configuration',
                'zurmo',
            );
        }

        public function getRootModelNames()
        {
            return array('Contact');
        }

        public static function getTranslatedRightsLabels()
        {
            $params                              = LabelUtil::getTranslationParamsForAllModules();
            $labels                              = array();
            $labels[self::RIGHT_CREATE_CONTACTS] = Zurmo::t('ContactsModule', 'Create ContactsModulePluralLabel',     $params);
            $labels[self::RIGHT_DELETE_CONTACTS] = Zurmo::t('ContactsModule', 'Delete ContactsModulePluralLabel',     $params);
            $labels[self::RIGHT_ACCESS_CONTACTS] = Zurmo::t('ContactsModule', 'Access ContactsModulePluralLabel Tab', $params);
            return $labels;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array();
            $metadata['global'] = array(
                'designerMenuItems' => array(
                    'showFieldsLink' => true,
                    'showGeneralLink' => true,
                    'showLayoutsLink' => true,
                    'showMenusLink' => true,
                ),
                'globalSearchAttributeNames' => array(
                    'fullName',
                    'anyEmail',
                    'officePhone',
                    'mobilePhone',
                ),
                'startingState' => 1,
                'tabMenuItems' => array(
                    array(
                        'label' => "eval:Zurmo::t('ContactsModule', 'ContactsModulePluralLabel', \$translationParams)",
                        'url'   => array('/contacts/default'),
                        'right' => self::RIGHT_ACCESS_CONTACTS,
                        'mobile' => true,
                    ),
                ),
                'shortcutsCreateMenuItems' => array(
                    array(
                        'label'  => "eval:Zurmo::t('ContactsModule', 'ContactsModuleSingularLabel', \$translationParams)",
                        'url'    => array('/contacts/default/create'),
                        'right'  => self::RIGHT_CREATE_CONTACTS,
                        'mobile' => true,
                    ),
                ),
                'updateLatestActivityDateTimeWhenATaskIsCompleted'        => true,
                'updateLatestActivityDateTimeWhenANoteIsCreated'          => true,
                'updateLatestActivityDateTimeWhenAnEmailIsSentOrArchived' => true,
                'updateLatestActivityDateTimeWhenAMeetingIsInThePast'     => true,
            );
            return $metadata;
        }

        public static function getPrimaryModelName()
        {
            return 'Contact';
        }

        /**
         * Used on first load to install ContactState data
         * and the startingState for the Contacts module.
         * @return true/false if data was in fact loaded.
         */
        public static function loadStartingData()
        {
            if (count(ContactState::GetAll()) != 0)
            {
                return false;
            }
            $data = array(
                Zurmo::t('Core', 'New'),
                Zurmo::t('Core', 'In Progress'),
                Zurmo::t('ContactsModule', 'Recycled'),
                Zurmo::t('ContactsModule', 'Dead'),
                Zurmo::t('ContactsModule', 'Qualified'),
                Zurmo::t('ZurmoModule', 'Customer')
            );
            $order = 0;
            $startingStateId = null;
            foreach ($data as $stateName)
            {
                $state        = new ContactState();
                $state->name  = $stateName;
                $state->order = $order;
                $saved        = $state->save();
                assert('$saved');
                if ($stateName == Zurmo::t('ContactsModule', 'Qualified'))
                {
                    $startingStateId = $state->id;
                }
                $order++;
            }
            if ($startingStateId == null)
            {
                throw new NotSupportedException();
            }
            $metadata = ContactsModule::getMetadata();
            $metadata['global']['startingStateId'] = $startingStateId;
            ContactsModule::setMetadata($metadata);
            assert('count(ContactState::GetAll()) == 6');
            return true;
        }

        public static function getAccessRight()
        {
            return self::RIGHT_ACCESS_CONTACTS;
        }

        public static function getCreateRight()
        {
            return self::RIGHT_CREATE_CONTACTS;
        }

        public static function getDeleteRight()
        {
            return self::RIGHT_DELETE_CONTACTS;
        }

        /**
         * Override since the ContactsModule controls module permissions for both leads and contacts.
         */
        public static function getSecurableModuleDisplayName()
        {
            $label = static::getModuleLabelByTypeAndLanguage('Plural') . '&#160;&#38;&#160;' .
                     LeadsModule::getModuleLabelByTypeAndLanguage('Plural');
            return $label;
        }

        public static function getDefaultDataMakerClassName()
        {
            return 'ContactsDefaultDataMaker';
        }

        public static function getDemoDataMakerClassNames()
        {
            return array('ContactsDemoDataMaker');
        }

        public static function getStateMetadataAdapterClassName()
        {
            return 'ContactsStateMetadataAdapter';
        }

        public static function getGlobalSearchFormClassName()
        {
            return 'ContactsSearchForm';
        }

        public static function hasPermissions()
        {
            return true;
        }

        public static function isReportable()
        {
            return true;
        }

        public static function canHaveWorkflow()
        {
            return true;
        }

        public static function canHaveContentTemplates()
        {
            return true;
        }

        protected static function getSingularModuleLabel($language)
        {
            return Zurmo::t('ContactsModule', 'Contact', array(), null, $language);
        }

        protected static function getPluralModuleLabel($language)
        {
            return Zurmo::t('ContactsModule', 'Contacts', array(), null, $language);
        }

        public static function shouldUpdateLatestActivityDateTimeWhenATaskIsCompleted()
        {
            $metadata = static::getMetadata();
            if (isset($metadata['global']['updateLatestActivityDateTimeWhenATaskIsCompleted']))
            {
                return (bool) $metadata['global']['updateLatestActivityDateTimeWhenATaskIsCompleted'];
            }
            return false;
        }

        public static function shouldUpdateLatestActivityDateTimeWhenANoteIsCreated()
        {
            $metadata = static::getMetadata();
            if (isset($metadata['global']['updateLatestActivityDateTimeWhenANoteIsCreated']))
            {
                return (bool) $metadata['global']['updateLatestActivityDateTimeWhenANoteIsCreated'];
            }
            return false;
        }

        public static function shouldUpdateLatestActivityDateTimeWhenAnEmailIsSentOrArchived()
        {
            $metadata = static::getMetadata();
            if (isset($metadata['global']['updateLatestActivityDateTimeWhenAnEmailIsSentOrArchived']))
            {
                return (bool) $metadata['global']['updateLatestActivityDateTimeWhenAnEmailIsSentOrArchived'];
            }
            return false;
        }

        public static function shouldUpdateLatestActivityDateTimeWhenAMeetingIsInThePast()
        {
            $metadata = static::getMetadata();
            if (isset($metadata['global']['updateLatestActivityDateTimeWhenAMeetingIsInThePast']))
            {
                return (bool) $metadata['global']['updateLatestActivityDateTimeWhenAMeetingIsInThePast'];
            }
            return false;
        }
    }
?>
