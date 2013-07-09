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
     * Displays the standard boolean field
     * rendered as a check box.
     */
    class TaskCheckBoxListElement extends Element implements DerivedElementInterface
    {
        /**
         * Render A standard text input.
         * @return The element's content as a string.
         */
        protected function renderControlEditable()
        {
            throw new NotImplementedException($message, $code, $previous);
        }

        protected function getEditableHtmlOptions()
        {
            $htmlOptions             = array();
            $htmlOptions['id']       = $this->getEditableInputId();
            $htmlOptions['name']     = $this->getEditableInputName();
            if ($this->getDisabledValue())
            {
                $htmlOptions['disabled'] = $this->getDisabledValue();
                if (BooleanUtil::boolVal($this->model->{$this->attribute}))
                {
                    $htmlOptions['uncheckValue'] = 1;
                }
                if ($htmlOptions['disabled'] == 'disabled')
                {
                    $htmlOptions['labelClass'] = 'disabled';
                }
            }
            return $htmlOptions;
        }

        /**
         * Renders the attribute from the model.
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            $content  = null;
//            $data = $this->getExistingTaskCheckList();
//            if(!empty($data))
//            {
//                foreach($data as $checkbox)
//                {
//                    $content .= $checkbox->render();
//                }
//            }
            $content .= $this->renderTaskCheckListItems();
            $content .= $this->renderTaskCreateCheckItem();

            return $content;
        }

        /**
         * @return array
         */
        protected function getExistingTaskCheckList()
        {
            $existingCheckListItems = array();
            for ($i = 0; $i < count($this->model->checkListItem); $i++)
            {
                $checkListItemElement = new CheckBoxElement($this->model->checkListItem[$i], 'name', $this->form, $this->params);
                $existingCheckListItems[] = $checkListItemElement;
            }
            return $existingCheckListItems;
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element. For this element, there are no attributes from the model.
         * @return array - empty
         */
        public static function getModelAttributeNames()
        {
            return array();
        }

        /**
         * @return string
         */
        protected function renderLabel()
        {
            return $this->getFormattedAttributeLabel();
        }

        /**
         * @return string
         */
        protected function getFormattedAttributeLabel()
        {
            return Yii::app()->format->text(Zurmo::t('TasksModule', 'Tasks'));
        }

        protected function renderTaskCreateCheckItem()
        {
            $content            = null;
            $taskCheckListItem  = new TaskCheckListItem();
            $uniquePageId       = 'TaskCheckItemInlineEditForModelView';
            $redirectUrl        = Yii::app()->createUrl('/tasks/taskCheckItems/inlineCreateTaskCheckItemFromAjax',
                                                    array('id' => $this->model->id,
                                                          'uniquePageId' => $uniquePageId));
            $urlParameters      = array('relatedModelId'           => $this->model->id,
                                        'relatedModelClassName'    => 'Task',
                                        'relatedModelRelationName' => 'checkListItems',
                                        'redirectUrl'              => $redirectUrl); //After save, the url to go to.

            $inlineView         = new TaskCheckItemInlineEditView($taskCheckListItem, 'taskCheckItems', 'tasks', 'inlineCreateTaskCheckItemSave', $urlParameters, $uniquePageId);
            $content            .= $inlineView->render();
            $htmlOptions = array('id' => 'TaskCheckItemInlineEditForModelView');
            return ZurmoHtml::tag('div', $htmlOptions, $content);
        }

        protected function getNonEditableTemplate()
        {
            return '<th style="text-align:left; padding-left:5px;">{label}</th></tr><tr><td>{content}</td>';
        }

        protected function renderTaskCheckListItems()
        {
            $getParams      = array('relatedModelId'           => $this->model->id,
                                  'relatedModelClassName'    => get_class($this->model),
                                  'relatedModelRelationName' => 'checkListItems');
            $checkItemsData = TaskCheckListItem::getTaskCheckListItemsByTask($this->model->id);
            $view           = new TaskCheckListItemsForTaskView('taskCheckItems', 'tasks', $checkItemsData, $this->model, $this->form, $getParams, 'TaskCheckItemInlineEditForModelView');
            $content        = $view->render();
            return $content;
        }
    }
?>
