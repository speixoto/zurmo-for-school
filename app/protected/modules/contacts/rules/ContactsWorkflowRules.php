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
     * Report rules to be used with the Contacts module.
     */
    class ContactsWorkflowRules extends WorkflowRules
    {
        //todo: since ContactsReportRules has the same exact methods we should think about this
        public static function getVariableStateModuleLabel(User $user)
        {
            assert('$user->id > 0');
            $adapterName  = ContactsUtil::resolveContactStateAdapterByModulesUserHasAccessTo('LeadsModule',
                'ContactsModule', $user);
            if($adapterName === false)
            {
                return null;
            }
            elseif($adapterName == 'LeadsStateMetadataAdapter')
            {
                return Zurmo::t('ContactsModule', 'LeadsModulePluralLabel', LabelUtil::getTranslationParamsForAllModules());
            }
            elseif($adapterName == 'ContactsStateMetadataAdapter')
            {
                return Zurmo::t('ContactsModule', 'ContactsModulePluralLabel', LabelUtil::getTranslationParamsForAllModules());
            }
            elseif($adapterName === null)
            {
                return Zurmo::t('ContactsModule', 'ContactsModulePluralLabel and LeadsModulePluralLabel',
                    LabelUtil::getTranslationParamsForAllModules());
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public static function canUserAccessModuleInAVariableState(User $user)
        {
            assert('$user->id > 0');
            if(RightsUtil::canUserAccessModule('ContactsModule', $user) ||
               RightsUtil::canUserAccessModule('LeadsModule', $user))
            {
                return true;
            }
            return false;
        }

        public static function resolveStateAdapterUserHasAccessTo(User $user)
        {
            assert('$user->id > 0');
            return ContactsUtil::resolveContactStateAdapterByModulesUserHasAccessTo('LeadsModule',
                                                                                    'ContactsModule', $user);
        }
    }
?>