<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2014 Zurmo Inc.
     *
     * Zurmo is free software; you can redistribute it and/or modify it under
     * the terms of the GNU Affero General Public License version 3 as published by the
     * Free Software Foundation with the addition of the following permission added
     * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
     * IN WHICH THE COPYRIGHT IS OWNED BY ZURMO, ZURMO DISCLAIMS THE WARRANTY
     * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
     *
     * Zurmo is distributed in the hope that it will be useful, but WITHOUT
     * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
     * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
     * details.
     *
     * You should have received a copy of the GNU Affero General Public License along with
     * this program; if not, see http://www.gnu.org/licenses or write to the Free
     * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
     * 02110-1301 USA.
     *
     * You can contact Zurmo, Inc. with a mailing address at 27 North Wacker Drive
     * Suite 370 Chicago, IL 60606. or at email address contact@zurmo.com.
     *
     * The interactive user interfaces in original and modified versions
     * of this program must display Appropriate Legal Notices, as required under
     * Section 5 of the GNU Affero General Public License version 3.
     *
     * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
     * these Appropriate Legal Notices must retain the display of the Zurmo
     * logo and Zurmo copyright notice. If the display of the logo is not reasonably
     * feasible for technical reasons, the Appropriate Legal Notices must display the words
     * "Copyright Zurmo Inc. 2014. All rights reserved".
     ********************************************************************************/

    class ZurmoCssToInlineStylesUtil extends CssToInlineStyles
    {
        protected $combineStyleBlocks       = false;

        protected $moveStyleBlocksToBody    = false;

        public function setCombineStyleBlock($combineStyleBlocks = true)
        {
            $this->combineStyleBlocks  = $combineStyleBlocks;
        }

        public function setMoveStyleBlocksToBody($moveStyleBlocksToBody = true)
        {
            $this->moveStyleBlocksToBody    = $moveStyleBlocksToBody;
        }

        public function convert($outputXHTML = false)
        {
            $html   = parent::convert($outputXHTML);
            $html   = $this->moveStyleBlocks($html);
            return $html;
        }

        protected function moveStyleBlocks($html)
        {
            $this->ensureEitherStripeOrMoveIsSet();
            $styles             = $this->resolveStyleBlockContent();
            $html               = $this->stripOriginalStyleTags($html);
            if ($this->moveStyleBlocksToBody)
            {
                return $this->combineAndMoveStylesToBody($styles, $html);
            }
            return $this->combineAndMoveStylesToHead($styles, $html);
        }

        protected function combineAndMoveStylesToBody($styles, $html)
        {
            $html           = $this->combineAndMoveStyles($styles, $html, false);
            return $html;
        }

        protected function combineAndMoveStylesToHead($styles, $html)
        {
            $html           = $this->combineAndMoveStyles($styles, $html, true);
            return $html;
        }

        protected function combineAndMoveStyles($styles, $html, $moveToHead)
        {
            $search     = 'body';
            if ($moveToHead)
            {
                $search = '/head';
            }
            $matches        = preg_split('#(<' . $search . '.*?>)#i', $html, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            if ($moveToHead)
            {
                $styles     = $styles . $matches[1];
            }
            else
            {
                $styles     = $matches[1] . $styles;
            }
            $html           = $matches[0] . $styles . $matches[2];
            return $html;
        }

        protected function resolveStyleBlockContent()
        {
            $html               = $this->html;
            $matches            = array();
            preg_match_all('|<style(.*)>(.*)</style>|isU', $html, $matches);
            if ($this->combineStyleBlocks)
            {
                $styleBlockContent  = implode(PHP_EOL, $matches[2]);
                $style              = ZurmoHtml::tag('style', array(), $styleBlockContent);
            }
            else
            {
                $style              = implode(PHP_EOL, $matches[0]);
            }
            return $style;
        }

        protected function ensureEitherStripeOrMoveIsSet()
        {
            if ($this->stripOriginalStyleTags && $this->moveStyleBlocksToBody)
            {
                throw new NotSupportedException('stripOriginalStyleTags and moveStyleBlocksToBody are both set.');
            }
        }
    }
?>