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

    class SavedCalendarEditAndDetailsView extends SecuredEditAndDetailsView
    {
        /**
         * @return array
         */
        public static function getDefaultMetadata()
        {
            $metadata = array(
                'global' => array(
                    'toolbar' => array(
                        'elements' => array(
                            array('type' => 'SaveButton',    'renderType' => 'Edit'),
                            array('type' => 'CancelLink',    'renderType' => 'Edit'),
                            array('type' => 'EditLink',      'renderType' => 'Details'),
                            array('type' => 'CopyLink',      'renderType' => 'Details')
                        ),
                    ),
                    'nonPlaceableAttributeNames' => array(
                        'owner'
                    ),
                    'panelsDisplayType' => FormLayout::PANELS_DISPLAY_TYPE_ALL,
                    'panels' => array(
                        array(
                            'rows' => array(
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'name', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'description', 'type' => 'TextArea'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'location', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'moduleClassName', 'type' => 'CalendarModuleClassNameDropDown'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'startAttributeName', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'endAttributeName', 'type' => 'Text'),
                                            ),
                                        ),
                                    )
                                ),
                                array('cells' =>
                                    array(
                                        array(
                                            'elements' => array(
                                                array('attributeName' => 'timeZone', 'type' => 'TimeZoneStaticDropDown',
                                                      'addBlank' => true),
                                            ),
                                        ),
                                    )
                                ),
                            ),
                        ),
                    ),
                ),
            );
            return $metadata;
        }

        /**
         * @return string
         */
        protected function getNewModelTitleLabel()
        {
            return Zurmo::t('CalendarsModule', 'Create CalendarsModuleSingularLabel',
                                     LabelUtil::getTranslationParamsForAllModules());
        }

        /**
         * @return string
         */
        public static function getModuleClassName()
        {
            return 'CalendarsModule';
        }

        /**
         * @return string
         */
        protected function renderAfterFormLayout($form)
        {
            $content = parent::renderAfterFormLayout($form);
            return $content . $this->renderFiltersContent($form);
        }

        /**
         * @return string
         */
        protected function renderFiltersContent($form)
        {
            assert('$form instanceof ZurmoActiveForm');
            $content = null;
            $report  = SavedCalendarToReportAdapter::makeReportBySavedCalendar($this->model);

            //todo: if CREATE - then we don't have the moduleClassName....
            //todo: need to figure out how to deal with this and default it. i think if moduleclassname is not selected, we shouldn't show 'filters' link
            //todo: anyways. so we need to get taht working correctly.
            //todo: maybe default moduleClassName on new saved cal to Meetings since we won't have an empty anyways.

            $adapter = new ReportToWizardFormAdapter($report);
            $reportWizardForm = $adapter->makeRowsAndColumnsWizardForm();
            $filtersForReportWizardViewClassName = static::getFiltersForReportWizardViewClassName();
            $filtersForReportWizardView = new $filtersForReportWizardViewClassName($reportWizardForm, $form, false); //todo: maybe hide view by default or not?
            $content .= $filtersForReportWizardView->render();
            $this->registerFiltersScripts();
            $this->registerModuleClassNameChangeScript();
            $this->registerFiltersCss();
            return $content;
        }

        /**
         * Register filter scripts.
         */
        protected function registerFiltersScripts()
        {
            Yii::app()->getClientScript()->registerCoreScript('treeview');
            Yii::app()->clientScript->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.modules.reports.views.assets')) . '/ReportUtils.js');
            Yii::app()->clientScript->registerScript('clickflow', "
                function loadFiltersTreeView()
                {
                    " . RowsAndColumnsReportForSavedCalendarWizardView::renderTreeViewAjaxScriptContent(
                            static::getFormId(), static::getFiltersForReportWizardViewClassName(), Report::TYPE_ROWS_AND_COLUMNS) . "
                }
                loadFiltersTreeView();
            ");
            Yii::app()->getClientScript()->registerCoreScript('bbq');
            OperatorStaticDropDownElement::registerOnLoadAndOnChangeScript();
        }

        /**
         * Register filter css.
         */
        protected function registerFiltersCss()
        {
            Yii::app()->getClientScript()->registerCssFile(Yii::app()->getClientScript()->getCoreScriptUrl() .
                                                           '/treeview/jquery.treeview.css');
        }

        /**
         * @return string
         */
        protected static function getFormClassName()
        {
            return 'WizardActiveForm';
        }

        /**
         * @return array
         */
        protected function resolveActiveFormAjaxValidationOptions()
        {
            return array('enableAjaxValidation' => true,
                //todo: add ? 'modelClassNameForError'  => get_class($this->model like in WizardView?
                         'clientOptions'        => $this->getClientOptions());
        }

        /**
         * @return array
         */
        protected function getClientOptions()
        {
            return array(
                'validateOnSubmit'  => true,
                'validateOnChange'  => false,
                'beforeValidate'    => 'js:$(this).beforeValidateAction',
                'afterValidate'     => 'js:$(this).afterValidateAjaxAction',
                'afterValidateAjax' => $this->renderConfigSaveAjax(static::getFormId()),
            );
        }

        /**
         * @return array
         */
        protected function renderConfigSaveAjax($formName)
        {
            // Begin Not Coding Standard
            return ZurmoHtml::ajax(array(
                'type' => 'POST',
                'data' => 'js:$("#' . $formName . '").serialize()',
                'url'  =>  $this->getValidateAndSaveUrl(),
             //   'update' => '#' . $this->uniquePageId,
            ));
            // End Not Coding Standard
        }

        /**
         * @return string
         */
        protected function getValidateAndSaveUrl()
        {
            return Yii::app()->createUrl($this->moduleId . '/' . $this->controllerId . '/' .
                                         Yii::app()->controller->action->id, $_GET);
        }

        protected function registerModuleClassNameChangeScript()
        {
            //todo: add handling for altering the startAttributeName and end AttributeName inputs..
            $moduleClassNameId = get_class($this->model) .  '[moduleClassName]';
            $filtersForReportWizardViewClassName = static::getFiltersForReportWizardViewClassName();
            Yii::app()->clientScript->registerScript('moduleForSavedCalendarChangeScript', "
                $('[name=\"" . $moduleClassNameId . "\"]').live('change', function()
                    {
                        $('#" . $filtersForReportWizardViewClassName . "').find('.dynamic-rows').find('ul:first').find('li').remove();
                        $('#FiltersTreeArea').html('');
                        $('." . $filtersForReportWizardViewClassName::getZeroComponentsClassName() . "').show();
                        rebuildReportFiltersAttributeRowNumbersAndStructureInput('" .
                                $filtersForReportWizardViewClassName . "');
                        loadFiltersTreeView();
                    }
                );
            ");
        }

        /**
         * @return string
         */
        protected static function getFiltersForReportWizardViewClassName()
        {
            return 'SavedCalendarFiltersForReportWizardView';
        }
    }
?>
