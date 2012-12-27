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

    class ReportToWizardFormAdapter
    {
        protected $report;

        public function __construct(Report $report)
        {
            $this->report = $report;
        }

        public static function getFormClassNameByType($type)
        {
            assert('is_string($type)');
            if($type == Report::TYPE_ROWS_AND_COLUMNS)
            {
                return 'RowsAndColumnsReportWizardForm';
            }
            elseif($type == Report::TYPE_SUMMATION)
            {
                return 'SummationReportWizardForm';
            }
            elseif($type == Report::TYPE_MATRIX)
            {
                return 'MatrixReportWizardForm';
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public function makeFormByType()
        {
            if($this->report->getType() == Report::TYPE_ROWS_AND_COLUMNS)
            {
                return $this->makeRowsAndColumnsWizardForm();
            }
            elseif($this->report->getType() == Report::TYPE_SUMMATION)
            {
                return $this->makeSummationWizardForm();
            }
            elseif($this->report->getType() == Report::TYPE_MATRIX)
            {
                return $this->makeMatrixWizardForm();
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public function makeRowsAndColumnsWizardForm()
        {
            $formModel       = new RowsAndColumnsReportWizardForm();
            $this->setCommonAttributes($formModel);
            return $formModel;
        }

        public function makeSummationWizardForm()
        {
            $formModel             = new SummationReportWizardForm();
            $this->setCommonAttributes($formModel);
            return $formModel;
        }

        public function makeMatrixWizardForm()
        {
            $formModel       = new MatrixReportWizardForm();
            $this->setCommonAttributes($formModel);
            return $formModel;
        }

        protected function setCommonAttributes(ReportWizardForm $formModel)
        {
            $formModel->id               = $this->report->getId();
            $formModel->description      = $this->report->getDescription();
            $formModel->moduleClassName  = $this->report->getModuleClassName();
            if($this->report->getOwner()->id > 0)
            {
                $formModel->ownerId      = (int)$this->report->getOwner()->id;
                $formModel->ownerName    = strval($this->report->getOwner());
            }
            $formModel->name             = $this->report->getName();
            $formModel->type             = $this->report->getType();
            $formModel->filtersStructure = $this->report->getFiltersStructure();

            $formModel->currencyConversionType     = $this->report->getCurrencyConversionType();
            $formModel->spotConversionCurrencyCode = $this->report->getSpotConversionCurrencyCode();

            if($this->report->isNew())
            {
                $formModel->setIsNew();
            }
            $formModel->setExplicitReadWriteModelPermissions($this->report->getExplicitReadWriteModelPermissions());
            $formModel->filters                       = $this->report->getFilters();
            $formModel->orderBys                      = $this->report->getOrderBys();
            $formModel->groupBys                      = $this->report->getGroupBys();
            $formModel->displayAttributes             = $this->report->getDisplayAttributes();
            $formModel->drillDownDisplayAttributes    = $this->report->getDrillDownDisplayAttributes();
            $formModel->chart                         = $this->report->getChart();
        }
    }
?>