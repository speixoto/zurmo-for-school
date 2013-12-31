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
     * Class DupesSummaryView specific view to show the details view os all possible dupes for model
     */
    abstract class DupesSummaryView extends SecuredDetailsView
    {
        const MAX_NUMBER_OF_MODELS_TO_SHOW = 0;

        protected $dupeModels;

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
            $leftContainer = ZurmoHtml::tag('div', array('class' => 'left-column'), $leftContent);
            $rightContent = $this->renderRightSideContent();
            $rightContainer = ZurmoHtml::tag('div', array('class' => 'right-column'), $rightContent);
            $content = ZurmoHtml::tag('div', array('class' => 'full-width', 'style' => 'height:200px;border:solid 1px #cccccc'),
                                                $leftContainer . $rightContainer);
            return ZurmoHtml::tag('div',
                                  array('class' => 'details-table'),
                                  $content);
        }

        protected function renderSelectedContactsListWithCardView()
        {
            $label           = $this->getLabelForDupes();
            $maxWarning      = $this->getMaxWarning();
            $preparedContent = $label . $maxWarning . '<ul>';
            $modelsToShow    = $this->dupeModels;
            $this->resolveMaxModelsToShow($modelsToShow);
            foreach($modelsToShow as $dupeModel)
            {
                $detailsViewContent = $this->renderDetailsViewForDupeModel($dupeModel);
                $content = ZurmoHtml::tag('div', array('class' => 'sliding-panel business-card showing-panel',
                                                        'id'   => 'dupeDetailsView-' . $dupeModel->id,
                                                        'style' => 'display:none'),
                                          $detailsViewContent);
                $checked      = !strcmp($dupeModel->id, $this->model->id);
                $radioElement = ZurmoHtml::radioButton('primaryModelId', $checked,
                                                        array('id'     => 'primaryModelId-' . $dupeModel->id,
                                                              'class'  => 'dupeContactsPrimaryModel',
                                                              'value'  => $dupeModel->id
                                                             )) . strval($dupeModel);
                $contactNameElement = ZurmoHtml::tag('li', array('class' => 'selectedDupe',
                                                                 'id' => 'selectedDupe-' . $dupeModel->id),
                                                                $radioElement) . $content;
                $preparedContent .= $contactNameElement;
            }
            $preparedContent .= '</ul>';
            $this->registerScripts();
            return $preparedContent;
        }

        protected function resolveMaxModelsToShow(& $models)
        {
            if (static::MAX_NUMBER_OF_MODELS_TO_SHOW > 0 && count($this->dupeModels) > static::MAX_NUMBER_OF_MODELS_TO_SHOW)
            {
                $models = array_slice($models, 0, static::MAX_NUMBER_OF_MODELS_TO_SHOW);
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
            $content = $layout->renderContent();
            return $content;
        }

        protected function renderRightSideContent($form = null)
        {
            $chartContent = $this->renderChart();
            $divContent   = ZurmoHtml::tag('div', array('class' => 'spidergraph', 'style' => 'display:none'), $chartContent);
            $spanContent  = ZurmoHtml::tag('span', array('class' => 'graphDisplay'), Zurmo::t('ZurmoModule', 'Show'));
            return $spanContent . $divContent;
        }

        protected function renderChart()
        {
            Yii::import('ext.amcharts.AmChartMaker');
            $chartId = 'dedupeChart';
            $amChart = new AmChartMaker();
            $amChart->categoryField    = 'category';
            $amChart->data             = array(array('category' => 'Notes',    'model1' => 1,  'model2' => 8),
                                               array('category' => 'Taks',     'model1' => 10, 'model2' => 5),
                                               array('category' => 'Emails',   'model1' => 2,  'model2' => 8),
                                               array('category' => 'Meetings', 'model1' => 5,  'model2' => 4)
            );
            $amChart->id               = $chartId;
            $amChart->type             = ChartRules::TYPE_RADAR;
            $amChart->addSerialGraph('model1', 'radar', array('bullet' => "'round'", 'balloonText' => "'Quantity: [[value]]'", 'lineColor' => "'#98cdff'"));
            $amChart->addSerialGraph('model2', 'radar', array('bullet' => "'round'", 'balloonText' => "'Quantity: [[value]]'", 'lineColor' => "'#12cd11'"));
            $scriptContent      = $amChart->javascriptChart();
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $chartId, $scriptContent);
            $cClipWidget        = new CClipWidget();
            $cClipWidget->beginClip("Chart" . $chartId);
            $cClipWidget->widget('application.core.widgets.AmChart', array('id' => $chartId));
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
                            $('#dupeDetailsView-' + idArray[1]).show();
                        });
                        $('body').on('mouseout', 'li.selectedDupe',
                        function()
                        {
                            var id = $(this).attr('id');
                            var idArray = id.split('-');
                            $('#dupeDetailsView-' + idArray[1]).hide();
                        });

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
            return null;
        }

        protected function getMaxWarning()
        {
            if (static::MAX_NUMBER_OF_MODELS_TO_SHOW > 0 && count($this->dupeModels) > static::MAX_NUMBER_OF_MODELS_TO_SHOW)
            {
                return Zurmo::t('ZurmoModule', 'Only showing the first {n} possible matches.', static::MAX_NUMBER_OF_MODELS_TO_SHOW);
            }
        }
    }
?>
