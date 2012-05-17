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
     * Override for any functions that need special handling for the zurmo application.
     */
    class ZurmoHtml extends CHtml
    {
        /**
         * Override to handle relation model error summary information.  This information needs to be parsed properly
         * otherwise it will show up as 'Array' for the error text.
         * @see CHtml::errorSummary()
         */
        public static function errorSummary($model, $header = null, $footer = null, $htmlOptions = array())
        {
            $content = '';
            if (!is_array($model))
            {
                $model = array($model);
            }
            if (isset($htmlOptions['firstError']))
            {
                $firstError = $htmlOptions['firstError'];
                unset($htmlOptions['firstError']);
            }
            else
            {
                $firstError = false;
            }
            foreach ($model as $m)
            {
                foreach ($m->getErrors() as $errors)
                {
                    foreach ($errors as $errorOrRelatedError)
                    {
                        if (is_array($errorOrRelatedError))
                        {
                            foreach ($errorOrRelatedError as $relatedError)
                            {
                                if ($relatedError != '')
                                {
                                    $content .= "<li>$relatedError</li>\n";
                                }
                            }
                        }
                        elseif ($errorOrRelatedError != '')
                        {
                            $content .= "<li>$errorOrRelatedError</li>\n";
                        }
                        if ($firstError)
                        {
                            break;
                        }
                    }
                }
            }
            if ($content !== '')
            {
                if ($header === null)
                {
                    $header = '<p>' . Yii::t('yii', 'Please fix the following input errors:') . '</p>';
                }
                if (!isset($htmlOptions['class']))
                {
                    $htmlOptions['class'] = CHtml::$errorSummaryCss;
                }
                return CHtml::tag('div', $htmlOptions, $header."\n<ul>\n$content</ul>" . $footer);
            }
            else
            {
                return '';
            }
        }

         /**
          * This function overrides the radioButtonList from CHtml and excepts a new variable which consists of select
          * box to be appended to the label element.
          */
        public static function radioButtonList($name, $select, $data, $htmlOptions = array(),
                                               $dataSelectOption = array())
        {
            $template   =   isset($htmlOptions['template'])?$htmlOptions['template']:'{input} {label}';
            $separator  =   isset($htmlOptions['separator'])?$htmlOptions['separator']:"<br/>\n";
            unset($htmlOptions['template'], $htmlOptions['separator']);

            $labelOptions   =   isset($htmlOptions['labelOptions'])?$htmlOptions['labelOptions']:array();
            unset($htmlOptions['labelOptions']);

            $items  = array();
            $baseID = self::getIdByName($name);
            $id     = 0;
            foreach ($data as $value => $label)
            {
                $checked                =   !strcmp($value, $select);
                $htmlOptions['value']   =   $value;
                $htmlOptions['id']      =   $baseID . '_' . $id++;
                $option                 =   self::radioButton($name, $checked, $htmlOptions);
                $label                  =   self::label($label, $htmlOptions['id'], $labelOptions);
                $selectOption           =   "";
                if (isset($dataSelectOption[$value]))
                {
                    $selectOption       =   str_replace("{bindId}", $htmlOptions['id'], $dataSelectOption[$value]);
                }
                $items[] = strtr($template, array('{input}'    =>  $option,
                                                  '{label}'    =>  $label . $selectOption));
            }
            return implode($separator, $items);
        }

        public static function activeCheckBox($model, $attribute, $htmlOptions = array())
        {
            self::resolveNameID($model, $attribute, $htmlOptions);
            if (isset($htmlOptions['disabled']))
            {
                $disabledClass = ' disabled';
            }
            else
            {
                $disabledClass = '';
            }
            if (!isset($htmlOptions['value']))
            {
                $htmlOptions['value'] = 1;
            }
            if (!isset($htmlOptions['checked']) && self::resolveValue($model, $attribute) == $htmlOptions['value'])
            {
                $htmlOptions['checked'] = 'checked';
            }
            self::clientChange('click', $htmlOptions);
            if (array_key_exists('uncheckValue', $htmlOptions))
            {
                $uncheck = $htmlOptions['uncheckValue'];
                unset($htmlOptions['uncheckValue']);
            }
            else
            {
                $uncheck = '0';
            }
            $hiddenOptions = isset($htmlOptions['id']) ? array('id' => self::ID_PREFIX . $htmlOptions['id']) : array('id' => false);
            $hidden = $uncheck !== null ? self::hiddenField($htmlOptions['name'], $uncheck, $hiddenOptions) : '';
            return $hidden . CHtml::tag("label", array("class" => "hasCheckBox" . $disabledClass),
                   self::activeInputField('checkbox', $model, $attribute, $htmlOptions));
        }

        /**
         * Override to add proper styling to checkboxes.
         * @see CHtml::checkBox
         */
        public static function checkBox($name, $checked = false, $htmlOptions = array())
        {
            if ($checked)
            {
                $htmlOptions['checked']='checked';
            }
            else
            {
                unset($htmlOptions['checked']);
            }
            $value = isset($htmlOptions['value']) ? $htmlOptions['value'] : 1;
            self::clientChange('click', $htmlOptions);

            if (array_key_exists('uncheckValue', $htmlOptions))
            {
                $uncheck = $htmlOptions['uncheckValue'];
                unset($htmlOptions['uncheckValue']);
            }
            else
            {
                $uncheck = null;
            }

            if ($uncheck !== null)
            {
                // add a hidden field so that if the radio button is not selected, it still submits a value
                if (isset($htmlOptions['id']) && $htmlOptions['id'] !== false)
                {
                    $uncheckOptions = array('id' => self::ID_PREFIX . $htmlOptions['id']);
                }
                else
                {
                    $uncheckOptions = array('id' => false);
                }
                $hidden = self::hiddenField($name, $uncheck, $uncheckOptions);
            }
            else
            {
                $hidden = '';
            }

            // add a hidden field so that if the checkbox  is not selected, it still submits a value
            return $hidden . CHtml::tag("label", array("class" => "hasCheckBox"), self::inputField('checkbox', $name, $value, $htmlOptions));
        }

    /**
     * Override to support namespacing and unbinding before binding any clientChange click actions.
     * @see CHtml::ajaxLink
     */
    public static function ajaxLink($text, $url, $ajaxOptions = array(), $htmlOptions = array())
    {
        if(!isset($htmlOptions['href']))
        {
            $htmlOptions['href'] = '#';
        }
        $ajaxOptions['url']      = $url;
        $htmlOptions['ajax']     = $ajaxOptions;
        self::clientChange('click', $htmlOptions);
        if(isset($htmlOptions['namespace']))
        {
            unset($htmlOptions['namespace']);
        }
        return self::tag('a',$htmlOptions,$text);
    }

    /**
     * Override to support namespacing.  Namespacing is important because if there is a namespace defined, then whatever
     * binding for the even is occuring, will be first unbinded.  This is important because in an ajax load, you can
     * have things double or triple bound.  This resolves that issue. If you want the binding to have an attempted
     * unbind first, then set the name space.
     * @see CHtml::clientChange();
     */
    protected static function clientChange($event, &$htmlOptions)
    {
        if(!isset($htmlOptions['submit']) && !isset($htmlOptions['confirm']) && !isset($htmlOptions['ajax']))
        {
            return;
        }
        if(isset($htmlOptions['namespace']))
        {
            $namespace = true;
            $event     = $event . '.' . $htmlOptions['namespace'];
            unset($htmlOptions['namespace']);
        }
        else
        {
            $namespace = false;
        }
        if(isset($htmlOptions['live']))
        {
            $live = $htmlOptions['live'];
            unset($htmlOptions['live']);
        }
        else
        {
            $live = self::$liveEvents;
        }
        if(isset($htmlOptions['return']) && $htmlOptions['return'])
        {
            $return = 'return true';
        }
        else
        {
            $return = 'return false';
        }
        if(isset($htmlOptions['on' . $event]))
        {
            $handler = trim($htmlOptions['on' . $event], ';') . ';';
            unset($htmlOptions['on' . $event]);
        }
        else
            $handler='';

        if(isset($htmlOptions['id']))
        {
            $id=$htmlOptions['id'];
        }
        else
        {
            $id = $htmlOptions['id'] = isset($htmlOptions['name']) ? $htmlOptions['name']: self::ID_PREFIX.self::$count++;
        }
        $cs = Yii::app()->getClientScript();
        $cs->registerCoreScript('jquery');

        if(isset($htmlOptions['submit']))
        {
            $cs->registerCoreScript('yii');
            $request = Yii::app()->getRequest();
            if($request->enableCsrfValidation && isset($htmlOptions['csrf']) && $htmlOptions['csrf'])
            {
                $htmlOptions['params'][$request->csrfTokenName]=$request->getCsrfToken();
            }
            if(isset($htmlOptions['params']))
            {
                $params=CJavaScript::encode($htmlOptions['params']);
            }
            else
            {
                $params='{}';
            }
            if($htmlOptions['submit']!=='')
            {
                $url = CJavaScript::quote(self::normalizeUrl($htmlOptions['submit']));
            }
            else
            {
                $url = '';
            }
            $handler .= "jQuery.yii.submitForm(this,'$url',$params);{$return};";
        }

        if(isset($htmlOptions['ajax']))
        {
            $handler.=self::ajax($htmlOptions['ajax'])."{$return};";
        }
        if(isset($htmlOptions['confirm']))
        {
            $confirm='confirm(\''.CJavaScript::quote($htmlOptions['confirm']).'\')';
            if($handler!=='')
                $handler="if($confirm) {".$handler."} else return false;";
            else
                $handler="return $confirm;";
        }

        if($live)
        {
            if($namespace)
            {
               $cs->registerScript('Yii.CHtml.#' . $id, "$('body').off('$event', '#$id'); $('body').on('$event','#$id',function(){{$handler}});");
            }
            else
            {
                $cs->registerScript('Yii.CHtml.#' . $id, "$('body').on('$event', '#$id',function(){{$handler}});");
            }
        }
        else
        {
            if($namespace)
            {
                $cs->registerScript('Yii.CHtml.#' . $id, "$('#$id').off('$event'); $('#$id').on('$event', function(){{$handler}});");
            }
            else
            {
                $cs->registerScript('Yii.CHtml.#' . $id, "$('#$id').on('$event', function(){{$handler}});");
            }

        }
        unset($htmlOptions['params'],
              $htmlOptions['submit'],
              $htmlOptions['ajax'],
              $htmlOptions['confirm'],
              $htmlOptions['return'],
              $htmlOptions['csrf']);
    }
    }
?>