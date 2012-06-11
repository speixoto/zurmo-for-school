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
     * Helper class for conversation participant related logic.
     */
    class ConversationParticipantsUtil
    {
        public static function isUserAParticipant(User $user)
        {
            if ($model->conversationParticipants->count() > 0)
            {
                foreach($model->conversationParticipants as $participant)
                {
                    if($participant->getClassId('Item') == $user->getClassId('Item'))
                    {
                        return true;
                    }
                }
            }
            return false;
        }

        /**
         * Based on the post data, resolve the conversation participants. While this is being resolved also
         * resolve the correct read/write permissions.
         * @param object $model - Conversation Model
         * @param array $postData
         * @param object $explicitReadWriteModelPermissions - ExplicitReadWriteModelPermissions model
         */
        public static function resolveConversationHasManyParticipantsFromPost(
                                    Conversation $model, $postData, $explicitReadWriteModelPermissions)
        {
            assert('$explicitReadWriteModelPermissions instanceof ExplicitReadWriteModelPermissions');
            if (isset($postData['itemIds']))
            {
                $newParticipantsIndexedByItemId = array();
                foreach ($postData['itemIds'] as $itemId)
                {
                    $newParticipantsIndexedByItemId[$itemId] = static::castDownItem(Item::getById((int)$itemId));
                }
                if ($model->conversationParticipants->count() > 0)
                {
                    $participantsToRemove = array();
                    foreach ($model->conversationParticipants as $index => $existingParticipantModel)
                    {
                        if (!isset($newParticipantsIndexedByItemId[$existingParticipantModel->getClassId('Item')]))
                        {
                            $participantsToRemove[] = $existingParticipantModel;
                        }
                        else
                        {
                            unset($newParticipantsIndexedByItemId[$existingParticipantModel->getClassId('Item')]);
                        }
                    }
                    foreach ($participantsToRemove as $participantModelToRemove)
                    {
                        $model->conversationParticipants->remove($participantModelToRemove);
                        if($participantModel instanceof Permitable)
                        {
                            $explicitReadWriteModelPermissions->addReadWritePermitableToRemove($participantModel);
                        }
                    }
                }
                //Now add missing participants
                foreach ($newParticipantsIndexedByItemId as $participantModel)
                {
                    $model->conversationParticipants->add($participantModel);
                    if($participantModel instanceof Permitable)
                    {
                        $explicitReadWriteModelPermissions->addReadWritePermitable($participantModel);
                    }
                }
            }
            else
            {
                //remove all participants
                $model->conversationParticipants->removeAll();
                $explicitReadWriteModelPermissions->removeAllReadWritePermitables();
            }
        }

        protected static function castDownItem(Item $item)
        {
            foreach(array('Contact', 'User') as $modelClassName)
            {
                try
                {
                    $modelDerivationPathToItem = RuntimeUtil::getModelDerivationPathToItem($modelClassName);
                    return $item->castDown(array($modelDerivationPathToItem));
                }
                catch (NotFoundException $e)
                {
                }
            }
            throw new NotSupportedException();
        }
    }
?>