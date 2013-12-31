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

    class ContactListViewMergeSummaryView extends SecuredDetailsView
    {
        protected $selectedContacts;

        /**
         * Constructs a detail view specifying the controller as
         * well as the model that will have its details displayed.
         */
        public function __construct($controllerId, $moduleId, $model, $selectedContacts)
        {
            $this->assertModelIsValid($model);
            $this->controllerId   = $controllerId;
            $this->moduleId       = $moduleId;
            $this->model          = $model;
            $this->modelClassName = get_class($model);
            $this->modelId        = $model->id;
            $this->selectedContacts = $selectedContacts;
        }

        /**
         * Renders content for a view including a layout title, form toolbar,
         * and form layout.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $content  = '<div class="details-table">';
            $leftContent = $this->renderSelectedContactsListWithCardView();
            $leftContainer = ZurmoHtml::tag('div', array('class' => 'left-column'), $leftContent);
            $rightContent = $this->renderRightSideContent();
            $rightContainer = ZurmoHtml::tag('div', array('class' => 'right-column'), $rightContent);
            $content .= ZurmoHtml::tag('div', array('class' => 'full-width', 'style' => 'height:200px;border:solid 1px #cccccc'),
                                                $leftContainer . $rightContainer);
            $content .= '</div>';
            return $content;
        }

        protected function renderSelectedContactsListWithCardView()
        {
            $preparedContent = '<ul>';
            foreach($this->selectedContacts as $contact)
            {
                $layout  = new PersonCardViewLayout($contact);
                $content = $layout->renderContent();
                $content = ZurmoHtml::tag('div', array('class' => 'sliding-panel business-card showing-panel',
                                                        'id'   => 'businessCardView-' . $contact->id,
                                                        'style' => 'display:none'),
                                                $content);
                $checked      = !strcmp($contact->id, $this->model->id);
                $radioElement = ZurmoHtml::radioButton('primaryModelId', $checked,
                                                        array('id'     => 'primaryModelId-' . $contact->id,
                                                              'class'  => 'mergeContactsPrimaryModel',
                                                              'value'  => $contact->id
                                                             )) . strval($contact);
                $contactNameElement = ZurmoHtml::tag('li', array('class' => 'selectedContact',
                                                                 'id' => 'selectedContact-' . $contact->id),
                                                                $radioElement) . $content;
                $preparedContent .= $contactNameElement;
            }
            $preparedContent .= '</ul>';
            $this->registerScripts();
            return $preparedContent;
        }

        protected function renderRightSideContent($form = null)
        {
            return '<span class="graphDisplay">Show</span><div class="spidergraph" style="display:none">
                        Spider graph displayed here</div>';
        }

        protected function registerScripts()
        {
            $url = Yii::app()->request->getUrl();
            $script = "$('.selectedContact').mouseover(
                        function()
                        {
                            var id = $(this).attr('id');
                            var idArray = id.split('-');
                            $('#businessCardView-' + idArray[1]).show();
                        });
                        $('.selectedContact').mouseout(
                        function()
                        {
                            var id = $(this).attr('id');
                            var idArray = id.split('-');
                            $('#businessCardView-' + idArray[1]).hide();
                        });

                        $('.graphDisplay').click(
                        function()
                        {
                            if($('.spidergraph').is(':visible'))
                            {
                                $('.spidergraph').hide();
                                $('.graphDisplay').html('Show');
                            }
                            else
                            {
                                $('.spidergraph').show();
                                $('.graphDisplay').html('Hide');
                            }
                        });
                        $('.mergeContactsPrimaryModel').change(
                        function()
                        {
                            var id = $(this).attr('id');
                            var idArray = id.split('-');
                            window.location.href = '{$url}' + '&primaryModelId=' + idArray[1];
                        });
                      ";
            Yii::app()->clientScript->registerScript('selectedContactMouseOverEvents', $script);
        }
    }
?>
