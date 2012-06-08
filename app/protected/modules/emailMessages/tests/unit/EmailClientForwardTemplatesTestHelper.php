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

    class EmailClientForwardTemplatesTestHelper
    {
        public static $subjectPrefixes = array(
            'Gmail' => array('Fwd:'),
            'Outlook' => array ('FW:'),
            'OutlookExpress' => array ('FW:'),
            'ThunderBird' => array('Fwd:'),
            'Yahoo' => array ('Fw:')
        );

        public static $bodyPrefixes = array(
            'Gmail' => array(<<<EOD
---------- Forwarded message ----------
From: FROM_NAME <FROM_EMAIL>
Date: Fri, Jun 8, 2012 at 10:16 AM
Subject: Email from John
To: Steve <steve@example.com>
EOD
            ),
            'Outlook' => array(<<<EOD
-----Original Message-----
From: FROM_NAME [mailto:FROM_EMAIL]
Sent: Thursday, May 03, 2012 8:52 AM
To: 'Steve'
Subject: Email from John
EOD
            ),
            'OutlookExpress' => array(<<<EOD
-----Original Message-----
From: FROM_NAME [mailto:FROM_EMAIL]
Sent: Thursday, May 03, 2012 8:52 AM
To: 'Steve'
Subject: Email from John
EOD
            ),
            'ThunderBird' => array(<<<EOD
-------- Original Message --------
Subject: 	Email from John
Date: 	Thu, 7 Jun 2012 10:17:07 -0500
From: 	FROM_NAME <FROM_EMAIL>
Reply-To: 	<FROM_EMAIL>
EOD
            ),
            'Yahoo' => array(<<<EOD
--- On Fri, 6/8/12, FROM_NAME <FROM_EMAIL> wrote:

From: FROM_NAME <FROM_EMAIL>
Subject: Email from John
To: "Steve" <steve@example.com>
Date: Friday, June 8, 2012, 6:15 AM
EOD
            ),
        );
    }
?>