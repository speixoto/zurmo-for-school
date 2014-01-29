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

    class CalendarUtil
    {
        /**
         * Make calendar items by model.
         * @param RedBeanModel $model
         * @param SavedCalendar $savedCalendar
         * @return CalendarItem
         */
        public static function makeCalendarItemByModel(RedBeanModel $model, SavedCalendar $savedCalendar)
        {
            $calendarItem   = new CalendarItem();
            $startAttribute = $savedCalendar->startAttributeName;
            $endAttribute   = $savedCalendar->endAttributeName;
            $calendarItem->setTitle($model->name);
            $calendarItem->setStartDateTime($model->$startAttribute);
            if($endAttribute != null)
            {
                $calendarItem->setEndDateTime($model->$endAttribute);
            }
            $calendarItem->setCalendarId($savedCalendar->id);
            $calendarItem->setModelClass(get_class($model));
            $calendarItem->setModelId($model->id);
            $calendarItem->setColor($savedCalendar->color);
            $calendarItem->setModuleClassName($savedCalendar->moduleClassName);
            return $calendarItem;
        }

        /**
         * Gets date range type.
         * @return string
         */
        public static function getDateRangeType()
        {
            return SavedCalendar::DATERANGE_TYPE_MONTH;
        }

        /**
         * Gets start date.
         * @param string $dateRangeType
         * @return string
         */
        public static function getStartDate($dateRangeType)
        {
            assert('is_string($dateRangeType)');
            if($dateRangeType == SavedCalendar::DATERANGE_TYPE_MONTH)
            {
                return DateTimeUtil::getFirstDayOfAMonthDate();
            }
            if($dateRangeType == SavedCalendar::DATERANGE_TYPE_WEEK)
            {
                return DateTimeUtil::getFirstDayOfAWeek();
            }
            if($dateRangeType == SavedCalendar::DATERANGE_TYPE_DAY)
            {
                return DateTimeUtil::getTodaysDate();
            }
        }

        /**
         * Gets end date.
         * @param string $dateRangeType
         * @return string
         */
        public static function getEndDate($dateRangeType)
        {
            assert('is_string($dateRangeType)');
            if($dateRangeType == SavedCalendar::DATERANGE_TYPE_MONTH)
            {
                $dateTime = new DateTime();
                $dateTime->modify('last day of this month');
                return Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                        $dateTime->getTimestamp());
            }
            if($dateRangeType == SavedCalendar::DATERANGE_TYPE_WEEK)
            {
                $dateTime       = new DateTime('last day of this week');
                return Yii::app()->dateFormatter->format(DatabaseCompatibilityUtil::getDateFormat(),
                        $dateTime->getTimestamp());
            }
            if($dateRangeType == SavedCalendar::DATERANGE_TYPE_DAY)
            {
                return DateTimeUtil::getTodaysDate();
            }
        }

        /**
         * Get saved calendars for user.
         * @param User $user
         * @return array
         */
        public static function getUserSavedCalendars(User $user)
        {
            $metadata = array();
            $metadata['clauses'] = array(
                1 => array(
                    'attributeName'        => 'createdByUser',
                    'relatedAttributeName' => 'id',
                    'operatorType'         => 'equals',
                    'value'                => $user->id,
                )
            );
            $metadata['structure'] = '1';
            $joinTablesAdapter   = new RedBeanModelJoinTablesQueryAdapter('SavedCalendar');
            $where  = RedBeanModelDataProvider::makeWhere('SavedCalendar', $metadata, $joinTablesAdapter);
            return SavedCalendar::getSubset($joinTablesAdapter, null, null, $where);
        }

        /**
         * Process user calendars and get data provider.
         * @param mixed $myCalendarIds
         * @return CalendarItemsDataProvider
         */
        public static function processUserCalendarsAndMakeDataProviderForCombinedView($myCalendarIds = null, $mySubscribedCalendarIds = null)
        {
            $savedCalendarSubscriptions = SavedCalendarSubscriptions::makeByUser(Yii::app()->user->userModel,
                                                                                 $myCalendarIds,
                                                                                 $mySubscribedCalendarIds);
            $dateRangeType              = CalendarUtil::getDateRangeType();
            $startDate                  = CalendarUtil::getStartDate($dateRangeType);
            $endDate                    = CalendarUtil::getEndDate($dateRangeType);
            $dataProvider               = CalendarItemsDataProviderFactory::getDataProviderByDateRangeType($savedCalendarSubscriptions,
                                                                                                $startDate, $endDate, $dateRangeType);
            return $dataProvider;
        }

        /**
         * Get full calendar items.
         * @param CalendarItemsDataProvider $dataProvider
         * @return array
         */
        public static function getFullCalendarItems(CalendarItemsDataProvider $dataProvider)
        {
            $calendarItems = $dataProvider->getData();
            $fullCalendarItems = array();
            for($k = 0; $k < count($calendarItems); $k++)
            {
                $fullCalendarItem = array();
                $calItem = $calendarItems[$k];
                $fullCalendarItem['title'] = $calItem->getTitle();
                $fullCalendarItem['start'] = self::getFullCalendarFormattedDateTimeElement($calItem->getStartDateTime());
                if($calItem->getEndDateTime() != null)
                {
                    $fullCalendarItem['end'] = self::getFullCalendarFormattedDateTimeElement($calItem->getEndDateTime());
                }
                $fullCalendarItem['color'] = $calItem->getColor();
                $fullCalendarItems[] = $fullCalendarItem;
            }
            return $fullCalendarItems;
        }

        /**
         * Gets full calendar formatted date time.
         * @param string $dateTime
         * @return string formatted in datetime format required for full calendar widget
         */
        public static function getFullCalendarFormattedDateTimeElement($dateTime)
        {
            $dateTimeObject = new DateTime($dateTime);
            return Yii::app()->dateFormatter->format('yyyy-MM-dd HH:mm',
                        $dateTimeObject->getTimestamp());
        }

        /**
         * Gets used color by user.
         *
         * @param User $user
         * @return array
         */
        public static function getUsedCalendarColorsByUser(User $user, $modelClassName, $attributeName)
        {
            $quote                     = DatabaseCompatibilityUtil::getQuote();
            $selectDistinct            = false;
            $joinTablesAdapter         = new RedBeanModelJoinTablesQueryAdapter($modelClassName);
            $selectQueryAdapter        = new RedBeanModelSelectQueryAdapter($selectDistinct);
            $selectQueryAdapter->addClause($modelClassName::getTableName(), 'color');
            $metadata                  = array();
            $metadata['clauses']       = array(
                                                    1 => array(
                                                        'attributeName'        => $attributeName,
                                                        'relatedAttributeName' => 'id',
                                                        'operatorType'         => 'equals',
                                                        'value'                => $user->id,
                                                    )
                                                );
            $metadata['structure'] = '1';
            $where   = RedBeanModelDataProvider::makeWhere($modelClassName, $metadata, $joinTablesAdapter);
            $sql     = SQLQueryUtil::makeQuery($modelClassName::getTableName(), $selectQueryAdapter, $joinTablesAdapter, null, null, $where);
            $records = ZurmoRedBean::getAll($sql);
            $colors  = array();
            foreach($records as $record)
            {
                if($record['color'] != null && $record['color'] != '')
                {
                    $colors[] = $record['color'];
                }
            }
            return $colors;
        }

        /**
         * @return string
         */
        public static function getModalContainerId()
        {
            return ModalContainerView::ID;
        }

        /**
         * @return array
         */
        public static function resolveAjaxOptionsForModalView()
        {
            $title = Zurmo::t('Calendarsmodule', 'Shared Calendars');
            return   ModalView::getAjaxOptionsForModalLink($title, self::getModalContainerId(), 'auto', 600,
                     'center top+25', $class = "''");
        }

        /**
         * Get task modal script
         * @param string $url
         * @param string $selector
         * @return string
         */
        public static function registerSharedCalendarModalScript($url, $selector)
        {
            assert('is_string($url)');
            assert('is_string($selector)');
            $modalId     = CalendarUtil::getModalContainerId();
            $ajaxOptions = CalendarUtil::resolveAjaxOptionsForModalView();
            $ajaxOptions['beforeSend'] = new CJavaScriptExpression($ajaxOptions['beforeSend']);
            return "$(document).on('click', '{$selector}', function()
                         {
                            $.ajax(
                            {
                                'type' : 'GET',
                                'url'  : '{$url}',
                                'beforeSend' : {$ajaxOptions['beforeSend']},
                                'update'     : '{$ajaxOptions['update']}',
                                'success': function(html){jQuery('#{$modalId}').html(html)}
                            });
                          }
                        );";
        }

        /**
         * Get the items by user
         * @param User $user
         * @return integer
         */
        public static function getUserSubscribedCalendars(User $user)
        {
            $searchAttributeData = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'user',
                    'relatedAttributeName'      => 'id',
                    'operatorType'              => 'equals',
                    'value'                     => intval($user->id),
                )
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter('SavedCalendarSubscription');
            $where  = RedBeanModelDataProvider::makeWhere('SavedCalendarSubscription', $searchAttributeData, $joinTablesAdapter);
            $models = SavedCalendarSubscription::getSubset($joinTablesAdapter, null, null, $where, null);
            return $models;
        }

        /**
         * Make calendar items list.
         * @param array $data
         * @param string $field
         * @param string $itemClass
         * @param string $type
         * @return string
         */
        public static function makeCalendarItemsList($data, $field, $itemClass, $type)
        {
            $itemsContent = null;
            foreach($data as $calendarArray)
            {
                $isChecked = false;
                if($calendarArray[1] === true)
                {
                    $isChecked = true;
                }
                $input          = ZurmoHtml::checkBox($field,
                                                      $isChecked,
                                                      array('value' => $calendarArray[0]->id,
                                                            'class' => $itemClass));
                $color          = ZurmoHtml::tag('span', array('class' => 'cal-color', 'style' => 'background:' .
                                                                                                        $calendarArray[0]->color), '');
                if($type == 'saved')
                {
                    $label          = $calendarArray[0]->name;
                    $options        = self::getSavedCalendarOptions($calendarArray[0]->id);
                    $subscriptionData = null;
                }
                else
                {
                    $savedCalendar    = $calendarArray[0]->savedcalendar;
                    $label            = $savedCalendar->name;
                    $options          = self::getSharedCalendarOptions($calendarArray[0]->id);
                    $subscriptionData = CalendarUtil::getCalendarSubscriberData($calendarArray[0]->savedcalendar);
                }
                $label = ZurmoHtml::tag('strong', array('class' => 'cal-name'), $label);
                $itemsContent   .= ZurmoHtml::tag('li', array(), $input . $color . $label . $subscriptionData . $options);
            }
            return ZurmoHtml::tag('ul', array(), $itemsContent);
        }

        /**
         * Get shared calendar options.
         * @param int $savedCalendarSubscriptionId
         * @return string
         */
        public static function getSharedCalendarOptions($savedCalendarSubscriptionId)
        {
            //$elementContent = null;
            $elementContent = ZurmoHtml::tag('li', array(),
                                            ZurmoHtml::link('Unsubscribe', '#',
                                                    array('data-value'  => $savedCalendarSubscriptionId,
                                                          'class'       => 'shared-cal-unsubscribe')));
//            $editElement    = new EditLinkActionElement($this->controllerId, $this->moduleId, $calendarId, array());
//            $elementContent .= ZurmoHtml::tag('li', array(), $editElement->render());
//            $deleteElement  = new CalendarDeleteLinkActionElement($this->controllerId, $this->moduleId, $calendarId, array());
//            $elementContent .= ZurmoHtml::tag('li', array(), $deleteElement->render());
            $elementContent = ZurmoHtml::tag('ul', array(), $elementContent);
            $content        = ZurmoHtml::tag('li', array('class' => 'parent last'),
                                                   ZurmoHtml::link('<span></span>', 'javascript:void(0);') . $elementContent);
            $content        = ZurmoHtml::tag('ul', array('class' => 'options-menu edit-row-menu nav'), $content);
            return $content;
        }

        public static function registerCalendarUnsubscriptionScript($startDate, $endDate)
        {
            $url    = Yii::app()->createUrl('/calendars/default/unsubscribe');
            $eventsUrl = Yii::app()->createUrl('calendars/default/getEvents');
            $script = "$(document).on('click', '.shared-cal-unsubscribe', function(){
                            $.ajax(
                            {
                                type : 'GET',
                                url  : '{$url}',
                                data : {'id' : $(this).data('value')},
                                beforeSend: function(xhr)
                                            {
                                                $('#shared-calendars-list').html('');
                                                $(this).makeLargeLoadingSpinner(true, '#shared-calendars-list');
                                            },
                                success : function(data)
                                          {
                                                $('#shared-calendars-list').html(data);
                                                $(this).makeLargeLoadingSpinner(false, '#shared-calendars-list');
                                          }
                            }
                            );
                            refreshCalendarEvents('{$eventsUrl}', '{$startDate}', '{$endDate}');
                      })";
            $cs = Yii::app()->getClientScript();
            if($cs->isScriptRegistered('calunsubscribescript', ClientScript::POS_END) === false)
            {
                $cs->registerScript('calunsubscribescript', $script, ClientScript::POS_END);
            }
        }

        /**
         * Get saved calendar options.
         * @param int $calendarId
         * @return string
         */
        public static function getSavedCalendarOptions($calendarId)
        {
            $elementContent = null;
            $controllerId   = Yii::app()->controller->getId();
            $moduleId       = Yii::app()->controller->getModule()->getId();
            $editElement    = new CalendarEditLinkActionElement($controllerId, $moduleId, $calendarId, array());
            $elementContent .= ZurmoHtml::tag('li', array(), $editElement->render());
            $deleteElement  = new CalendarDeleteLinkActionElement($controllerId, $moduleId, $calendarId, array());
            $elementContent .= ZurmoHtml::tag('li', array(), $deleteElement->render());
            $elementContent = ZurmoHtml::tag('ul', array(), $elementContent);
            $content        = ZurmoHtml::tag('li', array('class' => 'parent last'),
                                                   ZurmoHtml::link('<span></span>', 'javascript:void(0);') . $elementContent);
            $content        = ZurmoHtml::tag('ul', array('class' => 'options-menu edit-row-menu nav'), $content);
            return $content;
        }

        /**
         * Get calendar items data provider.
         * @return CalendarItemsDataProvider
         */
        public static function getCalendarItemsDataProvider()
        {
            $mySavedCalendarIds         = ZurmoConfigurationUtil::getByUserAndModuleName(Yii::app()->user->userModel,
                                                                                        'CalendarsModule', 'myCalendarSelections');
            $mySubscribedCalendarIds    = ZurmoConfigurationUtil::getByUserAndModuleName(Yii::app()->user->userModel,
                                                                                        'CalendarsModule', 'mySubscribedCalendarSelections');
            return CalendarUtil::processUserCalendarsAndMakeDataProviderForCombinedView($mySavedCalendarIds, $mySubscribedCalendarIds);
        }

        /**
         * Get users subscribed for calendar.
         * @param SavedCalendar $subscribedCalendar
         * @return array
         */
        public static function getUsersSubscribedForCalendar(SavedCalendar $subscribedCalendar)
        {
            $searchAttributeData = array();
            $users               = array();
            $searchAttributeData['clauses'] = array(
                1 => array(
                    'attributeName'             => 'savedcalendar',
                    'relatedAttributeName'      => 'id',
                    'operatorType'              => 'equals',
                    'value'                     => intval($subscribedCalendar->id),
                )
            );
            $searchAttributeData['structure'] = '1';
            $joinTablesAdapter                = new RedBeanModelJoinTablesQueryAdapter('SavedCalendarSubscription');
            $where  = RedBeanModelDataProvider::makeWhere('SavedCalendarSubscription', $searchAttributeData, $joinTablesAdapter);
            $models = SavedCalendarSubscription::getSubset($joinTablesAdapter, null, null, $where, null);
            foreach($models as $model)
            {
                $users[] = $model->user;
            }
            return $users;
        }

        /**
         * Get shared calendar subscriber data
         * @param Task $task
         * @return string
         */
        public static function getCalendarSubscriberData(SavedCalendar $subscribedCalendar)
        {
            $users    = CalendarUtil::getUsersSubscribedForCalendar($subscribedCalendar);
            $content  = null;
            $alreadySubscribedUsers = array();
            foreach ($users as $user)
            {
                //Take care of duplicates if any
                if (!in_array($user->id, $alreadySubscribedUsers))
                {
                    $content .= TasksUtil::renderSubscriberImageAndLinkContent($user, 25);
                    $alreadySubscribedUsers[] = $user->id;
                }
            }

            return $content;
        }

        /**
         * Register script whick would be invoked on click of any calendar item in my calendars or shared calendars
         */
        public static function registerSelectCalendarScript($startDate, $endDate)
        {
            //refer to http://stackoverflow.com/questions/9801095/jquery-fullcalendar-send-custom-parameter-and-refresh-calendar-with-json
            $url    = Yii::app()->createUrl('calendars/default/getEvents');
            $script = "$(document).on('click', '.mycalendar,.sharedcalendar',
                                                      function(){
                                                        refreshCalendarEvents('{$url}', '{$startDate}', '{$endDate}');
                                                        });";
            $cs = Yii::app()->getClientScript();
            if($cs->isScriptRegistered('mycalendarselectscript', ClientScript::POS_END) === false)
            {
                $cs->registerScript('mycalendarselectscript', $script);
            }
        }
    }
?>