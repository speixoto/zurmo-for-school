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

    /**
     * Class DupesSummaryView specific view to show the details view os all possible dupes for model
     */
    abstract class ModelsToMergeListAndChartView extends SecuredDetailsView
    {
        protected $dupeModels;

        protected $colorsArray = array('#98cdff', '#12cd11');

        public function __construct($controllerId, $moduleId, $model, $dupeModels)
        {
            $this->assertModelIsValid($model);
            $this->controllerId   = $controllerId;
            $this->moduleId       = $moduleId;
            $this->model          = $model;
            $this->modelClassName = get_class($model);
            $this->modelId        = $model->id;
            $this->dupeModels     = $dupeModels;
        }

        /**
         * Renders content for a view including a layout title, form toolbar,
         * and form layout.
         * @return A string containing the element's content.
         */
        protected function renderContent()
        {
            $leftContent = $this->renderSelectedContactsListWithCardView();
            $leftContainer = ZurmoHtml::tag('div', array('class' => 'left-column clearfix'), $leftContent);
            $rightContent = $this->renderRightSideContent();
            $rightContainer = ZurmoHtml::tag('div', array('class' => 'right-column'), $rightContent);
            return ZurmoHtml::tag('div', array('class' => 'chosen-entries'), $leftContainer . $rightContainer);
        }

        protected function renderSelectedContactsListWithCardView()
        {
            $label           = $this->getLabelForDupes();
            $maxWarning      = $this->getMaxWarning();
            $preparedContent = null;
            $modelsToShow    = $this->dupeModels;
            $this->resolveMaxModelsToShow($modelsToShow);
            $cards = null;
            $position = 1;
            foreach($modelsToShow as $key => $dupeModel)
            {
                $detailsViewContent = $this->renderDetailsViewForDupeModel($dupeModel);
                $display = ($key == 0) ? 'block' : 'none';
                $cards .= ZurmoHtml::tag('div', array('class' => 'sliding-panel business-card showing-panel',
                                                        'id'   => 'dupeDetailsView-' . $dupeModel->id,
                                                        'style' => 'display:' . $display),
                                          $detailsViewContent);
                $checked      = !strcmp($dupeModel->id, $this->model->id);
                $radioElement = ZurmoHtml::radioButton('primaryModelId', $checked,
                                                        array('id'     => 'primaryModelId-' . $dupeModel->id,
                                                              'class'  => 'dupeContactsPrimaryModel',
                                                              'value'  => $dupeModel->id
                                                             )) . strval($dupeModel);
                $contactNameElement = ZurmoHtml::tag('li', array('class' => 'selectedDupe merge-color-' . $position++,
                                                                 'id' => 'selectedDupe-' . $dupeModel->id),
                                                                 $radioElement);
                $preparedContent .= $contactNameElement;
            }
            $this->registerScripts();
            $cards = ZurmoHtml::tag('div', array('class' => 'cards'), $cards);
            $possibleMatches = ZurmoHtml::tag('ul', array('class' => 'possible-matches'), $label . $maxWarning . $preparedContent);
            return $possibleMatches . $cards;
        }

        protected function resolveMaxModelsToShow(& $models)
        {
            if (ModelsListDuplicateMergedModelForm::SELECTED_MODELS_COUNT > 0 && count($this->dupeModels) > ModelsListDuplicateMergedModelForm::SELECTED_MODELS_COUNT)
            {
                $models = array_slice($models, 0, ModelsListDuplicateMergedModelForm::SELECTED_MODELS_COUNT);
            }
        }

        protected function renderDetailsViewForDupeModel($model)
        {
            $content = null;
            if ($model instanceof User || $model instanceof Person)
            {
                $layout  = new PersonCardViewLayout($model);
            }
            elseif ($model instanceof Account)
            {
                $layout  = new AccountCardViewLayout($model);
            }
            else
            {
                throw new NotSupportedException();
            }
            $content  = $layout->renderContent();
            $content .= $this->renderActivitiesTotals($model);
            return $content;
        }

        protected function renderRightSideContent($form = null)
        {
            $content = ZurmoHtml::tag('div', array('class' => 'spidergraph'), $this->renderChart());
            return $content;
        }

        protected   function renderChart()
        {
            if (empty($this->dupeModels))
            {
                return null;
            }
            Yii::import('ext.amcharts.AmChartMaker');
            $chartId = 'dedupeChart';
            $amChart = new AmChartMaker();
            $amChart->categoryField    = 'category';
            $this->resolveDataForChart($amChart);
            $amChart->id               = $chartId;
            $amChart->type             = ChartRules::TYPE_RADAR;
            $this->resolveGraphsForChart($amChart);
            $scriptContent      = $amChart->javascriptChart();
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $chartId, $scriptContent);
            $cClipWidget        = new CClipWidget();
            $cClipWidget->beginClip("Chart" . $chartId);
            $cClipWidget->widget('application.core.widgets.AmChart', array('id' => $chartId, 'height' => '250px'));
            $cClipWidget->endClip();
            return $cClipWidget->getController()->clips['Chart' . $chartId];
        }

        protected function registerScripts()
        {
            $script = "$('body').on('mouseover', 'li.selectedDupe',
                        function()
                        {
                            var id = $(this).attr('id');
                            var idArray = id.split('-');
                            $('.business-card:visible').hide();
                            $('#dupeDetailsView-' + idArray[1]).show();
                        });
//                        $('body').on('mouseout', 'li.selectedDupe',
//                        function()
//                        {
//                            var id = $(this).attr('id');
//                            var idArray = id.split('-');
//                            $('#dupeDetailsView-' + idArray[1]).hide();
//                        });

                        $('body').on('click', '.graphDisplay',
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
                        $('body').on('change', '.dupeContactsPrimaryModel',
                            {$this->onChangeScript()}
                        );
                      ";
            Yii::app()->clientScript->registerScript(__CLASS__ . '#selectedContactMouseOverEvents', $script);
        }

        /**
         * When the user changes the dupe selection will trigger this function
         * Implement this as needed
         * @throws NotSupportedException
         */
        protected function onChangeScript()
        {
            throw new NotSupportedException();
        }

        /**
         * The label for the list of dupes
         */
        protected function getLabelForDupes()
        {
            $instructions   = Zurmo::t('ZurmoModule', 'Select primary {label}', array('{label}' => $this->getSingularLabel()));
            return ZurmoHtml::tag('span', array(), $instructions);
        }

        protected function getMaxWarning()
        {
            if (ModelsListDuplicateMergedModelForm::SELECTED_MODELS_COUNT > 0 && count($this->dupeModels) > ModelsListDuplicateMergedModelForm::SELECTED_MODELS_COUNT)
            {
                return Zurmo::t('ZurmoModule', 'Only showing the first {n} possible matches.', ModelsListDuplicateMergedModelForm::SELECTED_MODELS_COUNT);
            }
        }

        protected function getColorForDupe()
        {
            $color = array_shift($this->colorsArray);
            $this->colorsArray[] = $color;
            return $color;
        }

        /**
         * For each dupeModel adds a graph into the chart
         * @param $chart
         */
        protected function resolveGraphsForChart(& $chart)
        {
            foreach ($this->dupeModels as $dupeModel)
            {
                $chart->addSerialGraph('model-' . $dupeModel->id,
                                       'radar',
                                       array('bullet'      => "'round'",
                                             'balloonText' => "'Quantity: [[value]]'",
                                             'lineColor'   => "'" . $this->getColorForDupe() . "'"
                                       ));
            }
        }

        /**
         * For each dupeModel add total ammount of Notes, Tasks, Emails and Meetings
         * @param $chart
         */
        protected function resolveDataForChart(& $chart)
        {
            $notes    = array('category' => NotesModule::getModuleLabelByTypeAndLanguage('Plural'));
            $tasks    = array('category' => TasksModule::getModuleLabelByTypeAndLanguage('Plural'));
            $emails   = array('category' => EmailMessagesModule::getModuleLabelByTypeAndLanguage('Plural'));
            $meetings = array('category' => MeetingsModule::getModuleLabelByTypeAndLanguage('Plural'));
            foreach ($this->dupeModels as $dupeModel)
            {
                $itemId = $dupeModel->getClassId('Item');
                $notes   ['model-' . $dupeModel->id] = LatestActivitiesUtil::
                    getCountByModelClassName('Note', array($itemId), LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL);
                $tasks   ['model-' . $dupeModel->id] = LatestActivitiesUtil::
                    getCountByModelClassName('Task', array($itemId), LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL);
                $emails  ['model-' . $dupeModel->id] = LatestActivitiesUtil::
                    getCountByModelClassName('EmailMessage', array($itemId), LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL);
                $meetings['model-' . $dupeModel->id] = LatestActivitiesUtil::
                    getCountByModelClassName('Meeting', array($itemId), LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL);
            }
            $chart->data = array($notes, $tasks, $emails, $meetings);
        }

        protected function renderActivitiesTotals($model)
        {
            $itemId = $model->getClassId('Item');
            $notesTotalContent    = ZurmoHtml::tag('span',
                                                  array('class' => 'total-notes'),
                                                  LatestActivitiesUtil::getCountByModelClassName
                                                      ('Note', array($itemId), LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL));
            $tasksTotalContent    = ZurmoHtml::tag('span',
                                                   array('class' => 'total-tasks'),
                                                   LatestActivitiesUtil::getCountByModelClassName
                                                       ('Task', array($itemId), LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL));
            $emailsTotalContent   = ZurmoHtml::tag('span',
                                                   array('class' => 'total-emails'),
                                                   LatestActivitiesUtil::getCountByModelClassName
                                                       ('EmailMessage', array($itemId), LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL));
            $meetingsTotalContent = ZurmoHtml::tag('span',
                                                   array('class' => 'total-meetings'),
                                                   LatestActivitiesUtil::getCountByModelClassName
                                                       ('Meeting', array($itemId), LatestActivitiesConfigurationForm::OWNED_BY_FILTER_ALL));
            $content = $notesTotalContent . $tasksTotalContent . $emailsTotalContent . $meetingsTotalContent;
            return ZurmoHtml::tag('div', array('class' => 'entry-stats'), $content);
        }

        protected function getSingularLabel()
        {
            $modelClass  = get_class($this->model);
            $moduleClass = $modelClass::getModuleClassName();
            return $moduleClass::getModuleLabelByTypeAndLanguage('Singular');
        }
    }
?>
