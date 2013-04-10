<?php
    class MinimalDynamicLabelMbMenu extends MbMenu
    {
        // TODO: @Shoaibi: Low: Refactor this and MbMenu
        protected function renderMenuRecursive($items)
        {
            foreach ($items as $item)
            {
                $liClose    = null;
                $rendered   = false;
                if (!array_key_exists('renderHeader', $item) || $item['renderHeader'])
                {
                    $rendered   = true;
                    $liClose    = ZurmoHtml::closeTag('li') . "\n";
                    echo ZurmoHtml::openTag('li', isset($item['itemOptions']) ? $item['itemOptions'] : array());
                    if (isset($item['linkOptions']))
                    {
                         $htmlOptions = $item['linkOptions'];
                    }
                    else
                    {
                        $htmlOptions = array();
                    }
                    if (!empty($item['label']))
                    {
                        $resolvedLabelContent = $this->renderLabelPrefix() . ZurmoHtml::tag('span', array(), $item['label']);
                    }
                    else
                    {
                        $resolvedLabelContent = static::resolveAndGetSpanAndDynamicLabelContent($item);
                    }
                    if ((isset($item['ajaxLinkOptions'])))
                    {
                        echo ZurmoHtml::ajaxLink($resolvedLabelContent, $item['url'], $item['ajaxLinkOptions'], $htmlOptions);
                    }
                    elseif (isset($item['url']))
                    {
                        echo ZurmoHtml::link($this->renderLinkPrefix() . $resolvedLabelContent, $item['url'], $htmlOptions);
                    }
                    else
                    {
                        if (!empty($item['label']))
                        {
                            echo ZurmoHtml::link($resolvedLabelContent, "javascript:void(0);", $htmlOptions);
                        }
                        else
                        {
                            echo $resolvedLabelContent;
                        }
                    }
                }
                if (isset($item['items']) && count($item['items']))
                {
                    $nestedUlOpen   = null;
                    $nestedUlClose  = null;
                    if ($rendered)
                    {
                        $nestedUlOpen   = "\n" . ZurmoHtml::openTag('ul', $this->submenuHtmlOptions) . "\n";
                        $nestedUlClose  = ZurmoHtml::closeTag('ul') . "\n";
                    }
                    echo $nestedUlOpen;
                    $this->renderMenuRecursive($item['items']);
                    echo $nestedUlClose;
                }
                echo $liClose;
            }
        }

        protected static function resolveAndGetSpanAndDynamicLabelContent($item)
        {
            if (isset($item['dynamicLabelContent']))
            {
                return $item['dynamicLabelContent'];
            }
        }

        protected function resolveNavigationClass()
        {
            if (!Yii::app()->userInterface->isMobile())
            {
                parent::resolveNavigationClass();
            }
        }
    }
?>
