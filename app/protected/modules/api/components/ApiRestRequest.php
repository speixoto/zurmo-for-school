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

    /*
     * ApiResponse
     */
    class ApiRestRequest
    {
        /**
         * Parse params from request.
         * @return array
         */
        public function getParamsFromRequest()
        {
            $requestMethod = strtolower($_SERVER['REQUEST_METHOD']);

            switch ($requestMethod)
            {
                case 'get':
                    $params = $_GET;
                    break;
                case 'post':
                    $params = $_POST;
                    $xml = @simplexml_load_string($params['data']);
                    if (false !== $xml && null !== $xml)
                    {
                        $params = XML2Array::createArray($params['data']);
                    }
                    break;
                case 'put':
                    parse_str(file_get_contents('php://input'), $params);
                    $xml = @simplexml_load_string($params['data']);
                    if (false !== $xml && null !== $xml)
                    {
                        $params = XML2Array::createArray($params['data']);
                    }
                    $params['id'] = $_GET['id'];
                    break;
                case 'delete':
                    $params['id'] = $_GET['id'];
                    break;
            }
            return $params;
        }

        /**
         * Get sessionId from HTTP headers
         */
        public function getSessionId()
        {
            if (isset($_SERVER['HTTP_ZURMO_SESSION_ID']) && $_SERVER['HTTP_ZURMO_SESSION_ID'] != '')
            {
                return $_SERVER['HTTP_ZURMO_SESSION_ID'];
            }
            if (function_exists('getallheaders'))
            {
                $httpHeaders = getallheaders();
                if (isset($httpHeaders['ZURMO_SESSION_ID']))
                {
                    return $httpHeaders['ZURMO_SESSION_ID'];
                }
            }
            return false;
        }

        /**
         * Get token from HTTP headers
         */
        public function getSessionToken()
        {
            if (isset($_SERVER['HTTP_ZURMO_TOKEN']) && $_SERVER['HTTP_ZURMO_TOKEN'] != '')
            {
                return $_SERVER['HTTP_ZURMO_TOKEN'];
            }
            if (function_exists('getallheaders'))
            {
                $httpHeaders = getallheaders();
                if (isset($httpHeaders['ZURMO_TOKEN']))
                {
                    return $httpHeaders['ZURMO_TOKEN'];
                }
            }
            return false;
        }

        /**
         * Get username from HTTP headers
         */
        public function getUsername()
        {
            if (isset($_SERVER['HTTP_ZURMO_AUTH_USERNAME']) && $_SERVER['HTTP_ZURMO_AUTH_USERNAME'] != '')
            {
                return $_SERVER['HTTP_ZURMO_AUTH_USERNAME'];
            }
            if (function_exists('getallheaders'))
            {
                $httpHeaders = getallheaders();
                if (isset($httpHeaders['ZURMO_AUTH_USERNAME']))
                {
                    return $httpHeaders['ZURMO_AUTH_USERNAME'];
                }
            }
            return false;
        }

        /**
         * Get password from HTTP headers
         */
        public function getPassword()
        {
            if (isset($_SERVER['HTTP_ZURMO_AUTH_PASSWORD']) && $_SERVER['HTTP_ZURMO_AUTH_PASSWORD'] != '')
            {
                return $_SERVER['HTTP_ZURMO_AUTH_PASSWORD'];
            }
            if (function_exists('getallheaders'))
            {
                $httpHeaders = getallheaders();
                if (isset($httpHeaders['ZURMO_AUTH_PASSWORD']))
                {
                    return $httpHeaders['ZURMO_AUTH_PASSWORD'];
                }
            }
            return false;
        }

        /**
         * Get language from HTTP headers
         */
        public function getLanguage()
        {
            if (isset($_SERVER['HTTP_ZURMO_LANG']) && $_SERVER['HTTP_ZURMO_LANG'] != '')
            {
                return $_SERVER['HTTP_ZURMO_LANG'];
            }
            if (function_exists('getallheaders'))
            {
                $httpHeaders = getallheaders();
                if (isset($httpHeaders['ZURMO_LANG']))
                {
                    return $httpHeaders['ZURMO_LANG'];
                }
            }
            return false;
        }

        public function isSessionTokenRequired()
        {
            return true;
        }

        /**
         * Get requested response format (json or xml)
         */
        public function getResponseFormat()
        {
            if (@strpos($_SERVER['HTTP_ACCEPT'], ApiRequest::JSON_FORMAT))
            {
                return  ApiRequest::JSON_FORMAT;
            }
            elseif (@strpos($_SERVER['HTTP_ACCEPT'], ApiRequest::XML_FORMAT))
            {
                return ApiRequest::XML_FORMAT;
            }
            else
            {
                return false;
            }
        }

        public function getResponseClassName()
        {
            if (@strpos($_SERVER['HTTP_ACCEPT'], ApiRequest::JSON_FORMAT))
            {
                return 'ApiJsonResponse';
            }
            elseif (@strpos($_SERVER['HTTP_ACCEPT'], ApiRequest::XML_FORMAT))
            {
                return 'ApiXmlResponse';
            }
            else
            {
                $message = Yii::t('Default', 'Invalid API request type.');
                throw new ApiException($message);
            }
        }

        /**
         * Get request type from HTTP headers
         */
        public function getRequestType()
        {
            if (isset($_SERVER['HTTP_ZURMO_API_REQUEST_TYPE']) && $_SERVER['HTTP_ZURMO_API_REQUEST_TYPE'] != '')
            {
                if (strtolower($_SERVER['HTTP_ZURMO_API_REQUEST_TYPE']) == 'rest')
                {
                    return ApiRequest::REST;
                }
            }
            if (function_exists('getallheaders'))
            {
                $httpHeaders = getallheaders();
                if (isset($httpHeaders['ZURMO_API_REQUEST_TYPE']))
                {
                    return $httpHeaders['ZURMO_API_REQUEST_TYPE'];
                }
            }
            return false;
        }

        /**
         * Parse params from request.
         */
        public function parseParams()
        {
            if ($this->getRequestType() == ApiRequest::REST)
            {
                $params = ApiRestRequest::getParamsFromRequest();
            }
            else
            {
                echo Yii::t('Default', "Invalid request");
                Yii::app()->end();
            }
            return $params;
        }
    }
?>