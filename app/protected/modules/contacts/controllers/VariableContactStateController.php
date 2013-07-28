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
     * This controller handles actions that rely on more than just the contact module for security checks. Most likely
     * it includes the lead module as well.  Controller security checks are done within the actions and not done using
     * a filter. You can add filters if you would like.
     *
     */
    class ContactsVariableContactStateController extends Controller
    {
        /**
         * Given a partial name search for all contacts regardless of contact state unless the current user has security
         * restrictions on some states.  If the adapter resolver returns false, then the
         * user does not have access to the Leads or Contacts module.
         * JSON encode the resulting array of contacts.
         */
        public function actionAutoCompleteAllContacts($term)
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'autoCompleteListPageSize', get_class($this->getModule()));
            $adapterName  = ContactsUtil::resolveContactStateAdapterByModulesUserHasAccessTo('LeadsModule',
                                                                                        'ContactsModule',
                                                                                         Yii::app()->user->userModel);
            if ($adapterName === false)
            {
                $messageView = new AccessFailureView();
                $view        = new AccessFailurePageView($messageView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
            $autoCompleteResults = ContactAutoCompleteUtil::getByPartialName($term, $pageSize, $adapterName);
            echo CJSON::encode($autoCompleteResults);
        }

        /**
         * Given a partial name or e-mail address, search for all contacts regardless of contact state unless the
         * current user has security restrictions on some states.  If the adapter resolver returns false, then the
         * user does not have access to the Leads or Contacts module.
         * JSON encode the resulting array of contacts.
         */
        public function actionAutoCompleteAllContactsForMultiSelectAutoComplete($term)
        {
            $pageSize = Yii::app()->pagination->resolveActiveForCurrentUserByType(
                            'autoCompleteListPageSize', get_class($this->getModule()));
            $adapterName  = ContactsUtil::resolveContactStateAdapterByModulesUserHasAccessTo('LeadsModule',
                                                                                        'ContactsModule',
                                                                                         Yii::app()->user->userModel);
            if ($adapterName === false)
            {
                $messageView = new AccessFailureView();
                $view        = new AccessFailurePageView($messageView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
            $contacts = ContactSearch::getContactsByPartialFullNameOrAnyEmailAddress($term, $pageSize, $adapterName);
            $autoCompleteResults  = array();
            foreach ($contacts as $contact)
            {
                $autoCompleteResults[] = array(
                    'id'   => $contact->id,
                    'name' => MultipleContactsForMeetingElement::renderHtmlContentLabelFromContactAndKeyword($contact, $term)
                );
            }
            echo CJSON::encode($autoCompleteResults);
        }

        public function actionModalListAllContacts()
        {
            $modalListLinkProvider = new SelectFromRelatedEditModalListLinkProvider(
                                            $_GET['modalTransferInformation']['sourceIdFieldId'],
                                            $_GET['modalTransferInformation']['sourceNameFieldId'],
                                            $_GET['modalTransferInformation']['modalId']
            );
            $adapterName  = ContactsUtil::resolveContactStateAdapterByModulesUserHasAccessTo('LeadsModule',
                                                                                        'ContactsModule',
                                                                                         Yii::app()->user->userModel);
            if ($adapterName === false)
            {
                $messageView = new AccessFailureView();
                $view        = new AccessFailurePageView($messageView);
                echo $view->render();
                Yii::app()->end(0, false);
            }
            echo ModalSearchListControllerUtil::setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider,
                                                $adapterName);
        }
    }
?>
