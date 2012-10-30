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

    class ZurmoActiveForm extends CActiveForm
    {
        /**
         * Allows the form to be bound as a live event. This will allow the form to stay active even after an ajax
         * action done through the yiiGridView for example.
         * @var boolean
         */
        public $bindAsLive = false;

        /**
         * Override to handle relation model error summary information.  This information needs to be parsed properly
         * otherwise it will show up as 'Array' for the error text.
         * @see CActiveForm::errorSummary()
         */
        public function errorSummary($models, $header = null, $footer = null, $htmlOptions = array())
        {
            if (!$this->enableAjaxValidation && !$this->enableClientValidation)
            {
                return ZurmoHtml::errorSummary($models, $header, $footer, $htmlOptions);
            }
            if (!isset($htmlOptions['id']))
            {
                $htmlOptions['id'] = $this->id . '_es_';
            }
            $html = ZurmoHtml::errorSummary($models, $header, $footer, $htmlOptions);
            if ($html === '')
            {
                if ($header === null)
                {
                    $header = '<p>' . Yii::t('yii', 'Please fix the following input errors:') . '</p>';
                }
                if (!isset($htmlOptions['class']))
                {
                    $htmlOptions['class'] = ZurmoHtml::$errorSummaryCss;
                }
                if (isset($htmlOptions['style']))
                {
                    $htmlOptions['style'] = rtrim($htmlOptions['style'], ';') . ';display:none';
                }
                else
                {
                    $htmlOptions['style'] = 'display:none';
                }
                $html = ZurmoHtml::tag('div', $htmlOptions, $header . "\n<ul><li>dummy</li></ul>" . $footer);
            }

            $this->summaryID = $htmlOptions['id'];
            return $html;
        }

        /**
         * Override to allow for optional live binding of yiiactiveform. @see $bindAsLive.
         */
        public function run()
        {
            if (is_array($this->focus))
            {
                $this->focus="#" . ZurmoHtml::activeId($this->focus[0], $this->focus[1]);
            }
            echo ZurmoHtml::endForm();
            $cs = Yii::app()->clientScript;
            $cs->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('application.core.views.assets')
                    ) . '/FormUtils.js',
                CClientScript::POS_END
            );
            if (!$this->enableAjaxValidation && !$this->enableClientValidation || empty($this->attributes))
            {
                if ($this->focus !== null)
                {
                    $cs->registerCoreScript('jquery');
                    $cs->registerScript('CActiveForm#focus', "
                        if (!window.location.hash)
                            { $('" . $this->focus . "').focus(); }
                    ");
                }
                return;
            }
            $options = $this->clientOptions;
            if (isset($this->clientOptions['validationUrl']) && is_array($this->clientOptions['validationUrl']))
            {
                $options['validationUrl'] = ZurmoHtml::normalizeUrl($this->clientOptions['validationUrl']);
            }

            $options['attributes'] = array_values($this->attributes);

            if ($this->summaryID !== null)
            {
                $options['summaryID'] = $this->summaryID;
            }
            if ($this->focus !== null)
            {
                $options['focus'] = $this->focus;
            }

            $options = CJavaScript::encode($options);
            //Not registering via coreScript because it does not properly register when using ajax non-minified
            //on home page myList config view.  Needs a better solution
            $cs->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('system.web.js.source')
                    ) . '/jquery.yii.js',
                CClientScript::POS_END
            );
            $cs->registerScriptFile(
                Yii::app()->getAssetManager()->publish(
                    Yii::getPathOfAlias('system.web.js.source')
                    ) . '/jquery.yiiactiveform.js',
                CClientScript::POS_END
            );
            $id = $this->id;
            if ($this->bindAsLive)
            {
                $cs->registerScript(__CLASS__. '#' . $id, "\$('#$id').live('focus', function(e)
                {
                    if ($(this).data('settings') == undefined)
                    {
                        $(this).yiiactiveform($options);
                    }
                });
                ");
            }
            else
            {
                $cs->registerScript(__CLASS__. '#' . $id, "\$('#$id').yiiactiveform($options);");
            }
        }

        /**
         * (non-PHPdoc)
         * @see CActiveForm::radioButtonList()
         */
        public function radioButtonList($model, $attribute, $data, $htmlOptions = array())
        {
            return ZurmoHtml::activeRadioButtonList($model, $attribute, $data, $htmlOptions);
        }

        /**
         * Override to support adding label class = 'hasCheckBox'
         * (non-PHPdoc)
         * @see CActiveForm::checkBox()
         */
        public function checkBox($model, $attribute, $htmlOptions = array())
        {
            return ZurmoHtml::activeCheckBox($model, $attribute, $htmlOptions);
        }

        /**
         * (non-PHPdoc)
         * @see CActiveForm::checkBoxList()
         */
        public function checkBoxList($model, $attribute, $data, $htmlOptions = array())
        {
            return ZurmoHtml::activeCheckBoxList($model, $attribute, $data, $htmlOptions);
        }

        /**
         * (non-PHPdoc)
         * @see CActiveForm::dropDownList()
         */
        public function dropDownList($model, $attribute, $data, $htmlOptions = array())
        {
            return ZurmoHtml::activeDropDownList($model, $attribute, $data, $htmlOptions);
        }
    }
?>