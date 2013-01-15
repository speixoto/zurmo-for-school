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
     * A sequential process needs a container view to start with.  The container view renders the progress bar
     * and a message that displays through the entire sequence.  The sequence process view gets rendered via
     * the ajax calls and updates the div in the process view with new content.
     * @see SequenceProcessView.
     */
    class SequentialProcessContainerView extends ProcessView
    {
        /**
         * This is the intial SequenceProcessView that will be rendered.
         * @var object SequenceProcessView
         */
        protected $containedView;

        /**
         * A message that will be displayed throughout the entire sequence.
         * @var string
         */
        protected $allStepsMessage;

        protected $title;

        public function __construct($containedView, $allStepsMessage, $title = null)
        {
            assert('$containedView instanceof SequentialProcessView');
            assert('is_string($allStepsMessage)');
            assert('is_string($title) || $title == null');
            $this->containedView   = $containedView;
            $this->allStepsMessage = $allStepsMessage;
            $this->title           = $title;
        }

        protected function renderContent()
        {
            $content  = '<div>';
            $content .= $this->renderTitleContent();
            $content .= '<div class="process-container-view">';
            $content .= "<h3>" . $this->allStepsMessage . '</h3>';
            $content .= '<div class="progressbar-wrapper"><span id="progress-percent">0&#37;</span>' .
                        $this->renderProgressBarContent() . '</div>';
            $content .= '</div>';
            $content .= '<div id="' . $this->containerViewId . '" class="process-container-view">';
            $content .= $this->containedView->render();
            $content .= '</div></div>';
            return $content;
        }

        protected function renderProgressBarContent()
        {
            $cClipWidget = new CClipWidget();
            $cClipWidget->beginClip("ProgressBar");
            $cClipWidget->widget('zii.widgets.jui.CJuiProgressBar', array(
                'id'         => $this->getProgressBarId(),
                'value'      => 0,
            ));
            $cClipWidget->endClip();
            return  $cClipWidget->getController()->clips['ProgressBar'];
        }
    }
?>