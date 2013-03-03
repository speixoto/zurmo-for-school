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
     * Base view class for components that appear in wizard based modules such as reporting and workflow
     */
    abstract class ComponentForWizardModelView extends MetadataView
    {
        /**
         * @var WizardForm
         */
        protected $model;

        /**
         * @var WizardActiveForm
         */
        protected $form;

        /**
         * @var bool
         */
        protected $hideView;

        /**
         * @return string
         */
        abstract protected function renderFormContent();

        /**
         * Override in child class as needed
         * @throws NotImplementedException
         */
        public static function getWizardStepTitle()
        {
            throw new NotImplementedException();
        }

        /**
         * Override in child class as needed
         * @throws NotSupportedException
         */
        public static function getPreviousPageLinkId()
        {
            throw new NotSupportedException();
        }

        /**
         * Override in child class as needed
         * @throws NotImplementedException
         */
        public static function getNextPageLinkId()
        {
            throw new NotSupportedException();
        }

        /**
         * @param WizardForm $model
         * @param WizardActiveForm $form
         * @param bool $hideView
         */
        public function __construct(WizardForm $model, WizardActiveForm $form, $hideView = false)
        {
            assert('is_bool($hideView)');
            $this->model    = $model;
            $this->form     = $form;
            $this->hideView = $hideView;
        }

        /**
         * @return bool
         */
        public function isUniqueToAPage()
        {
            return true;
        }

        /**
         * @return string
         */
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

        /**
         * @param $renderedInForm
         * @return null|string
         */
        protected function renderActionElementBar($renderedInForm)
        {
            return $this->renderActionLinksContent();
        }

        /**
         * @return null|string
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
            $params                = array();
            $params['label']       = Zurmo::t('ReportsModule', 'Next');
            $params['htmlOptions'] = array('id' => static::getNextPageLinkId(),
                                           'onclick' => 'js:$(this).addClass("attachLoadingTarget");');
            $element               = new SaveButtonActionElement(null, null, null, $params);
            return $element->render();
        }

        /**
         * @return null|string
         */
        protected function getViewStyle()
        {
            if($this->hideView)
            {
                return ' style="display:none;"';
            }
        }

        /**
         * @return string
         */
        protected function renderTitleContent()
        {
            return ZurmoHtml::tag('h3',   array(), $this->getTitle());
        }

        /**
         * @return string
         */
        protected function renderAttributesAndRelationsTreeContent()
        {
            $content  = ZurmoHtml::tag('div', array('id' => static::getTreeDivId(), 'class' => 'hasTree loading'), '');
            return $content;
        }


        /**
         * @return string
         */
        protected function getZeroComponentsContent()
        {
            if($this->getItemsCount() > 0)
            {
                $style = ' style="display:none;"';
            }
            else
            {
                $style = null;
            }
            $content = '<div class="' . static::getZeroComponentsClassName() . '" ' . $style . '>';
            $content .= $this->getZeroComponentsMessageContent();
            $content .= '</div>';
            return $content;
        }
    }
?>