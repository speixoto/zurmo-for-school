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

    class ContactDetailsAndRelationsView extends DetailsAndRelationsView
    {
        public function isUniqueToAPage()
        {
            return true;
        }

        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'leftTopView' => array(
                        'viewClassName' => 'ContactEditAndDetailsView',
                    ),
                    'leftBottomView' => array(
                        'showAsTabbed' => false,
                        'columns' => array(
                            array(
                                'rows' => array(
                                    array(
                                        'type' => 'NoteInlineEditForPortlet'
                                    ),
                                    array(
                                        'type' => 'ContactLatestActivtiesForPortlet'
                                    ),
                                )
                            )
                        )
                    ),
                    'rightTopView' => array(
                        'columns' => array(
                            array(
                                'rows' => array(
                                    array(
                                        'type' => 'UpcomingMeetingsForContactCalendar',
                                    ),
                                    array(
                                        'type' => 'OpenTasksForContactRelatedList',
                                    ),
                                    array(
                                        'type' => 'OpportunitiesForContactRelatedList',
                                    )
                                )
                            )
                        )
                    )
                )
            );
            return $metadata;
        }

        protected function renderContent() {
            $content = parent::renderContent();
            Yii::app()->clientScript->registerScriptFile(
                  //Yii::app()->baseUrl. '/app/protected/extensions/juitokeninput/assets/jquery.tokeninput.js'
                    'http://localhost/jquery.tokeninput.js'
            );
            $content  .= ZurmoHtml::textField('exp', null);
            $content  .= "<script>$(document).ready(function () { $('#exp').tokenInput('/zurmo-main/app/index.php/emailMessages/default/autoCompleteForMultiSelectAutoComplete', { queryParam: 'term',hintText: 'Type name or email',prePopulate: [{'id':'Betty.Allen@NordyneDefenseD.com','name':'Betty Allen (Betty.Allen@NordyneDefenseD.com)'}],preventDuplicates: 'true', classes: {tokenList: 'token-input-list'}});});</script>";
            return $content;
        }
    }
?>