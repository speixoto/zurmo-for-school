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

    Yii::import('ext.swiftmailer.SwiftMailer');

    /**
     * Class for Zurmo specific SwiftMailer functionality.
     */
    class ZurmoSwiftMailer extends SwiftMailer
    {
        /**
         * Stores send response log from server as email is sending.
         * @var array
         */
        protected $sendResponseLog          = array();

        /**
         * (non-PHPdoc)
         * @see SwiftMailer::smtpTransport()
         */
        public function smtpTransport($host = null, $port = null, $security = null)
        {
            return ZurmoSwiftSmtpTransport::newInstance($host, $port, $security);
        }

        /**
         * @return array of data.
         */
        public function getSendResponseLog()
        {
            return $this->sendResponseLog;
        }

        /**
         * Override to support adding sendResponseLog messages
         * (non-PHPdoc)
         * @see SwiftMailer::send()
         */
        public function send()
        {
            $transport = $this->loadTransport();
            $mailer    = Swift_Mailer::newInstance($transport);
            $message   = Swift_Message::newInstance($this->Subject);
            $message->setFrom($this->From);
            foreach ($this->toAddressesAndNames as $address => $name)
            {
                try
                {
                    $message->addTo($address, $name);
                }
                catch (Swift_RfcComplianceException $e)
                {
                    throw new OutboundEmailSendException($e->getMessage(), $e->getCode(), $e);
                }
            }

            if (!empty($this->ccAddressesAndNames))
            {
                foreach ($this->ccAddressesAndNames as $address => $name)
                {
                    try
                    {
                        $message->addCc($address, $name);
                    }
                    catch (Swift_RfcComplianceException $e)
                    {
                        throw new OutboundEmailSendException($e->getMessage(), $e->getCode(), $e);
                    }
                }
            }

            if (!empty($this->bccAddressesAndNames))
            {
                foreach ($this->bccAddressesAndNames as $address => $name)
                {
                    try
                    {
                        $message->addBcc($address, $name);
                    }
                    catch (Swift_RfcComplianceException $e)
                    {
                        throw new OutboundEmailSendException($e->getMessage(), $e->getCode(), $e);
                    }
                }
            }

            if (!empty($this->attachments))
            {
                foreach ($this->attachments as $attachment)
                {
                    $message->attach($attachment);
                }
            }

            if ($this->body)
            {
                $message->addPart($this->body, 'text/html');
            }
            if ($this->altBody)
            {
                $message->setBody($this->altBody);
            }
            try
            {
                $result                = $mailer->send($message);
                $this->sendResponseLog = $transport->getResponseLog();
            }
            catch (Swift_SwiftException $e)
            {
                $this->sendResponseLog = $transport->getResponseLog();
                throw new OutboundEmailSendException($e->getMessage(), $e->getCode(), $e);
            }
            $this->clearAddresses();
            return $result;
        }
    }
?>