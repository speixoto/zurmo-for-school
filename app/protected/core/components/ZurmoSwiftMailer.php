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

    Yii::import('ext.swiftmailer.SwiftMailer');

    /**
     * Class for Zurmo specific SwiftMailer functionality.
     */
    class ZurmoSwiftMailer extends SwiftMailer
    {
        /**
         * Stores additional headers for email messages
         * @var array
         */
        public $headers                  = array();

        /**
         * Stores additional parts for email message
         * @var array
         */
        public $parts = array();

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
            if (!empty($this->toAddressesAndNames))
            {
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

            if (!empty($this->headers))
            {
                $headersObject  = $message->getHeaders();
                foreach ($this->headers as $headerKey => $headerValue)
                {
                    $method = "addTextHeader";
                    if ($headerKey == "Return-Path")
                    {
                        // this is a special header type unlike text headers.
                        $method = "addPathHeader";
                    }
                    $headersObject->$method($headerKey, $headerValue);
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
            if (!empty($this->parts))
            {
                foreach ($this->parts as $part)
                {
                    $message->addPart($part[0], $part[1], $part[2]);
                }
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