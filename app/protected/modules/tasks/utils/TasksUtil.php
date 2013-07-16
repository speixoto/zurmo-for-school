<?php
    /*********************************************************************************
     * Zurmo is a customer relationship management program developed by
     * Zurmo, Inc. Copyright (C) 2013 Zurmo Inc.
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
     * "Copyright Zurmo Inc. 2013. All rights reserved".
     ********************************************************************************/

    /**
     * Helper class for working with tasks
     */
    class TasksUtil
    {
        /**
         * Given a Conversation and User, determine if the user is already a conversationParticipant.
         * @param Conversation $model
         * @param User $user
         * @return boolean
         */
        public static function isUserSubscribedForTask(Task $model, User $user)
        {
            if ($model->notificationSubscribers->count() > 0)
            {
                foreach ($model->notificationSubscribers as $subscriber)
                {
                    if ($subscriber->person->getClassId('Item') == $user->getClassId('Item'))
                    {
                        return true;
                    }
                }
            }
            return false;
        }

        /**
         * Get task subscriber data
         * @param Task $task
         * @return string
         */
        public static function getTaskSubscriberData(Task $task)
        {
            $content = null;
            $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem('User');
            foreach ($task->notificationSubscribers as $subscriber)
            {
                $user           = $subscriber->person->castDown(array($modelDerivationPathToItem));
                $userUrl        = Yii::app()->createUrl('/users/default/details', array('id' => $user->id));
                $stringContent  = ZurmoHtml::link($user->getAvatarImage(36), $userUrl);
                $content        .= '<p>' . $stringContent . '</p>';
            }

            return $content;
        }

        /**
         * Given a conversation and a user, mark that the user has read or not read the latest changes as a conversation
         * participant, or if the user is the owner, than as the owner.
         * @param Conversation $conversation
         * @param User $user
         * @param Boolean $hasReadLatest
         */
//        public static function markUserHasReadLatest(Conversation $conversation, User $user, $hasReadLatest = true)
//        {
//            assert('$conversation->id > 0');
//            assert('$user->id > 0');
//            assert('is_bool($hasReadLatest)');
//            $save = false;
//            if ($user->getClassId('Item') == $conversation->owner->getClassId('Item'))
//            {
//                if ($conversation->ownerHasReadLatest != $hasReadLatest)
//                {
//                    $conversation->ownerHasReadLatest = $hasReadLatest;
//                    $save                             = true;
//                }
//            }
//            else
//            {
//                foreach ($conversation->conversationParticipants as $position => $participant)
//                {
//                    if ($participant->person->getClassId('Item') == $user->getClassId('Item') && $participant->hasReadLatest != $hasReadLatest)
//                    {
//                        $conversation->conversationParticipants[$position]->hasReadLatest = $hasReadLatest;
//                        $save                                                             = true;
//                    }
//                }
//            }
//            if ($save)
//            {
//                $conversation->save();
//            }
//        }

//        public static function hasUserReadConversationLatest(Conversation $conversation, User $user)
//        {
//            assert('$conversation->id > 0');
//            assert('$user->id > 0');
//            if ($user->isSame($conversation->owner))
//            {
//                return $conversation->ownerHasReadLatest;
//            }
//            else
//            {
//                foreach ($conversation->conversationParticipants as $position => $participant)
//                {
//                    if ($participant->person->getClassId('Item') == $user->getClassId('Item'))
//                    {
//                        return $participant->hasReadLatest;
//                    }
//                }
//            }
//            return false;
//        }


    }
?>