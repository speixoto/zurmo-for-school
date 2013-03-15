<?php
    class MinimalDynamicLabelMbMenu extends MbMenu
    {
        protected function renderMenuRecursive($items)
        {
            foreach ($items as $item)
            {
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
                    $resolvedLabelContent = ZurmoHtml::tag('em', array(), '') . ZurmoHtml::tag('span', array(), $item['label']);
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
                    echo ZurmoHtml::link('<span></span>' . $resolvedLabelContent, $item['url'], $htmlOptions);
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
                if (isset($item['items']) && count($item['items']))
                {
                    echo "\n" . ZurmoHtml::openTag('ul', $this->submenuHtmlOptions) . "\n";
                    $this->renderMenuRecursive($item['items']);
                    echo ZurmoHtml::closeTag('ul') . "\n";
                }
                echo ZurmoHtml::closeTag('li') . "\n";
            }
        }

        protected static function resolveAndGetSpanAndDynamicLabelContent($item)
        {
            if (isset($item['dynamicLabelContent']))
            {
                return $item['dynamicLabelContent'];
            }
        }
    }
?>
