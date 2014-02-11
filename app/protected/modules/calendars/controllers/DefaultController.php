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

    class CalendarsDefaultController extends ZurmoModuleController
    {
        public function filters()
        {
            $modelClassName   = $this->getModule()->getPrimaryModelName();
            $viewClassName    = $modelClassName . 'EditAndDetailsView';
            return array_merge(parent::filters(),
                array(
                    array(
                        ZurmoBaseController::REQUIRED_ATTRIBUTES_FILTER_PATH . ' + create, createFromRelation, edit',
                        'moduleClassName' => get_class($this->getModule()),
                        'viewClassName'   => $viewClassName,
                   ),
                    array(
                        ZurmoModuleController::ZERO_MODELS_CHECK_FILTER_PATH . ' + list, index',
                        'controller' => $this,
                   ),
               )
            );
        }

        /**
         * Redirect to combined details view for calendar.
         * @param int $id
         */
        public function actionDetails($id = null)
        {
            $urlParams = array($this->getId() . '/combinedDetails');
            $this->redirect($urlParams);
        }

        /**
         * Create the calendar.
         */
        public function actionCreate()
        {
            $savedCalendar                  = new SavedCalendar();
            $savedCalendar->moduleClassName = 'MeetingsModule';
            $attributes                     = CalendarUtil::getModelAttributesForSelectedModule($savedCalendar->moduleClassName);
            $attributeKeys                  = array_keys($attributes);
            $savedCalendar->startAttributeName = $attributeKeys[0];
            $editAndDetailsView = $this->makeEditAndDetailsView(
                //todo: call attemptToValidate before here, then just attemptToSave... look at User defualtCOntroller public function actionCreate()
                                            $this->resolveReportDataAndSaveCalendar($savedCalendar), 'Edit');
            $view               = new CalendarsPageView(ZurmoDefaultViewUtil::
                                                        makeStandardViewForCurrentUser($this, $editAndDetailsView));
            echo $view->render();
        }

        /**
         * Edit the calendar.
         * @param int $id
         * @param string $redirectUrl
         */
        public function actionEdit($id, $redirectUrl = null)
        {
            $savedCalendar = SavedCalendar::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($savedCalendar);
            $this->processEdit($savedCalendar, $redirectUrl);
        }

        /**
         * Process edit of the calendar.
         * @param SavedCalendar $calendar
         * @param string $redirectUrl
         */
        protected function processEdit(SavedCalendar $calendar, $redirectUrl = null)
        {
            //todo: call attemptToValidate before here, then just attemptToSave... look at User defualtCOntroller public function actionCreate()
            $view = new CalendarsPageView(ZurmoDefaultViewUtil::
                            makeStandardViewForCurrentUser($this,
                            $this->makeEditAndDetailsView(
                                $this->resolveReportDataAndSaveCalendar($calendar), 'Edit')));
            echo $view->render();
        }

        /**
         * Combined details for the calendar.
         */
        public function actionCombinedDetails()
        {
            $dataProvider               = CalendarUtil::getCalendarItemsDataProvider();
            $interactiveCalendarView    = new CombinedCalendarView($dataProvider, $this->getId(), $this->getModule()->getId());
            $view                       = new CalendarsPageView(ZurmoDefaultViewUtil::
                                                  makeStandardViewForCurrentUser($this,$interactiveCalendarView));
            echo $view->render();
        }

        /**
         * Render relations and attributes tree
         * @param string $type
         * @param string $treeType
         * @param int $id
         * @param string $nodeId
         */
        public function actionRelationsAndAttributesTree($type, $treeType, $id = null, $nodeId = null)
        {
            $report        = CalendarUtil::resolveReportBySavedCalendarPostData($type, $id);
            if ($nodeId != null)
            {
                $reportToTreeAdapter = new ReportRelationsAndAttributesToTreeAdapter($report, $treeType);
                echo ZurmoTreeView::saveDataAsJson($reportToTreeAdapter->getData($nodeId));
                Yii::app()->end(0, false);
            }
            $view        = new ReportRelationsAndAttributesForSavedCalendarTreeView($type, $treeType, 'edit-form');
            $content     = $view->render();
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        //todO: refactor to reuse same code in controller for reporting? do if it makes sense after done.
        public function actionAddAttributeFromTree($type, $treeType, $nodeId, $rowNumber,
                                                   $trackableStructurePosition = false, $id = null)
        {
            $report                             = CalendarUtil::resolveReportBySavedCalendarPostData($type, $id);
            $nodeIdWithoutTreeType              = ReportRelationsAndAttributesToTreeAdapter::
                                                     removeTreeTypeFromNodeId($nodeId, $treeType);
            $moduleClassName                    = $report->getModuleClassName();
            $modelClassName                     = $moduleClassName::getPrimaryModelName();
            $form                               = new WizardActiveForm();
            $form->id                           = 'edit-form';
            $form->enableAjaxValidation         = true; //ensures error validation populates correctly

            $wizardFormClassName                = ReportToWizardFormAdapter::getFormClassNameByType($report->getType());
            $model                              = ComponentForReportFormFactory::makeByComponentType($moduleClassName,
                                                      $modelClassName, $report->getType(), $treeType);
            $form->modelClassNameForError       = $wizardFormClassName;
            $attribute                          = ReportRelationsAndAttributesToTreeAdapter::
                                                      resolveAttributeByNodeId($nodeIdWithoutTreeType);
            $model->attributeIndexOrDerivedType = ReportRelationsAndAttributesToTreeAdapter::
                                                      resolveAttributeByNodeId($nodeIdWithoutTreeType);
            $inputPrefixData                    = ReportRelationsAndAttributesToTreeAdapter::
                                                      resolveInputPrefixData($wizardFormClassName,
                                                      $treeType, (int)$rowNumber);
            $adapter                            = new ReportAttributeForSavedCalendarToElementAdapter($inputPrefixData, $model,
                                                      $form, $treeType);
            $view                               = new AttributeRowForReportComponentView($adapter,
                                                      (int)$rowNumber, $inputPrefixData, $attribute,
                                                      (bool)$trackableStructurePosition, true, $treeType);
            $content               = $view->render();
            $form->renderAddAttributeErrorSettingsScript($view::getFormId());
            Yii::app()->getClientScript()->setToAjaxMode();
            Yii::app()->getClientScript()->render($content);
            echo $content;
        }

        /**
         * Override to handle report filters
         * @param SavedCalendar | ModelForm $model
         * @param string $postVariableName
         * @throws NotSupportedException();
         */
        protected function attemptToValidateAjaxFromPost($model, $postVariableName)
        //todo: should be called before attemptToSaveModelFromPost( in create/ and edit/ actions
        {
            if (isset($_POST['ajax']) && $_POST['ajax'] == 'edit-form')
            {
                $postData          = PostUtil::getData();
                $sanitizedPostdata = PostUtil::sanitizePostByDesignerTypeForSavingModel($model, $_POST[$postVariableName]);
                $model->setAttributes($sanitizedPostdata);
                $model->validate();
                $wizardFormClassName  = ReportToWizardFormAdapter::getFormClassNameByType(Report::TYPE_ROWS_AND_COLUMNS);
                if (!isset($postData[$wizardFormClassName]))
                {
                    throw new NotSupportedException();
                }
                $report = SavedCalendarToReportAdapter::makeReportBySavedCalendar($model);
                DataToReportUtil::resolveFiltersStructure($postData[$wizardFormClassName], $report);
                DataToReportUtil::resolveFilters($postData[$wizardFormClassName], $report);
                //This would do the filter and filter structure validation
                $reportToWizardFormAdapter = new ReportToWizardFormAdapter($report);
                $reportForm                     = $reportToWizardFormAdapter->makeFormByType();
                $postData['validationScenario'] = $wizardFormClassName::FILTERS_VALIDATION_SCENARIO;
                ReportUtil::validateReportWizardForm($postData, $reportForm);
                //todo: this validation above ReportUtil::validateReportWizardForm should append to errorData below
                //and make one error data array that is rendered
                $errorData = ZurmoActiveForm::makeErrorsDataAndResolveForOwnedModelAttributes($model);
                echo CJSON::encode($errorData);
                Yii::app()->end(0, false);
            }
        }


        /**
         * Resolve report data and save calendar.
         * @param SavedCalendar $savedCalendar
         * @return \SavedCalendar
         * @throws NotSupportedException
         */
        //todo: the special part of this method regarding adding filters to the savedCalendar should be done inside an override
        //todo: in an controllerUTIL override.
        protected function resolveReportDataAndSaveCalendar(SavedCalendar $savedCalendar)
        {
            if (isset($_POST['SavedCalendar']))
            {
                $postData   = PostUtil::getData();
                ControllerSecurityUtil::resolveAccessCanCurrentUserWriteModel($savedCalendar);
                $this->attemptToSaveModelFromPost($savedCalendar, null, false, false);
                if(count($savedCalendar->getErrors()) == 0)
                {
                    $wizardFormClassName  = ReportToWizardFormAdapter::getFormClassNameByType(Report::TYPE_ROWS_AND_COLUMNS);
                    if (!isset($postData[$wizardFormClassName]))
                    {
                        throw new NotSupportedException();
                    }
                    $report = SavedCalendarToReportAdapter::makeReportBySavedCalendar($savedCalendar);
                    DataToReportUtil::resolveFiltersStructure($postData[$wizardFormClassName], $report);
                    DataToReportUtil::resolveFilters($postData[$wizardFormClassName], $report);

                    //This would do the filter and filter structure validation
                    $reportToWizardFormAdapter = new ReportToWizardFormAdapter($report);
                    $model                     = $reportToWizardFormAdapter->makeFormByType();
                    if (isset($postData['ajax']) && $postData['ajax'] === 'edit-form')
                    {
                        $postData['validationScenario'] = $wizardFormClassName::FILTERS_VALIDATION_SCENARIO;
                        ReportUtil::validateReportWizardForm($postData, $model);
                    }
                    CalendarUtil::saveCalendarWithSerializedData($report, $savedCalendar, $postData[$wizardFormClassName]);
                }
                else
                {
                    $errorData = ZurmoActiveForm::makeErrorsDataAndResolveForOwnedModelAttributes($savedCalendar);
                    echo CJSON::encode($errorData);
                    Yii::app()->end(0, false);
                }
            }
            return $savedCalendar;
        }

        /**
         * Get events for the selected calendars.
         */
        public function actionGetEvents($selectedMyCalendarIds = null,
                                        $selectedSharedCalendarIds = null,
                                        $startDate = null,
                                        $endDate = null,
                                        $dateRangeType = null)
        {
            ZurmoConfigurationUtil::setByUserAndModuleName(Yii::app()->user->userModel,
                                                               'CalendarsModule',
                                                               'myCalendarStartDate', $startDate);
            ZurmoConfigurationUtil::setByUserAndModuleName(Yii::app()->user->userModel,
                                                               'CalendarsModule',
                                                               'myCalendarEndDate', $endDate);
            ZurmoConfigurationUtil::setByUserAndModuleName(Yii::app()->user->userModel,
                                                               'CalendarsModule',
                                                               'myCalendarDateRangeType', $dateRangeType);
            $dataProvider               = CalendarUtil::processUserCalendarsAndMakeDataProviderForCombinedView($selectedMyCalendarIds,
                                                                                                               $selectedSharedCalendarIds,
                                                                                                               $dateRangeType,
                                                                                                               $startDate,
                                                                                                               $endDate);
            $items                      = CalendarUtil::getFullCalendarItems($dataProvider);
            echo CJSON::encode($items);
        }

        /**
         * Deletes a calendar.
         * @param string $id
         */
        public function actionDelete($id)
        {
            $savedCalendar = SavedCalendar::getById(intval($id));
            ControllerSecurityUtil::resolveAccessCanCurrentUserDeleteModel($savedCalendar);
            $savedCalendar->delete();
            $dataProvider                        = CalendarUtil::getCalendarItemsDataProvider();
            $savedCalendarSubscriptions          = $dataProvider->getSavedCalendarSubscriptions();
            $content                             = CalendarUtil::makeCalendarItemsList($savedCalendarSubscriptions->getMySavedCalendarsAndSelected(),
                                                                                       'mycalendar[]', 'mycalendar', 'saved');
            echo $content;
        }

        /**
         * Renders modal list for the shared calendars for the user.
         */
        public function actionModalList()
        {
            $modalListLinkProvider = new SelectFromSharedCalendarsModalListLinkProvider(
                                            CalendarUtil::getModalContainerId(),
                                            'shared-calendars-list'
                                        );
            echo ModalSearchListControllerUtil::
                 setAjaxModeAndRenderModalSearchList($this, $modalListLinkProvider, 'SharedCalendersStateMetadataAdapter');
        }

        /**
         * Add subscription for calendar.
         * @param int $id
         */
        public function actionAddSubsriptionForCalendar($id)
        {
            $savedCalendar                       = SavedCalendar::getById(intval($id));
            $user                                = Yii::app()->user->userModel;
            $savedCalendarSubscription           = new SavedCalendarSubscription();
            $savedCalendarSubscription->user     = $user;
            $savedCalendarSubscription->savedcalendar = $savedCalendar;
            $savedCalendarSubscription->save();
            $dataProvider                        = CalendarUtil::getCalendarItemsDataProvider();
            $savedCalendarSubscriptions          = $dataProvider->getSavedCalendarSubscriptions();
            $content                             = CalendarUtil::makeCalendarItemsList($savedCalendarSubscriptions->getSubscribedToSavedCalendarsAndSelected(),
                                                                                       'sharedcalendar[]', 'sharedcalendar', 'shared');
            echo $content;
        }

        /**
         * Remove the subscription for the calendar.
         * @param int $id
         */
        public function actionUnsubscribe($id)
        {
            $savedCalendarSubscription = SavedCalendarSubscription::getById(intval($id));
            $savedCalendarSubscription->delete();
            $dataProvider                        = CalendarUtil::getCalendarItemsDataProvider();
            $savedCalendarSubscriptions          = $dataProvider->getSavedCalendarSubscriptions();
            $content                             = CalendarUtil::makeCalendarItemsList($savedCalendarSubscriptions->getSubscribedToSavedCalendarsAndSelected(),
                                                                                       'sharedcalendar[]', 'sharedcalendar', 'shared');
            echo $content;
        }

        /**
         * Get date time attributes.
         * @param string $moduleName
         * @param string $attribute
         */
        public function actionGetDateTimeAttributes($moduleName, $attribute)
        {
            assert('is_string($attribute)');
            assert('is_string($moduleName)');
            $data = CalendarUtil::getModelAttributesForSelectedModule($moduleName);
            $htmlOptions = array();
            if($attribute == 'endAttributeName')
            {
                $htmlOptions['empty'] = Zurmo::t('Core', '(None)');
            }
            echo ZurmoHtml::listOptions('', $data, $htmlOptions);
        }
    }
?>