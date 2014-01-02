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

    class AuditEvent extends RedBeanModel
    {
        public static $isTableOptimized = false;

        public static function getSinceTimestamp($timestamp)
        {
            assert('is_int($timestamp)');
            return getSinceDateTime(self::convertTimestampToDbFormatDateTime($timestamp));
        }

        public static function getSinceDate($date)
        {
            assert('DateTimeUtil::isValidDbFormattedDate($date)');
            return self::makeModels($beans = ZurmoRedBean::find('auditevent', "datetime >= '$date 00-00-00'"));
        }

        public static function getSinceDateTime($dateTime)
        {
            assert('DateTimeUtil::isValidDbFormattedDateTime($dateTime)');
            return self::makeModels($beans = ZurmoRedBean::find('auditevent', "datetime >= '$dateTime'"));
        }

        public static function getTailEvents($count)
        {
            assert('is_int($count)');
            $sql = "select id
                    from
                        (select   id
                         from     auditevent
                         order by id desc
                         limit    $count) as temp
                    order by id";
            $ids   = ZurmoRedBean::getCol($sql);
            $beans = ZurmoRedBean::batch ('auditevent', $ids);
            return self::makeModels($beans, __CLASS__);
        }

        public static function getTailDistinctEventsByEventName($eventName, User $user, $count)
        {
            assert('is_string($eventName)');
            assert('is_int($count)');
            $sql = "select id
                    from ( select id, modelclassname, modelid, datetime from auditevent where _user_id = {$user->id}
                    AND eventname = '{$eventName}' order by id desc ) auditevent
                    group by concat(modelclassname, modelid) order by datetime desc limit $count";
            $ids   = ZurmoRedBean::getCol($sql);
            $beans = ZurmoRedBean::batch ('auditevent', $ids);
            return self::makeModels($beans, __CLASS__);
        }

        public static function logAuditEvent($moduleName, $eventName, $data = null, RedBeanModel $model = null, User $user = null)
        {
            assert('is_string($moduleName) && $moduleName != ""');
            assert('is_string($eventName)  && $eventName  != ""');
            if ($user === null)
            {
                $user = Yii::app()->user->userModel;
                if (!$user instanceof User)
                {
                    throw new NoCurrentUserSecurityException();
                }
            }
            if ($eventName == "Item Viewed")
            {
                AuditEventsRecentlyViewedUtil::
                        resolveNewRecentlyViewedModel($data[1],
                                                      $model,
                                                      AuditEventsRecentlyViewedUtil::RECENTLY_VIEWED_COUNT + 1);
            }
            if ($eventName == "Item Deleted")
            {
                $modelClassName = get_class($model);
                AuditEventsRecentlyViewedUtil::
                        deleteModelFromRecentlyViewed($modelClassName::getModuleClassName(),
                                                      $model);
            }
            if (!AuditEvent::$isTableOptimized && !AUDITING_OPTIMIZED)
            {
                $auditEvent = new AuditEvent();
                $auditEvent->dateTime       = DateTimeUtil::convertTimestampToDbFormatDateTime(time());
                $auditEvent->moduleName     = $moduleName;
                $auditEvent->eventName      = $eventName;
                $auditEvent->user           = $user;
                $auditEvent->modelClassName = $model !== null ? get_class($model) : null;
                $auditEvent->modelId        = $model !== null ? $model->id        : null;
                $auditEvent->serializedData = serialize($data);
                $saved = $auditEvent->save();
                AuditEvent::$isTableOptimized = true;
            }
            else
            {
                $sql = "insert into auditevent (datetime,
                                                modulename,
                                                eventname,
                                                _user_id,
                                                modelclassname,
                                                modelid,
                                                serializeddata)
                        values ('" . DateTimeUtil::convertTimestampToDbFormatDateTime(time()) . "',
                                '$moduleName',
                                '$eventName',
                                {$user->id}, " .
                                ($model !== null ? "'" . get_class($model) . "', " : 'null, ') .
                                ($model !== null ? "{$model->id}, "                 : 'null, ') .
                                ":data)";
                ZurmoRedBean::exec($sql, array('data' => serialize($data))) !== null;
                $saved = true;
            }
            return $saved;
        }

        public function __toString()
        {
            $modelClassName = $this->modelClassName;
            $s  = DateTimeUtil::convertDbFormattedDateTimeToLocaleFormattedDisplay($this->dateTime) . ', ';
            $s .= $this->user . ', ';
            $s .= $this->eventName;
            if ($this->modelClassName !== null)
            {
                assert('is_string($this->modelClassName) && $this->modelClassName != ""');
                assert('is_numeric($this->modelId)');
                $s .= ', ' . $modelClassName::getModelLabelByTypeAndLanguage('Singular') . '(' . $this->modelId . ')';
            }
            return $s;
        }

        protected static function translatedAttributeLabels($language)
        {
            return array_merge(parent::translatedAttributeLabels($language),
                array(
                    'auditEvent' => Zurmo::t('ZurmoModule', 'Audit Event', array(), null, $language)
                )
            );
        }

        public static function getDefaultMetadata()
        {
            $metadata = parent::getDefaultMetadata();
            $metadata[__CLASS__] = array(
                'members' => array(
                    'dateTime',
                    'eventName',
                    'moduleName',
                    'modelClassName',
                    'modelId',
                    'serializedData',
                ),
                'relations' => array(
                    'user' => array(static::HAS_ONE,  'User'),
                ),
                'rules' => array(
                    array('dateTime',       'required'),
                    array('dateTime',       'type', 'type' => 'datetime'),
                    array('eventName',      'required'),
                    array('eventName',      'type',   'type' => 'string'),
                    array('eventName',      'length', 'min'  => 1, 'max' => 64),
                    array('moduleName',     'required'),
                    array('moduleName',     'type',   'type' => 'string'),
                    array('moduleName',     'length', 'min'  => 1, 'max' => 64),
                    array('modelClassName', 'type', 'type' => 'string'),
                    array('modelClassName', 'length', 'min'  => 1, 'max' => 64),
                    array('modelId',        'type', 'type' => 'integer'),
                    array('serializedData', 'required'),
                    array('serializedData', 'type', 'type' => 'string'),
                )
            );
            return $metadata;
        }

        public static function deleteAllByModel(RedBeanModel $model)
        {
            if ($model instanceof Item)
            {
                $searchAttributeData = array();
                $searchAttributeData['clauses'] = array(
                    1 => array(
                        'attributeName'        => 'modelClassName',
                        'operatorType'         => 'equals',
                        'value'                => get_class($model),
                    ),
                    2 => array(
                        'attributeName'        => 'modelId',
                        'operatorType'         => 'equals',
                        'value'                => $model->id,
                    ),
                );
                $searchAttributeData['structure'] = '1 and 2';
                $joinTablesAdapter = new RedBeanModelJoinTablesQueryAdapter('AuditEvent');
                $where             = RedBeanModelDataProvider::makeWhere('AuditEvent', $searchAttributeData, $joinTablesAdapter);
                $auditEvents       = self::getSubset($joinTablesAdapter, null, null, $where, null);
                foreach ($auditEvents as $event)
                {
                    $event->delete();
                }
            }
        }
    }
?>