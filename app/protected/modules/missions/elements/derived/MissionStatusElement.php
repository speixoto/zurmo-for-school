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

    /**
     * Display the mission status with the action button when applicable.
     */
    class MissionStatusElement extends Element implements DerivedElementInterface
    {
        protected function renderEditable()
        {
            throw new NotSupportedException();
        }

        protected function renderControlEditable()
        {
            throw new NotSupportedException();
        }

        /**
         * Render the full name as a non-editable display
         * @return The element's content.
         */
        protected function renderControlNonEditable()
        {
            assert('$this->attribute == "status"');
            assert('$this->model instanceof Mission');
            return self::renderStatusTextAndActionArea($this->model);
        }

        public static function renderStatusTextAndActionArea(Mission $mission)
        {
            $statusText        = self::renderStatusTextContent($mission);
            $statusAction      = self::renderStatusActionContent($mission, self::getStatusChangeDivId($mission->id));
            if ($statusAction != null)
            {
                $content = $statusAction;
            }
            else
            {
                $content = $statusText;
            }
            return ZurmoHtml::tag('div', array('id' => self::getStatusChangeDivId($mission->id), 'class' => 'missionStatusChangeArea'), $content);
        }

        public static function getStatusChangeDivId($missionId)
        {
            return  'MissionStatusChangeArea-' . $missionId;
        }

        public static function renderStatusTextContent(Mission $mission)
        {
            if ($mission->status == Mission::STATUS_AVAILABLE)
            {
                return ZurmoHtml::wrapLabel(Zurmo::t('Core', 'Available'), 'mission-status');
            }
            elseif ($mission->status == Mission::STATUS_TAKEN)
            {
                return ZurmoHtml::wrapLabel(Zurmo::t('Core', 'In Progress'), 'mission-status');
            }
            elseif ($mission->status == Mission::STATUS_COMPLETED)
            {
                return ZurmoHtml::wrapLabel(Zurmo::t('Core', 'Awaiting Acceptance'), 'mission-status');
            }
            elseif ($mission->status == Mission::STATUS_REJECTED)
            {
                return ZurmoHtml::wrapLabel(Zurmo::t('Core', 'Rejected'), 'mission-status');
            }
            elseif ($mission->status == Mission::STATUS_ACCEPTED)
            {
                return ZurmoHtml::wrapLabel(Zurmo::t('Core', 'Accepted'), 'mission-status');
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public static function renderStatusActionContent(Mission $mission, $updateDivId)
        {
            assert('is_string($updateDivId)');
            if ($mission->status == Mission::STATUS_AVAILABLE &&
               !$mission->owner->isSame(Yii::app()->user->userModel))
            {
                return self::renderAjaxStatusActionChangeLink(Mission::STATUS_TAKEN, $mission->id,
                                                              Zurmo::t('Core', 'Start'), $updateDivId);
            }
            elseif ($mission->status == Mission::STATUS_TAKEN &&
                   $mission->takenByUser->isSame(Yii::app()->user->userModel))
            {
                return self::renderAjaxStatusActionChangeLink(Mission::STATUS_COMPLETED, $mission->id,
                                                              Zurmo::t('Core', 'Complete'), $updateDivId);
            }
            elseif ($mission->status == Mission::STATUS_COMPLETED &&
                   $mission->owner->isSame(Yii::app()->user->userModel))
            {
                $content  = self::renderAjaxStatusActionChangeLink(      Mission::STATUS_ACCEPTED, $mission->id,
                                                                         Zurmo::t('Core', 'Accept'), $updateDivId);
                $content .= ' ' . self::renderAjaxStatusActionChangeLink(Mission::STATUS_REJECTED, $mission->id,
                                                                         Zurmo::t('Core', 'Reject'), $updateDivId);
                return $content;
            }
            elseif ($mission->status == Mission::STATUS_REJECTED &&
                   $mission->takenByUser->isSame(Yii::app()->user->userModel))
            {
                return self::renderAjaxStatusActionChangeLink(Mission::STATUS_COMPLETED, $mission->id,
                                                              Zurmo::t('Core', 'Complete'), $updateDivId);
            }
        }

        /**
         * @param int $newStatus
         * @param int $missionId
         * @param string $label
         * @param string $updateDivId
         * @return string
         */
        protected static function renderAjaxStatusActionChangeLink($newStatus, $missionId, $label, $updateDivId)
        {
            assert('is_int($newStatus)');
            assert('is_int($missionId)');
            assert('is_string($label)');
            assert('is_string($updateDivId)');
            $url     =   Yii::app()->createUrl('missions/default/ajaxChangeStatus',
                                               array('status' => $newStatus, 'id' => $missionId));
            $aContent                = ZurmoHtml::wrapLink($label);
            return       ZurmoHtml::ajaxLink($aContent, $url,
                         array('type'       => 'GET',
                               'success'    => 'function(data){$("#' . $updateDivId . '").replaceWith(data)}'
                             ),
                         array('id'         => $newStatus . '-' . $updateDivId,
                               'class'      => 'mission-change-status-link attachLoading z-button ' .
                                               self::resolveLinkSpecificCssClassNameByNewStatus($newStatus),
                               'namespace'  => 'update',
                               'onclick'    => 'js:$(this).addClass("loading").addClass("loading-ajax-submit");
                                                        $(this).makeOrRemoveLoadingSpinner(true, "#" + $(this).attr("id"));'));
        }

        protected static function resolveLinkSpecificCssClassNameByNewStatus($status)
        {
            assert('is_integer($status)');
            if ($status == Mission::STATUS_TAKEN)
            {
                return 'action-take';
            }
            elseif ($status == Mission::STATUS_COMPLETED)
            {
                return 'action-complete';
            }
            elseif ($status == Mission::STATUS_ACCEPTED)
            {
                return 'action-accept';
            }
            elseif ($status == Mission::STATUS_REJECTED)
            {
                return 'action-reject';
            }
        }

        protected function renderLabel()
        {
            return Zurmo::t('ZurmoModule', 'Status');
        }

        public static function getDisplayName()
        {
            return Zurmo::t('ZurmoModule', 'Status');
        }

        /**
         * Get the attributeNames of attributes used in
         * the derived element.
         * @return array of model attributeNames used.
         */
        public static function getModelAttributeNames()
        {
            return array(
                'status',
            );
        }
    }
?>