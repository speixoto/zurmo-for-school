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
     * Helper class for adapting an attribute to an Element
     */
    class WorkflowAttributeToElementAdapter extends WizardModelAttributeToElementAdapter
    {
        /**
         * @return string
         * @throws NotSupportedException if the treeType is invalid or null
         */
        public function getContent()
        {
            $this->form->setInputPrefixData($this->inputPrefixData);
            if($this->treeType == ComponentForWorkflowForm::TYPE_TIME_TRIGGER)
            {
                $content = $this->getContentForTimeTrigger();
            }
            elseif($this->treeType == ComponentForWorkflowForm::TYPE_TRIGGERS)
            {
                $content = $this->getContentForTrigger();
            }
            elseif($this->treeType == ComponentForWorkflowForm::TYPE_ACTIONS)
            {
                $content = $this->getContentForAction();
            }
            else
            {
                throw new NotSupportedException();
            }
            $this->form->clearInputPrefixData();
            return $content;
        }

        /**
         * @return string
         * @throws NotSupportedException if the workflowType is on-save since that workflow type does not have
         * a time trigger
         */
        protected function getContentForTimeTrigger()
        {
            if($this->model->getWorkflowType() == Workflow::TYPE_ON_SAVE)
            {
                throw new NotSupportedException();
            }
            $content                            = $this->getContentForTimeTriggerOrTrigger();
            $params                             = array('inputPrefix' => $this->inputPrefixData);
            $durationElement                    = new TimeTriggerDurationStaticDropDownElement($this->model,
                                                  'durationSeconds', $this->form, $params);
            $durationElement->editableTemplate  = '{content}{error}'; //todo: remove brs just here for now so we can pick from dd in ui
            $durationContent                    = '<BR><BR><BR>' .$durationElement->render();
            self::resolveDivWrapperForContent($durationContent, $content, 'dynamic-attribute-duration');
            return $content;
        }

        /**
         * @return string
         */
        protected function getContentForTrigger()
        {
            return $this->getContentForTimeTriggerOrTrigger();
        }

        /**
         * @return string
         * @throws NotSupportedException if the valueElementType is null
         */
        protected function getContentForTimeTriggerOrTrigger()
        {
            $params                                 = array('inputPrefix' => $this->inputPrefixData);
            if($this->model->hasAvailableOperatorsType())
            {
                $operatorElement                    = new OperatorStaticDropDownElement($this->model, 'operator', $this->form, $params);
                $operatorElement->editableTemplate  = '{content}{error}';
                $operatorContent                    = $operatorElement->render();
            }
            else
            {
                $operatorContent                    = null;
            }
            $valueElementType                       = $this->model->getValueElementType();
            if($valueElementType != null)
            {
                $valueElementClassName              = $valueElementType . 'Element';
                $valueElement                       = new $valueElementClassName($this->model, 'value', $this->form, $params);
                if($valueElement instanceof NameIdElement)
                {
                    $valueElement->setIdAttributeId('value');
                    $valueElement->setNameAttributeName('stringifiedModelForValue');
                }
                if($valueElement instanceof MixedNumberTypesElement)
                {
                    $valueElement->editableTemplate = '<div class="value-data">{content}{error}</div>';
                }
                elseif($valueElement instanceof MixedDateTypesElement)
                {
                    $valueElement->editableTemplate = '<div class="dynamic-attribute-operator">{valueType}</div>' .
                                                      '<div class="value-data has-date-inputs">' .
                                                      '<div class="first-value-area">{content}{error}</div></div>';
                }
                else
                {
                    $startingDivStyleFirstValue     = null;
                    if (in_array($this->model->getOperator(), array(OperatorRules::TYPE_IS_NULL, OperatorRules::TYPE_IS_NOT_NULL)))
                    {
                        $startingDivStyleFirstValue = "display:none;";
                    }
                    $valueElement->editableTemplate = '<div class="value-data"><div class="first-value-area" style="' .
                                                      $startingDivStyleFirstValue . '">{content}{error}</div></div>';
                }
                $valueContent                       = $valueElement->render();
            }
            else
            {
                throw new NotSupportedException();
            }
            $content                                = $this->renderAttributeIndexOrDerivedType();
            self::resolveDivWrapperForContent($this->model->getDisplayLabel(), $content, 'dynamic-attribute-label');
            self::resolveDivWrapperForContent($operatorContent,                $content, 'dynamic-attribute-operator');
            $content                               .= $valueContent;
            return $content;
        }

        /**
         * @return string
         */
        protected function getContentForAction()
        {
            $groupByAxisElement = null; //todo: become a edit link and change the name of this from groupby to something else
            $content                                  = 'fxithis'.$this->renderAttributeIndexOrDerivedType();
            self::resolveDivWrapperForContent($this->model->getDisplayLabel(), $content, 'dynamic-attribute-label');
            self::resolveDivWrapperForContent($groupByAxisElement,             $content, 'dynamic-attribute-field');
            return $content;
        }
    }
?>