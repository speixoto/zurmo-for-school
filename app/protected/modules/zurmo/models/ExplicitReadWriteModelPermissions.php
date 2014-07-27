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
     * Class helps interaction between the user interface, forms, and controllers that are involved in setting
     * the explicit permissions on a model.  This class merges permission concepts together to form easier to
     * understand structures for the user interface.  Currently this only supports either readOnly or readWrite
     * permission combinations against a model for a user or group.
     * @see ExplicitReadWriteModelPermissionsElement
     * @see ExplicitReadWriteModelPermissionsUtil
     */
    class ExplicitReadWriteModelPermissions
    {
        /**
         * Array of permitable objects that will be explicity set to read only.
         * @var array
         */
        protected $readOnlyPermitables  = array();

        /**
         * Array of permitable objects that will be explicity set to read and write.
         * @var array
         */
        protected $readWritePermitables = array();

        /**
         * Array of permitable objects that are explicity set to read only that need to be
         * removed from a securable item.
         * @var array
         */
        protected $readOnlyPermitablesToRemove  = array();

        /**
         * Array of permitable objects that are explicity set to read and write that need to
         * be removed from a securable item.
         * @var array
         */
        protected $readWritePermitablesToRemove = array();

        /**
         * Add a permitable object to the read only array.
         * @param object $permitable
         * @throws NotSupportedException
         */
        public function addReadOnlyPermitable(Permitable $permitable)
        {
            assert('$permitable instanceof Permitable');
            $this->resolveIsReadOnlySupported();
            $key = $this->resolvePermitableKey($permitable);
            if (!isset($this->readOnlyPermitables[$key]))
            {
                $this->readOnlyPermitables[$key] = $permitable;
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * Add a permitable object to the read write array.
         * @param object $permitable
         * @throws NotSupportedException
         */
        public function addReadWritePermitable(Permitable $permitable)
        {
            assert('$permitable instanceof Permitable');
            $key = $this->resolvePermitableKey($permitable);
            if (!isset($this->readWritePermitables[$key]))
            {
                $this->readWritePermitables[$key] = $permitable;
            }
            else
            {
                throw new NotSupportedException();
            }
        }

            /**
         * Add a permitable object that needs to be removed from the securable item.
         * @param object $permitable
         * @throws NotSupportedException
         */
        public function addReadOnlyPermitableToRemove(Permitable $permitable)
        {
            assert('$permitable instanceof Permitable');
            $this->resolveIsReadOnlySupported();
            $key = $this->resolvePermitableKey($permitable);
            if (!isset($this->readOnlyPermitablesToRemove[$key]))
            {
                $this->readOnlyPermitablesToRemove[$key] = $permitable;
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        /**
         * Add a permitable object that needs to be removed from the securable item.
         * @param object $permitable
         * @throws NotSupportedException
         */
        public function addReadWritePermitableToRemove(Permitable $permitable)
        {
            assert('$permitable instanceof Permitable');
            $key = $this->resolvePermitableKey($permitable);
            if (!isset($this->readWritePermitablesToRemove[$key]))
            {
                $this->readWritePermitablesToRemove[$key] = $permitable;
            }
            else
            {
                throw new NotSupportedException();
            }
        }

        public function removeAllReadWritePermitables()
        {
            foreach ($this->readWritePermitables as $permitable)
            {
                $key = $this->resolvePermitableKey($permitable);
                if (!isset($this->readWritePermitablesToRemove[$key]))
                {
                    $this->readWritePermitablesToRemove[$key] = $permitable;
                }
            }
        }

        /**
         * @return integer count of read only permitables
         */
        public function getReadOnlyPermitablesCount()
        {
            return count($this->readOnlyPermitables);
        }

        /**
         * @return integer count of read/write permitables
         */
        public function getReadWritePermitablesCount()
        {
            return count($this->readWritePermitables);
        }

        /**
         * @return integer count of read only permitables to remove from a securable item.
         */
        public function getReadOnlyPermitablesToRemoveCount()
        {
            return count($this->readOnlyPermitablesToRemove);
        }

            /**
         * @return integer count of read/write permitables to remove from a securable item.
         */
        public function getReadWritePermitablesToRemoveCount()
        {
            return count($this->readWritePermitablesToRemove);
        }

        /**
         * @return array of read only permitables
         */
        public function getReadOnlyPermitables()
        {
            return $this->readOnlyPermitables;
        }

        /**
         * @return array of read/write permitables
         */
        public function getReadWritePermitables()
        {
            return $this->readWritePermitables;
        }

        /**
         * @return array of read only permitables to remove from a securable item.
         */
        public function getReadOnlyPermitablesToRemove()
        {
            return $this->readOnlyPermitablesToRemove;
        }

        /**
         * @return array of read/write permitables to remove to remove from a securable item.
         */
        public function getReadWritePermitablesToRemove()
        {
            return $this->readWritePermitablesToRemove;
        }

        /**
         * Given a permitable, is that permitable in the read only data or the read write data?
         * @param Permitable $permitable
         * @return boolean true if it is in one of the data arrays.
         */
        public function isReadOrReadWritePermitable(Permitable $permitable)
        {
            assert('$permitable instanceof Permitable');
            $key = $this->resolvePermitableKey($permitable);
            if (isset($this->readWritePermitables[$key]) ||
                isset($this->readOnlyPermitables[$key]))
            {
                return true;
            }
            return false;
        }

        /**
         * Returns the related id from permitable models. This is unique for every Permitable child.
         * Public for tests
         * @param Permitable $permitable
         * @return int
         */
        public function resolvePermitableKey(Permitable $permitable)
        {
            return $permitable->getClassId('Permitable');
        }

        /**
         * If the read munge is also used for the write munge, then use of read only is not supported.
         * Change the processReadMungeAsWriteMunge param to false in order to properly use readOnly.
         * In the future the performance improvement from processReadMungeAsWriteMunge
         * will be refactored to fully support readOnly.
         * @return NotSupportedException
         */
        protected function resolveIsReadOnlySupported()
        {
            if ((bool)Yii::app()->params['processReadMungeAsWriteMunge'])
            {
                return new NotSupportedException();
            }
        }
    }
?>