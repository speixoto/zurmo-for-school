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

    // TODO: @Shoaibi/@Jason: Low: This should be refactored and used everywhere instead of manually creating clip.
    abstract class AutoCompleteTextElement extends TextElement
    {
        abstract protected function getWidgetValue();

        abstract protected function getSource();

        abstract protected function getOptions();

        /**
         * (non-PHPdoc)
         * @see TextElement::renderControlEditable()
         */
        protected function renderControlEditable()
        {
            $cClipWidget             = new CClipWidget();
            $clipId                  = $this->getWidgetClipName();
            $cClipWidget->beginClip($clipId);
            $cClipWidget->widget('zii.widgets.jui.CJuiAutoComplete', array(
                'name'        => $this->getWidgetName(),
                'id'          => $this->getWidgetId(),
                'value'       => $this->getWidgetValue(),
                'source'      => $this->getSource(),
                'options'     => $this->getOptions(),
                'htmlOptions' => $this->getHtmlOptions(),

            ));
            $cClipWidget->endClip();
            $content = $cClipWidget->getController()->clips[$clipId];
            return $content;
        }

        protected function getWidgetId()
        {
            return $this->getEditableInputId();
        }

        protected function getWidgetClipName()
        {
            return get_class($this);
        }

        protected function getWidgetName()
        {
            return $this->getEditableInputName();
        }
    }
?>
