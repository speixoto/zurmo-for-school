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

    class AboutView extends View
    {
        protected function renderContent()
        {
            $zurmoVersion    = VERSION;
            $yiiVersion     = YiiBase::getVersion();
            if (method_exists('R', 'getVersion'))
            {
                $redBeanVersion =  R::getVersion();
            }
            else
            {
                $redBeanVersion = '&lt; 1.2.9.1';
            }
            $content  = '<div>
            				<h1>Zurmo - <span>version {zurmoVersion}</span></h1>';
			
			$content .= '<div id="aboutText" class="clearfix">
							<div id="leftCol">
								<div id="ZurmoLogo" class="zurmo-logo"></div>
								<div>
									<p><strong>Zurmo</strong> is a <strong>Customer Relationship Management</strong> system by <strong>Zurmo Inc.</strong>
								</p>';
            				
            $content .= '<p>';
            $content .= Yii::t('Default', 'Visit the <strong>Zurmo Open Source Project</strong> at {url}.',
                           array('{url}' => '<a href="http://www.zurmo.org">http://www.zurmo.org</a>'));
            $content .= '<br/>';
            $content .= '</p>';
            $content .= '<p>';
            $content .= Yii::t('Default', 'Visit <strong>Zurmo Inc.</strong> at {url}.',
                        array('{url}' => '<a href="http://www.zurmo.com">http://www.zurmo.com</a>'));
            $content .= '<br/>';
            $content .= '</p>';
            $content .= '<p>';
            $content .= Yii::t('Default', '<strong>Zurmo</strong> is licensed under the GPLv3.  You can read the license <a href="http://www.zurmo.org/license">here</a>.');
            $content .= '</p></div>
							<div>
								<h3>Core Team</h3>
								<ul>
									<li>Lead Developer: Jason Green <a href="http://twitter.com/zurmojason">@ZurmoJason</a></li>
									<li>Lead Developer: Jason Green <a href="http://twitter.com/zurmojason">@ZurmoJason</a></li>
									<li>Lead Developer: Jason Green <a href="http://twitter.com/zurmojason">@ZurmoJason</a></li>
								</ul>
							</div>
							<div>
								<h3>Special Thanks</h3>
								<ul>
									<li>Lead Developer: Jason Green <a href="http://twitter.com/zurmojason">@ZurmoJason</a></li>
									<li>Lead Developer: Jason Green <a href="http://twitter.com/zurmojason">@ZurmoJason</a></li>
									<li>Lead Developer: Jason Green <a href="http://twitter.com/zurmojason">@ZurmoJason</a></li>
								</ul>
							</div>
						</div>';
			
            $content .= '<div id="rightCol"><div><h3>App Info</h3><p>';
            $content .= Yii::t('Default', 'This is <strong>version {zurmoVersion}</strong> of <strong>Zurmo</strong>.',
                        array('{zurmoVersion}' => $zurmoVersion));
            $content .= '</p>';
            $content .= '<p>';
            $content .= Yii::t('Default', '<strong>Zurmo</strong> uses the following great Open Source tools and frameworks:');
            $content .= '<ul>';
            $content .= '<li>';
            $content .= Yii::t('Default', '{url} (version {version} is installed)',
                           array('{url}'     => '<a href="http://www.yiiframework.com">Yii Framework</a>',
                                 '{version}' => $yiiVersion));
            $content .= '</li>';
			$content .= '<li>';
            $content .= Yii::t('Default', '{url} (version {version} is installed)',
                           array('{url}'     => '<a href="http://www.redbeanphp.com">RedBeanPHP ORM</a>',
                                 '{version}' => $redBeanVersion));
            $content .= '</li>';
			$content .= '<li>';
            $content .= Yii::t('Default', '{url} (installed with Yii)',
                           array('{url}'     => '<a href="http://www.jquery.com">jQuery JavaScript Framework</a>'));
            $content .= '</li>';
            $content .= '</ul></p></div>
            
            	<div><h3>RSS Feed</h3>please add this</div>
            	
            	<div>
	            	<script charset="utf-8" src="http://widgets.twimg.com/j/2/widget.js"></script>
					<script>
					new TWTR.Widget({
					  version: 2,
					  type: "profile",
					  rpp: 4,
					  interval: 30000,
					  width: "auto",
					  height: 300,
					  theme: {
					    shell: {
					      background: "#f4f4f4",
					      color: "#262777"
					    },
					    tweets: {
					      background: "#f4f4f4",
					      color: "#545454",
					      links: "#262777"
					    }
					  },
					  features: {
					    scrollbar: false,
					    loop: false,
					    live: false,
					    behavior: "all"
					  }
					}).render().setUser("ZurmoCRM").start();
					</script>
            	</div>
            	
				<div>
					<h3>Get Social with Zurmo</h3>
						<ul class="social-links clearfix">
							<li>
								<a href="https://www.facebook.com/pages/Zurmo/117701404997971" class="facebook" title="zurmo on facebook" target="_blank">
									<span>Zurmo CRM on Facebook</span>
								</a>
							</li>
							<li>
								<a href="http://twitter.com/ZurmoCRM" class="twitter" title="zurmo on twitter" target="_blank">
									<span>Zurmo CRM on Twitter</span> 
								</a>
							</li>
							<li>
								<a href="http://www.linkedin.com/company/zurmo-open-source" class="linkedin" title="zurmo on linkedin" target="_blank">
									<span>Zurmo CRM on LinkedIn</span>
								</a>
							</li>
							<li><a href="https://bitbucket.org/zurmo/" class="bitbucket" title="zurmo on bitbucket" target="_blank">
								<span>Zurmo CRM on BitBucket</span>
							</a></li>
							<li>
								<a href="https://www.pivotaltracker.com/projects/380027" class="pivotal" title="zurmo on pivotal tracker" target="_blank">
									<span>Zurmo CRM on Pivotal Tracker</span>
								</a>
							</li>
							<li>
								<a href="http://zurmo.org/feed" class="rss" title="zurmo rss" target="_blank">
									<span>Zurmo CRM RSS</span>
								</a>
							</li>
						</ul>
					</div>
            </div>
            
			<div id="aboutContactDetails">
				<p>
					Zurmo Inc. <span>|</span>
					<strong>Phone: </strong> (800) 391.4055 <span>|</span>
					<strong>Fax: </strong> (312) 244.3765 <span>|</span>
					<strong>Address: </strong> 27 North Wacker Drive Suite 370 Chicago IL 60606
				</p>
			</div>';
			
			
			
			$content .= '</div></div>';
			
            return $content;
        }
    }
?>




