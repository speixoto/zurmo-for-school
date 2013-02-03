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

    abstract class ComponentForReportWizardView extends MetadataView
    {
        protected $model;

        protected $form;

        protected $hideView;

        abstract protected function renderFormContent();

        public function getTitle()
        {
            return Zurmo::t('ReportsModule', 'Report Wizard') . ' - ' . static::getWizardStepTitle();
        }

        public static function getWizardStepTitle()
        {
            throw new NotImplementedException();
        }

        public function __construct(ReportWizardForm $model, ReportActiveForm $form, $hideView = false)
        {
            assert('is_bool($hideView)');
            $this->model    = $model;
            $this->form     = $form;
            $this->hideView = $hideView;
        }

        public function isUniqueToAPage()
        {
            return true;
        }

        protected function renderContent()
        {
            $content              = $this->renderTitleContent();
            $content             .= $this->renderFormContent();
            $actionElementContent = $this->renderActionElementBar(true);
            if ($actionElementContent != null)
            {
                $content .= $this->resolveAndWrapDockableViewToolbarContent($actionElementContent);
            }
            $this->registerScripts();
            return $content;
        }

        /**
         * Override if needed
         */
        protected function registerScripts()
        {
        }

        protected function renderActionElementBar($renderedInForm)
        {
            return $this->renderActionLinksContent();
        }

        /**
         * Given a form, render the content for the action links at the bottom of the view and return the content as
         * a string.
         * @param object $form
         */
        protected function renderActionLinksContent()
        {
            $previousPageLinkContent = $this->renderPreviousPageLinkContent();
            $nextPageLinkContent     = $this->renderNextPageLinkContent();
            $content                 = null;
            if ($previousPageLinkContent)
            {
                $content .= $previousPageLinkContent;
            }
            if ($nextPageLinkContent)
            {
                $content .= $nextPageLinkContent;
            }
            return $content;
        }

        /**
         * Override if the view should show a previous link.
         */
        protected function renderPreviousPageLinkContent()
        {
            return ZurmoHtml::link(ZurmoHtml::tag('span', array('class' => 'z-label'),
                   Zurmo::t('ReportsModule', 'Previous')), '#', array('id' => static::getPreviousPageLinkId()));
        }

        /**
         * Override if the view should show a next link.
         */
        protected function renderNextPageLinkContent()
        {
            $params = array();
            $params['label']       = Zurmo::t('ReportsModule', 'Next');
            $params['htmlOptions'] = array('id' => static::getNextPageLinkId(),
                                           'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $searchElement = new SaveButtonActionElement(null, null, null, $params);
            return $searchElement->render();
        }

        public static function getPreviousPageLinkId()
        {
            throw new NotSupportedException();
        }

        public static function getNextPageLinkId()
        {
            throw new NotSupportedException();
        }

        protected function getViewStyle()
        {
            if($this->hideView)
            {
                return ' style="display:none;"';
            }
        }

        protected function renderTitleContent()
        {
            return ZurmoHtml::tag('h3',   array(), $this->getTitle());
        }

        protected function renderAttributesAndRelationsTreeContent()
        {
            $content  = ZurmoHtml::tag('div', array('id' => static::getTreeDivId(), 'class' => 'hasTree loading'), '');
            return $content;
        }
    }
?>