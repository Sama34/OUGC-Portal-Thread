<?php

/***************************************************************************
 *
 *	OUGC Portal Thread plugin (/inc/plugins/ougc_portalthread.php)
 *	Author: Omar Gonzalez
 *	Copyright: © 2014 Omar Gonzalez
 *
 *	Website: http://omarg.me
 *
 *	Shows a thread directly within the portal page.
 *
 ***************************************************************************
 
****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('Direct initialization of this file is not allowed.');

// Run/Add Hooks
if(THIS_SCRIPT == 'portal.php')
{
	global $templatelist, $settings;

	// All right, so what if fid = -1? Lest make that equal to all forums
	if($settings['portal_announcementsfid'] == '-1')
	{
		global $forum_cache;
		$forum_cache or cache_forums();

		$fids = array();
		foreach($forum_cache as $forum)
		{
			if($forum['type'] == 'f' && $forum['active'] == 1 && $forum['open'] == 1)
			{
				$fids[(int)$forum['fid']] = (int)$forum['fid'];
			}
		}
		$settings['portal_announcementsfid'] = implode(',', array_unique($fids));
	}

	$plugins->add_hook('portal_start', 'ougc_portalthread_start', 11);
	$plugins->add_hook('redirect', 'ougc_portalthread_redirect');

	if(!isset($templatelist))
	{
		$templatelist = '';
	}
	else
	{
		$templatelist .= ',';
	}

	#$templatelist .= 'multipage_page_current, multipage_page, multipage_nextpage, multipage_prevpage, multipage_start, multipage_end, multipage';
}

// Plugin API
function ougc_portalthread_info()
{
	global $lang;
	ougc_portalthread_lang_load();

	return array(
		'name'			=> 'OUGC Portal Thread',
		'description'	=> $lang->ougc_portalthread_desc,
		'website'		=> 'http://omarg.me',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'http://omarg.me',
		'version'		=> '1.0',
		'versioncode'	=> 1000,
		'compatibility'	=> '16*',
		'guid' 			=> '',
		'pl'			=> array(
			'version'	=> 12,
			'url'		=> 'http://mods.mybb.com/view/pluginlibrary'
		)
	);
}

// _activate() routine
function ougc_portalthread_activate()
{
	global $cache, $PL;
	ougc_portalthread_deactivate();

	// Add settings group
	/*$PL->settings('ougc_awards', $lang->setting_group_ougc_awards, $lang->setting_group_ougc_awards_desc, array(
		'myalerts'	=> array(
		   'title'			=> $lang->setting_ougc_awards_myalerts,
		   'description'	=> $lang->setting_ougc_awards_myalerts_desc,
		   'optionscode'	=> 'yesno',
			'value'			=>	0,
		)
	));*/

	// Add template group
	$PL->templates('ougcportalthread', '<lang:ougc_portalthread>', array(
		''	=> '	{$pollbox}
	<div class="float_left">
		{$multipage}

	</div>
	<div class="float_right">
		<!--NEWPOINTS_BUMPTHREAD-->{$newreply}
	</div>
	{$ratethread}
	<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="clear: both; border-bottom-width: 0;">
		<tr>
			<td class="thead" colspan="2">
				<div style="float: right;">
					<span class="smalltext"><strong><a href="portal.php?mode=threaded&amp;tid={$tid}&amp;pid={$pid}#pid{$pid}">{$lang->threaded}</a> | <a href="portal.php?mode=linear&amp;tid={$tid}&amp;pid={$pid}#pid{$pid}">{$lang->linear}</a></strong></span>
				</div>
				<div>
					<strong>{$thread[\'threadprefix\']}{$thread[\'subject\']}</strong>
				</div>
			</td>
		</tr>
		{$classic_header}
	</table>
	<div id="posts">
		{$posts}
	</div>
	<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="border-top-width: 0;">
		<tr>
			<td colspan="2" class="tfoot">
				{$search_thread}
				<div>
					<strong>&laquo; <a href="{$next_oldest_link}">{$lang->next_oldest}</a> | <a href="{$next_newest_link}">{$lang->next_newest}</a> &raquo;</strong>
				</div>
			</td>
		</tr>
	</table>
	<div class="float_left">
		{$multipage}
	</div>
	<div style="padding-top: 4px;" class="float_right">
		<!--NEWPOINTS_BUMPTHREAD-->{$newreply}
	</div>
	<br style="clear: both;" />
	{$quickreply}
	{$threadexbox}
	{$similarthreads}
	<br />
	<div class="float_left">
		<ul class="thread_tools">
			<li class="printable"><a href="printthread.php?tid={$tid}">{$lang->view_printable}</a></li>
			<li class="sendthread"><a href="sendthread.php?tid={$tid}">{$lang->send_thread}</a></li>
			<li class="subscription_{$add_remove_subscription}"><a href="usercp2.php?action={$add_remove_subscription}subscription&amp;tid={$tid}&amp;my_post_key={$mybb->post_code}">{$add_remove_subscription_text}</a></li>
		</ul>
	</div>

	<div class="float_right" style="text-align: right;">
		{$moderationoptions}
		{$forumjump}
	</div>
	<br style="clear: both;" />
	{$usersbrowsing}',
	));

	// Insert/update version into cache
	$plugins = $cache->read('ougc_plugins');
	if(!$plugins)
	{
		$plugins = array();
	}

	$info = ougc_portalthread_info();

	if(!isset($plugins['portalthread']))
	{
		$plugins['portalthread'] = $info['versioncode'];
	}

	/*~*~* RUN UPDATES START *~*~*/

	/*~*~* RUN UPDATES END *~*~*/

	$plugins['portalthread'] = $info['versioncode'];
	$cache->update('ougc_plugins', $plugins);
}

// _deactivate() routine
function ougc_portalthread_deactivate()
{
	ougc_portalthread_pl_check();
}

// _is_installed() routine
function ougc_portalthread_is_installed()
{
	global $cache;

	$plugins = (array)$cache->read('ougc_plugins');

	return !empty($plugins['portalthread']);
}

// _uninstall() routine
function ougc_portalthread_uninstall()
{
	global $PL, $cache;
	ougc_portalthread_pl_check();

	// Delete version from cache
	$plugins = (array)$cache->read('ougc_plugins');

	if(isset($plugins['portalthread']))
	{
		unset($plugins['portalthread']);
	}

	if(!empty($plugins))
	{
		$cache->update('ougc_plugins', $plugins);
	}
	else
	{
		$PL->cache_delete('ougc_plugins');
	}
}

// Loads language strings
function ougc_portalthread_lang_load()
{
	global $lang;

	isset($lang->ougc_portalthread_desc) or $lang->load('ougc_portalthread', false, true);

	if(!isset($lang->ougc_portalthread_desc))
	{
		// Plugin API
		$lang->ougc_portalthread_desc = 'Shows a thread directly within the portal page.';

		// PluginLibrary
		$lang->ougc_portalthread_pl_required = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later to be uploaded to your forum.';
		$lang->ougc_portalthread_pl_old = 'This plugin requires <a href="{1}">PluginLibrary</a> version {2} or later, whereas your current version is {3}.';
	}
}

// PluginLibrary dependency check & load
function ougc_portalthread_pl_check()
{
	global $lang;
	ougc_portalthread_lang_load();
	$info = ougc_portalthread_info();

	if(!file_exists(PLUGINLIBRARY))
	{
		flash_message($lang->sprintf($lang->ougc_portalthread_pl_required, $info['pl']['url'], $info['pl']['version']), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}

	global $PL;

	$PL or require_once PLUGINLIBRARY;

	if($PL->version < $info['pl']['version'])
	{
		flash_message($lang->sprintf($lang->ougc_portalthread_pl_old, $info['pl']['url'], $info['pl']['version'], $PL->version), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}
}

// Hijack the portal
function ougc_portalthread_start()
{
	global $mybb;

	if(is_numeric($_GET['tid']))
	{
		$mybb->input['tid'] = (int)$_GET['tid'];
	}
	else
	{
		$mybb->input['tid'] = preg_replace('#[^a-z\.\-_]#i', '', $_GET['tid']);
	}

	if(empty($mybb->input['tid']))
	{
		return;
	}

	global $plugins;

	$plugins->add_hook('portal_end', 'ougc_portalthread_end', 9);

	$mybb->settings['portal_announcementsfid_bu'] = $mybb->settings['portal_announcementsfid'];
	$mybb->settings['portal_announcementsfid'] = $mybb->settings['portal_announcementsfid'] ? 'OUGC_PORTALTHREAD_SHOW' : '';
}

function ougc_portalthread_redirect(&$args)
{
	global $mybb, $tid;

	switch(THIS_SCRIPT)
	{
		case 'newreply.php':

			global $post_errors;

			if(count($post_errors) > 0)
			{
				break;
			}

			global $postinfo;

			if($postinfo['visible'] == -2)
			{
				$args['url'] = 'usercp.php?action=drafts';
			}
			elseif($postinfo['visible'] == 1)
			{
				$args['url'] = ougc_portalthread_info_get_post_link($postinfo['pid'], $tid).'#pid'.$postinfo['pid'];
			}
			else
			{
				$args['url'] = ougc_portalthread_info_get_thread_link($tid);
			}

			// This was a post made via the ajax quick reply
			if($mybb->input['ajax'])
			{
				if($postinfo['visible'] == 1)
				{
					// Was there a new post since we hit the quick reply button?
					if($mybb->input['lastpid'])
					{
						global $new_post;

						if($new_post['pid'] != $mybb->input['lastpid'])
						{
							$args['url'] = ougc_portalthread_info_get_thread_link($tid, 0, 'lastpost');
						}
					}

					// Lets see if this post is on the same page as the one we're viewing or not
					global $post_page;

					if($mybb->input['from_page'] && $post_page > $mybb->input['from_page'])
					{
						$args['url'] = ougc_portalthread_info_get_thread_link($tid, 0, 'lastpost');
					}
				}
				else
				{
					$args['url'] = ougc_portalthread_info_get_thread_link($tid, 0, 'lastpost');
				}
			}
			break;
		default:
			break;
	}
}

// Show the thread
function ougc_portalthread_end()
{
	global $mybb;

	if($mybb->settings['portal_announcementsfid'] != 'OUGC_PORTALTHREAD_SHOW')
	{
		$mybb->settings['portal_announcementsfid'] = $mybb->settings['portal_announcementsfid_bu'];
		return;
	}

	$mybb->settings['portal_announcementsfid'] = $mybb->settings['portal_announcementsfid_bu'];
	unset($mybb->settings['portal_announcementsfid_bu']);

	global $lang, $db, $theme, $header, $footer, $headerinclude;
	$lang->load('showthread');

	if(is_numeric($mybb->input['tid']))
	{
		$tid = $mybb->input['tid'] = (int)$mybb->input['tid'];
	}

	// Google SEO URL support
	// Code from Starpaul20's Move Posts plugin
	elseif($db->table_exists('google_seo'))
	{
		// Build regexp to match URL.
		$regexp = $mybb->settings['google_seo_url_threads'];

		if($regexp)
		{
			$regexp = preg_quote($regexp, '#');
			$regexp = str_replace('\\{\\$url\\}', '([^./]+)', $regexp);
			$regexp = str_replace('\\{url\\}', '([^./]+)', $regexp);
			$regexp = '#^'.$regexp.'$#u';
		}

		// Fetch the (presumably) Google SEO URL:
		$url = (string)$mybb->input['tid'];

		// $url can be either 'http://host/Thread-foobar' or just 'foobar'.

		// Kill anchors and parameters.
		$url = preg_replace('/^([^#?]*)[#?].*$/u', '\\1', $url);

		// Extract the name part of the URL.
		$url = preg_replace($regexp, '\\1', $url);

		// Unquote the URL.
		$url = urldecode($url);

		// If $url was 'http://host/Thread-foobar', it is just 'foobar' now.

		// Look up the ID for this item.
		$query = $db->simple_select('google_seo', 'id', 'idtype=\'4\' AND url=\''.$db->escape_string($url).'\'');

		$tid = (int)$db->fetch_field($query, 'id');
		//Both http://host/portal.php?tid=Thread-foobar and http://host/portal.php?tid=foobar work, last one shouln't
	};

	// Get the thread details from the database.
	$thread = get_thread($tid);

	if(!$tid || !$thread || substr($thread['closed'], 0, 6) == 'moved|')
	{
		error($lang->error_invalidthread);
	}

	// START: OUGC Show In Portal
	if(function_exists('ougc_showinportal_info') && !$thread['showinportal'])
	{
		error($lang->error_invalidthread);
	}
	// END: OUGC Show In Portal


	// Get thread prefix if there is one.
	$thread['threadprefix'] = $thread['displayprefix'] = '';
	if(!empty($thread['prefix']))
	{
		$threadprefix = build_prefixes($thread['prefix']);

		if(!empty($threadprefix['prefix']))
		{
			$thread['threadprefix'] = $threadprefix['prefix'].'&nbsp;';
			$thread['displayprefix'] = $threadprefix['displaystyle'].'&nbsp;';
		}
	}

	$fid = (int)$thread['fid'];

	$thread['username'] or $thread['username'] = $lang->guest;

	// Is the currently logged in user a moderator of this forum?
	$ismod = is_moderator($fid);
	$where['visibleonly'][] = $ismod ? 'visible=\'1\'' : '(visible=\'1\' OR visible=\'0\')';
	$where['visibleonly2'][] = $ismod ? 'p.visible=\'1\' AND t.visible=\'1\'' : '(p.visible=\'1\' OR p.visible=\'0\') AND (t.visible=\'1\' OR t.visible=\'0\')';

	// Make sure we are looking at a real thread here.
	if((!$thread['visible'] && !$ismod) || ($thread['visible'] > 1 && $ismod))
	{
		error($lang->error_invalidthread);
	}

	$forumpermissions = forum_permissions($fid);

	// Does the user have permission to view this thread?
	if(!$forumpermissions['canview'] || !$forumpermissions['canviewthreads'])
	{
		error_no_permission();
	}

	if(isset($forumpermissions['canonlyviewownthreads']) && $forumpermissions['canonlyviewownthreads'] && $thread['uid'] != $mybb->user['uid'])
	{
		error_no_permission();
	}

	$archive_url = build_archive_link('thread', $tid);

	// Does the thread belong to a valid forum?
	$forum = get_forum($fid);
	if(!$fid || !$forum || $forum['type'] != 'f')
	{
		error($lang->error_invalidforum);
	}

	// Check if this forum is password protected and we have a valid password
	check_forum_password($fid);

	empty($mybb->input['pid']) or $pid = $mybb->input['pid'];

	global $cache, $plugins, $templates, $header, $footer, $headerinclude;
	require_once MYBB_ROOT.'inc/functions_post.php';
	require_once MYBB_ROOT.'inc/functions_indicators.php';
	require_once MYBB_ROOT.'inc/class_parser.php';
	$parser = new postParser;

	// Forumdisplay cache
	$forum_stats = $cache->read('forumsdisplay');

	add_breadcrumb($thread['displayprefix'].$thread['subject'], ougc_portalthread_info_get_thread_link($thread['tid']));

	$plugins->run_hooks('showthread_start');

	$thread['firstpost'] or update_first_post($tid);
	
	// Does this thread have a poll?
	$pollbox = '';
	if(!empty($thread['poll']))
	{
		$query = $db->simple_select("polls", "*", "pid='".$thread['poll']."'", array('limit' => 1));
		$poll = $db->fetch_array($query);
		$poll['timeout'] = $poll['timeout']*60*60*24;
		$expiretime = $poll['dateline'] + $poll['timeout'];
		$now = TIME_NOW;

		// If the poll or the thread is closed or if the poll is expired, show the results.
		if($poll['closed'] || $thread['closed'] || ($expiretime < $now && $poll['timeout'] > 0))
		{
			$showresults = 1;
		}

		// If the user is not a guest, check if he already voted.
		if($mybb->user['uid'])
		{
			$query = $db->simple_select("pollvotes", "*", "uid='".$mybb->user['uid']."' AND pid='".$poll['pid']."'");
			while($votecheck = $db->fetch_array($query))
			{	
				$alreadyvoted = 1;
				$votedfor[$votecheck['voteoption']] = 1;
			}
		}
		elseif(isset($mybb->cookies['pollvotes'][$poll['pid']]) && $mybb->cookies['pollvotes'][$poll['pid']] !== '')
		{
			$alreadyvoted = 1;
		}
		$optionsarray = explode('||~|~||', $poll['options']);
		$votesarray = explode('||~|~||', $poll['votes']);
		$poll['question'] = htmlspecialchars_uni($poll['question']);
		$polloptions = '';
		$totalvotes = 0;

		for($i = 1; $i <= $poll['numoptions']; ++$i)
		{
			$poll['totvotes'] = $poll['totvotes'] + $votesarray[$i-1];
		}

		// Loop through the poll options.
		for($i = 1; $i <= $poll['numoptions']; ++$i)
		{
			// Set up the parser options.
			$parser_options = array(
				'allow_html' => $forum['allowhtml'],
				'allow_mycode' => $forum['allowmycode'],
				'allow_smilies' => $forum['allowsmilies'],
				'allow_imgcode' => $forum['allowimgcode'],
				'allow_videocode' => $forum['allowvideocode'],
				'filter_badwords' => 1
			);

			$option = $parser->parse_message($optionsarray[$i-1], $parser_options);
			$votes = $votesarray[$i-1];
			$totalvotes += $votes;
			$number = $i;

			// Mark the option the user voted for.
			$optionbg = 'trow1';
			$votestar = '';
			if($votedfor[$number])
			{
				$optionbg = 'trow2';
				$votestar = '*';
			}

			// If the user already voted or if the results need to be shown, do so; else show voting screen.
			if($alreadyvoted || $showresults)
			{
				$percent = 0;
				if((int)$votes)
				{
					$percent = number_format($votes/$poll['totvotes'] * 100, 2);
				}
				$imagewidth = round(($percent/3)*5);
				$imagerowwidth = $imagewidth+10;
				eval('$polloptions .= "'.$templates->get('showthread_poll_resultbit').'";');
			}
			elseif($poll['multiple'])
			{
				eval('$polloptions .= "'.$templates->get('showthread_poll_option_multiple').'";');
			}
			else
			{
				eval('$polloptions .= "'.$templates->get('showthread_poll_option').'";');
			}
		}

		// If there are any votes at all, all votes together will be 100%; if there are no votes, all votes together will be 0%.
		$totpercent = '0%';
		if($poll['totvotes'])
		{
			$totpercent = '100%';
		}

		// Check if user is allowed to edit posts; if so, show "edit poll" link.
		$edit_poll = '';
		if(is_moderator($fid, 'caneditposts'))
		{
			$edit_poll = ' | <a href="polls.php?action=editpoll&amp;pid='.$poll['pid'].'">'.$lang->edit_poll.'</a>';
		}

		// Decide what poll status to show depending on the status of the poll and whether or not the user voted already.
		if($alreadyvoted || $showresults)
		{
			$pollstatus = $lang->poll_closed;
			if($alreadyvoted)
			{
				$pollstatus = $lang->already_voted;
				
				if($mybb->usergroup['canundovotes'])
				{
					$pollstatus .= ' [<a href="polls.php?action=do_undovote&amp;pid='.$poll['pid'].'&amp;my_post_key='.$mybb->post_code.'\">'.$lang->undo_vote.'</a>]';
				}
			}

			$lang->total_votes = $lang->sprintf($lang->total_votes, $totalvotes);
			eval('$pollbox = "'.$templates->get('showthread_poll_results').'";');
			$plugins->run_hooks('showthread_poll_results');
		}
		else
		{
			$publicnote = '&nbsp;';
			if($poll['public'])
			{
				$publicnote = $lang->public_note;
			}
			eval('$pollbox = "'.$templates->get('showthread_poll').'";');
			$plugins->run_hooks('showthread_poll');
		}

	}

	// START: OUGC Portal Pagination
	$forumjump = '';
	if(function_exists('ougc_portalpagination_info') && $mybb->settings['enableforumjump'])
	{
		// Create the forum jump dropdown box.
		$forumjump = ougc_portalpagination_build_forum_jump(0, $fid, 1);
	}
	// END: OUGC Portal Pagination
	
	// Fetch some links
	$next_oldest_link = ougc_portalthread_info_get_thread_link($tid, 0, 'nextoldest');
	$next_newest_link = ougc_portalthread_info_get_thread_link($tid, 0, 'nextnewest');

	// Mark this thread as read
	mark_thread_read($tid, $fid);

	// If the forum is not open, show closed newreply button unless the user is a moderator of this forum.
	if($forum['open'])
	{
		eval('$newthread = "'.$templates->get('showthread_newthread').'";');

		// Show the appropriate reply button if this thread is open or closed
		if($thread['closed'])
		{
			eval('$newreply = "'.$templates->get('showthread_newreply_closed').'";');
		}
		else
		{
			eval('$newreply = "'.$templates->get('showthread_newreply').'";');
		}
	}

	// Create the admin tools dropdown box.
	$modoptions = '&nbsp;';
	$inlinemod = '';
	if($ismod)
	{
		$adminpolloptions = $closelinkch = $stickch = '';

		if($pollbox)
		{
			$adminpolloptions = "<option value=\"deletepoll\">".$lang->delete_poll."</option>";
		}
		if($thread['visible'])
		{
			$approveunapprovethread = "<option value=\"approvethread\">".$lang->approve_thread."</option>";
		}
		else
		{
			$approveunapprovethread = "<option value=\"unapprovethread\">".$lang->unapprove_thread."</option>";
		}

		if($thread['closed'])
		{
			$closelinkch = ' checked="checked"';
		}
		if($thread['sticky'])
		{
			$stickch = ' checked="checked"';
		}
		$closeoption = "<br /><label><input type=\"checkbox\" class=\"checkbox\" name=\"modoptions[closethread]\" value=\"1\"{$closelinkch} />&nbsp;<strong>".$lang->close_thread."</strong></label>";
		$closeoption .= "<br /><label><input type=\"checkbox\" class=\"checkbox\" name=\"modoptions[stickthread]\" value=\"1\"{$stickch} />&nbsp;<strong>".$lang->stick_thread."</strong></label>";
		$inlinecount = 0;
		$inlinecookie = 'inlinemod_thread'.$tid;
		$plugins->run_hooks('showthread_ismod');
	}

	// Increment the thread view.
	if($mybb->settings['delayedthreadviews'])
	{
		$db->shutdown_query("INSERT INTO ".TABLE_PREFIX."threadviews (tid) VALUES('{$tid}')");
	}
	else
	{
		$db->shutdown_query("UPDATE ".TABLE_PREFIX."threads SET views=views+1 WHERE tid='{$tid}'");
	}
	++$thread['views'];

	// Work out the thread rating for this thread.
	$rating = '';
	if($mybb->settings['allowthreadratings'] && $forum['allowtratings'])
	{
		$rated = 0;
		$lang->load("ratethread");
		if($thread['numratings'] <= 0)
		{
			$thread['width'] = 0;
			$thread['averagerating'] = 0;
			$thread['numratings'] = 0;
		}
		else
		{
			$thread['averagerating'] = floatval(round($thread['totalratings']/$thread['numratings'], 2));
			$thread['width'] = intval(round($thread['averagerating']))*20;
			$thread['numratings'] = intval($thread['numratings']);
		}

		if($thread['numratings'])
		{
			// At least >someone< has rated this thread, was it me?
			// Check if we have already voted on this thread - it won't show hover effect then.
			$query = $db->simple_select("threadratings", "uid", "tid='{$tid}' AND uid='{$mybb->user['uid']}'");
			$rated = $db->fetch_field($query, 'uid');
		}

		$not_rated = '';
		if(!$rated)
		{
			$not_rated = ' star_rating_notrated';
		}

		$ratingvotesav = $lang->sprintf($lang->rating_average, $thread['numratings'], $thread['averagerating']);
		eval("\$ratethread = \"".$templates->get("showthread_ratethread")."\";");
	}
	// Work out if we are showing unapproved posts as well (if the user is a moderator etc.)
	$visible = $ismod ? "AND (p.visible='0' OR p.visible='1')" : "AND p.visible='1'";
	
	// Can this user perform searches? If so, we can show them the "Search thread" form
	if($forumpermissions['cansearch'])
	{
		eval("\$search_thread = \"".$templates->get("showthread_search")."\";");
	}

	// Fetch the ignore list for the current user if they have one
	$ignored_users = array();
	if($mybb->user['uid'] && $mybb->user['ignorelist'])
	{
		$ignore_list = explode(',', $mybb->user['ignorelist']);
		foreach($ignore_list as $uid)
		{
			$ignored_users[$uid] = 1;
		}
	}
	
	// Which thread mode is our user using by default?
	$defaultmode = !empty($mybb->user['threadmode']) ? $mybb->user['threadmode'] : ($mybb->settings['threadusenetstyle'] ? 'threaded' : 'linear');
	
	// If mode is unset, set the default mode
	isset($mybb->input['mode']) or $mybb->input['mode'] = $defaultmode;

	// Threaded or linear display?
	if($mybb->input['mode'] != 'threaded')
	{
		$threadexbox = '';
		$mybb->settings['postsperpage'] or $mybb->settings['postperpage'] = 20;
		
		// Figure out if we need to display multiple pages.
		$page = 1;
		$perpage = $mybb->settings['postsperpage'];
		if(isset($mybb->input['page']) && $mybb->input['page'] != "last")
		{
			$page = intval($mybb->input['page']);
		}

		if(!empty($mybb->input['pid']))
		{
			$post = get_post($mybb->input['pid']);
			if($post)
			{
				$query = $db->query("
					SELECT COUNT(p.dateline) AS count FROM ".TABLE_PREFIX."posts p
					WHERE p.tid = '{$tid}'
					AND p.dateline <= '{$post['dateline']}'
					{$visible}
				");
				$result = $db->fetch_field($query, "count");
				if(($result % $perpage) == 0)
				{
					$page = $result / $perpage;
				}
				else
				{
					$page = intval($result / $perpage) + 1;
				}
			}
		}

		// Recount replies if user is a moderator to take into account unapproved posts.
		if($ismod)
		{
			$query = $db->simple_select("posts p", "COUNT(*) AS replies", "p.tid='$tid' $visible");
			$cached_replies = $thread['replies']+$thread['unapprovedposts'];
			$thread['replies'] = $db->fetch_field($query, 'replies')-1;
			
			// The counters are wrong? Rebuild them
			// This doesn't cover all cases however it is a good addition to the manual rebuild function
			if($thread['replies'] != $cached_replies)
			{
				require_once MYBB_ROOT."/inc/functions_rebuild.php";
				rebuild_thread_counters($thread['tid']);
			}
		}

		$postcount = intval($thread['replies'])+1;
		$pages = $postcount / $perpage;
		$pages = ceil($pages);

		if(isset($mybb->input['page']) && $mybb->input['page'] == "last")
		{
			$page = $pages;
		}

		if($page > $pages || $page <= 0)
		{
			$page = 1;
		}

		if($page)
		{
			$start = ($page-1) * $perpage;
		}
		else
		{
			$start = 0;
			$page = 1;
		}
		$upper = $start+$perpage;
		
		// Work out if we have terms to highlight
        $highlight = "";
        $threadmode = "";
        if($mybb->settings['seourls'] == "yes" || ($mybb->settings['seourls'] == "auto" && isset($_SERVER['SEO_SUPPORT']) && $_SERVER['SEO_SUPPORT'] == 1))
        {
            if($mybb->input['highlight'])
            {
                $highlight = "?highlight=".urlencode($mybb->input['highlight']);
            }
            
			if($defaultmode != "linear")
			{
	            if($mybb->input['highlight'])
	            {
	                $threadmode = "&amp;mode=linear";
	            }
	            else 
	            {
	                $threadmode = "?mode=linear";
	            }
			}
        }
        else
        {
			if(!empty($mybb->input['highlight']))
			{
				if(is_array($mybb->input['highlight']))
				{
					foreach($mybb->input['highlight'] as $highlight_word)
					{
						$highlight .= "&amp;highlight[]=".urlencode($highlight_word);
					}
				}
				else
				{
					$highlight = "&amp;highlight=".urlencode($mybb->input['highlight']);
				}
			}
            
            if($defaultmode != "linear")
            {
                $threadmode = "&amp;mode=linear";
            }
        }

        $multipage = multipage($postcount, $perpage, $page, str_replace("{tid}", $tid, THREAD_URL_PAGED.$highlight.$threadmode));
		if($postcount > $perpage)
		{
			eval("\$threadpages = \"".$templates->get("showthread_multipage")."\";");
		}

		// Lets get the pids of the posts on this page.
		$pids = "";
		$comma = '';
		$query = $db->simple_select("posts p", "p.pid", "p.tid='$tid' $visible", array('order_by' => 'p.dateline', 'limit_start' => $start, 'limit' => $perpage));
		while($getid = $db->fetch_array($query))
		{
			// Set the ID of the first post on page to $pid if it doesn't hold any value
			// to allow this value to be used for Thread Mode/Linear Mode links
			// and ensure the user lands on the correct page after changing view mode
			if(empty($pid))
			{
				$pid = $getid['pid'];
			}
			// Gather a comma separated list of post IDs
			$pids .= "$comma'{$getid['pid']}'";
			$comma = ",";
		}

		// If there are no pid's the thread is probably awaiting approval.
		$pids or error($lang->error_invalidthread);

		$pids = "pid IN($pids)";
		
		$attachcache = array();
		if($thread['attachmentcount'] > 0 || is_moderator($fid, 'caneditposts'))
		{
			// Now lets fetch all of the attachments for these posts.
			$query = $db->simple_select("attachments", "*", $pids);
			while($attachment = $db->fetch_array($query))
			{
				$attachcache[$attachment['pid']][$attachment['aid']] = $attachment;
			}
		}

		// Get the actual posts from the database here.
		$posts = '';
		$query = $db->query("
			SELECT u.*, u.username AS userusername, p.*, f.*, eu.username AS editusername
			FROM ".TABLE_PREFIX."posts p
			LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=p.uid)
			LEFT JOIN ".TABLE_PREFIX."userfields f ON (f.ufid=u.uid)
			LEFT JOIN ".TABLE_PREFIX."users eu ON (eu.uid=p.edituid)
			WHERE $pids
			ORDER BY p.dateline
		");
		while($post = $db->fetch_array($query))
		{
			if($thread['firstpost'] == $post['pid'])
			{
				$announcement = &$thread;
				$thread['visible'] or $post['visible'] = 0;

				// Make sure we can view this thread
				if($forumpermissions['canview'] == 0 || $forumpermissions['canviewthreads'] == 0 || $forumpermissions['canonlyviewownthreads'] == 1 && $thread['uid'] != $mybb->user['uid'])
				{
					continue;
				}

				$thread['message'] = $post['message'];
				$thread['pid'] = $post['pid'];
				$thread['smilieoff'] = $post['smilieoff'];
				$thread['threadlink'] = ougc_portalthread_info_get_thread_link($thread['tid']);
				
				if($thread['uid'] == 0)
				{
					$profilelink = htmlspecialchars_uni($thread['threadusername']);
				}
				else
				{
					$profilelink = build_profile_link($thread['username'], $thread['uid']);
				}
				
				if(!$thread['username'])
				{
					$thread['username'] = $thread['threadusername'];
				}
				$thread['subject'] = htmlspecialchars_uni($parser->parse_badwords($thread['subject']));
				if($thread['icon'] > 0 && $icon_cache[$thread['icon']])
				{
					$icon = $icon_cache[$thread['icon']];
					$icon = "<img src=\"{$icon['path']}\" alt=\"{$icon['name']}\" />";
				}
				else
				{
					$icon = "&nbsp;";
				}
				if($thread['avatar'] != '')
				{
					$avatar_dimensions = explode("|", $thread['avatardimensions']);
					if($avatar_dimensions[0] && $avatar_dimensions[1])
					{
						$avatar_width_height = "width=\"{$avatar_dimensions[0]}\" height=\"{$avatar_dimensions[1]}\"";
					}
					if (!stristr($thread['avatar'], 'http://'))
					{
						$thread['avatar'] = $mybb->settings['bburl'] . '/' . $thread['avatar'];
					}		
					$avatar = "<td class=\"trow1\" width=\"1\" align=\"center\" valign=\"top\"><img src=\"{$thread['avatar']}\" alt=\"\" {$avatar_width_height} /></td>";
				}
				else
				{
					$avatar = '';
				}
				$anndate = my_date($mybb->settings['dateformat'], $thread['dateline']);
				$anntime = my_date($mybb->settings['timeformat'], $thread['dateline']);

				if($thread['replies'])
				{
					eval("\$numcomments = \"".$templates->get("portal_announcement_numcomments")."\";");
				}
				else
				{
					eval("\$numcomments = \"".$templates->get("portal_announcement_numcomments_no")."\";");
					$lastcomment = '';
				}
				
				$plugins->run_hooks("portal_announcement");

				$parser_options = array(
					"allow_html" => $forum[$thread['fid']]['allowhtml'],
					"allow_mycode" => $forum[$thread['fid']]['allowmycode'],
					"allow_smilies" => $forum[$thread['fid']]['allowsmilies'],
					"allow_imgcode" => $forum[$thread['fid']]['allowimgcode'],
					"allow_videocode" => $forum[$thread['fid']]['allowvideocode'],
					"filter_badwords" => 1
				);
				if($thread['smilieoff'] == 1)
				{
					$parser_options['allow_smilies'] = 0;
				}

				$message = $parser->parse_message($thread['message'], $parser_options);
				
				if(is_array($attachcache[$thread['pid']]))
				{ // This post has 1 or more attachments
					$validationcount = 0;
					$id = $thread['pid'];
					foreach($attachcache[$id] as $aid => $attachment)
					{
						if($attachment['visible'])
						{ // There is an attachment thats visible!
							$attachment['filename'] = htmlspecialchars_uni($attachment['filename']);
							$attachment['filesize'] = get_friendly_size($attachment['filesize']);
							$ext = get_extension($attachment['filename']);
							if($ext == "jpeg" || $ext == "gif" || $ext == "bmp" || $ext == "png" || $ext == "jpg")
							{
								$isimage = true;
							}
							else
							{
								$isimage = false;
							}
							$attachment['icon'] = get_attachment_icon($ext);
							// Support for [attachment=id] code
							if(stripos($message, "[attachment=".$attachment['aid']."]") !== false)
							{
								if($attachment['thumbnail'] != "SMALL" && $attachment['thumbnail'] != '')
								{ // We have a thumbnail to show (and its not the "SMALL" enough image
									eval("\$attbit = \"".$templates->get("postbit_attachments_thumbnails_thumbnail")."\";");
								}
								elseif($attachment['thumbnail'] == "SMALL" && $forumpermissions[$thread['fid']]['candlattachments'] == 1)
								{
									// Image is small enough to show - no thumbnail
									eval("\$attbit = \"".$templates->get("postbit_attachments_images_image")."\";");
								}
								else
								{
									// Show standard link to attachment
									eval("\$attbit = \"".$templates->get("postbit_attachments_attachment")."\";");
								}
								$message = preg_replace("#\[attachment=".$attachment['aid']."]#si", $attbit, $message);
							}
							else
							{
								if($attachment['thumbnail'] != "SMALL" && $attachment['thumbnail'] != '')
								{ // We have a thumbnail to show
									eval("\$post['thumblist'] .= \"".$templates->get("postbit_attachments_thumbnails_thumbnail")."\";");
									if($tcount == 5)
									{
										$thumblist .= "<br />";
										$tcount = 0;
									}
									++$tcount;
								}
								elseif($attachment['thumbnail'] == "SMALL" && $forumpermissions[$thread['fid']]['candlattachments'] == 1)
								{
									// Image is small enough to show - no thumbnail
									eval("\$post['imagelist'] .= \"".$templates->get("postbit_attachments_images_image")."\";");
								}
								else
								{
									eval("\$post['attachmentlist'] .= \"".$templates->get("postbit_attachments_attachment")."\";");
								}
							}
						}
						else
						{
							$validationcount++;
						}
					}
					if($post['thumblist'])
					{
						eval("\$post['attachedthumbs'] = \"".$templates->get("postbit_attachments_thumbnails")."\";");
					}
					if($post['imagelist'])
					{
						eval("\$post['attachedimages'] = \"".$templates->get("postbit_attachments_images")."\";");
					}
					if($post['attachmentlist'] || $post['thumblist'] || $post['imagelist'])
					{
						eval("\$post['attachments'] = \"".$templates->get("postbit_attachments")."\";");
					}
				}

				#_dump($avatar, $post['attachments']);
				eval('$posts .= "'.$templates->get('portal_announcement').'";');
				unset($announcement);
			}
			else
			{
				$posts .= build_postbit($post);
			}
			unset($post);
		}
		$plugins->run_hooks("showthread_linear");
	}

	// Show the similar threads table if wanted.
	$similarthreads = '';
	if($mybb->settings['showsimilarthreads'])
	{
		switch($db->type)
		{
			case "pgsql":
				$query = $db->query("
					SELECT t.*, t.username AS threadusername, u.username
					FROM ".TABLE_PREFIX."threads t
					LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid = t.uid), plainto_tsquery ('".$db->escape_string($thread['subject'])."') AS query
					WHERE t.fid='{$thread['fid']}' AND t.tid!='{$thread['tid']}' AND t.visible='1' AND t.closed NOT LIKE 'moved|%' AND t.subject @@ query
					ORDER BY t.lastpost DESC
					OFFSET 0 LIMIT {$mybb->settings['similarlimit']}
				");
				break;
			default:
				$query = $db->query("
					SELECT t.*, t.username AS threadusername, u.username, MATCH (t.subject) AGAINST ('".$db->escape_string($thread['subject'])."') AS relevance
					FROM ".TABLE_PREFIX."threads t
					LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid = t.uid)
					WHERE t.fid='{$thread['fid']}' AND t.tid!='{$thread['tid']}' AND t.visible='1' AND t.closed NOT LIKE 'moved|%' AND MATCH (t.subject) AGAINST ('".$db->escape_string($thread['subject'])."') >= '{$mybb->settings['similarityrating']}'
					ORDER BY t.lastpost DESC
					LIMIT 0, {$mybb->settings['similarlimit']}
				");
		}

		$count = 0;
		$similarthreadbits = '';
		$icon_cache = $cache->read("posticons");
		while($similar_thread = $db->fetch_array($query))
		{
			++$count;
			$trow = alt_trow();
			if($similar_thread['icon'] > 0 && $icon_cache[$similar_thread['icon']])
			{
				$icon = $icon_cache[$similar_thread['icon']];
				$icon = "<img src=\"{$icon['path']}\" alt=\"{$icon['name']}\" />";
			}
			else
			{
				$icon = "&nbsp;";
			}				
			if(!$similar_thread['username'])
			{
				$similar_thread['username'] = $similar_thread['threadusername'];
				$similar_thread['profilelink'] = $similar_thread['threadusername'];
			}
			else
			{
				$similar_thread['profilelink'] = build_profile_link($similar_thread['username'], $similar_thread['uid']);
			}
			
			// If this thread has a prefix, insert a space between prefix and subject
			if($similar_thread['prefix'] != 0)
			{
				$prefix = build_prefixes($similar_thread['prefix']);
				$similar_thread['threadprefix'] = $prefix['displaystyle'].'&nbsp;';
			}
			
			$similar_thread['subject'] = $parser->parse_badwords($similar_thread['subject']);
			$similar_thread['subject'] = htmlspecialchars_uni($similar_thread['subject']);
			$similar_thread['threadlink'] = ougc_portalthread_info_get_thread_link($similar_thread['tid']);
			$similar_thread['lastpostlink'] = ougc_portalthread_info_get_thread_link($similar_thread['tid'], 0, "lastpost");

			$lastpostdate = my_date($mybb->settings['dateformat'], $similar_thread['lastpost']);
			$lastposttime = my_date($mybb->settings['timeformat'], $similar_thread['lastpost']);
			$lastposter = $similar_thread['lastposter'];
			$lastposteruid = $similar_thread['lastposteruid'];

			// Don't link to guest's profiles (they have no profile).
			if($lastposteruid == 0)
			{
				$lastposterlink = $lastposter;
			}
			else
			{
				$lastposterlink = build_profile_link($lastposter, $lastposteruid);
			}
			$similar_thread['replies'] = my_number_format($similar_thread['replies']);
			$similar_thread['views'] = my_number_format($similar_thread['views']);
			eval("\$similarthreadbits .= \"".$templates->get("showthread_similarthreads_bit")."\";");
		}
		if($count)
		{
			eval("\$similarthreads = \"".$templates->get("showthread_similarthreads")."\";");
		}
	}

	// Decide whether or not to show quick reply.
	$quickreply = '';
	if($forumpermissions['canpostreplys'] != 0 && $mybb->user['suspendposting'] != 1 && ($thread['closed'] != 1 || is_moderator($fid)) && $mybb->settings['quickreply'] != 0 && $mybb->user['showquickreply'] != '0' && $forum['open'] != 0)
	{
		$query = $db->simple_select("posts", "pid", "tid='{$tid}'", array("order_by" => "pid", "order_dir" => "desc", "limit" => 1));
		$last_pid = $db->fetch_field($query, "pid");
		
		// Show captcha image for guests if enabled
		$captcha = '';
		if($mybb->settings['captchaimage'] && !$mybb->user['uid'])
		{
			require_once MYBB_ROOT.'inc/class_captcha.php';
			$post_captcha = new captcha(true, "post_captcha");

			if($post_captcha->html)
			{
				$captcha = $post_captcha->html;
			}
		}

		$postoptionschecked = array('signature' => '', 'emailnotify' => '');
		if($mybb->user['signature'])
		{
			$postoptionschecked['signature'] = 'checked="checked"';
		}
		
		// Hide signature option if no permission
		$option_signature = '';
		if($mybb->usergroup['canusesig'] && !$mybb->user['suspendsignature'])
		{
			eval("\$option_signature = \"".$templates->get('showthread_quickreply_options_signature')."\";");
		}

		if(isset($mybb->user['emailnotify']) && $mybb->user['emailnotify'] == 1)
		{
			$postoptionschecked['emailnotify'] = 'checked="checked"';
		}

	    $posthash = md5($mybb->user['uid'].random_str());
		eval("\$quickreply = \"".$templates->get("showthread_quickreply")."\";");
	}
	
	// If the user is a moderator, show the moderation tools.
	if($ismod)
	{
		$customthreadtools = $customposttools = '';

		if(is_moderator($forum['fid'], 'canusecustomtools') && (!empty($forum_stats[-1]['modtools']) || !empty($forum_stats[$forum['fid']]['modtools'])))
		{
			switch($db->type)
			{
				case 'pgsql':
				case 'sqlite':
					$where = '\',\'||forums||\',\' LIKE \'%,'.$fid.',%\' OR \',\'||forums||\',\' LIKE \'%,-1,%\'';
					break;
				default:
					$where = 'CONCAT(\',\',forums,\',\') LIKE \'%,'.$fid.',%\' OR CONCAT(\',\',forums,\',\') LIKE \'%,-1,%\'';
					break;
			}
			$query = $db->simple_select('modtools', 'tid, name, type', $where.' OR forums=\'\'');
	
			while($tool = $db->fetch_array($query))
			{
				if($tool['type'] == 'p')
				{
					eval('$customposttools .= "'.$templates->get('showthread_inlinemoderation_custom_tool').'";');
				}
				else
				{
					eval('$customthreadtools .= "'.$templates->get('showthread_moderationoptions_custom_tool').'";');
				}
			}

			// Build inline moderation dropdown
			if(!empty($customposttools))
			{
				eval('$customposttools = "'.$templates->get('showthread_inlinemoderation_custom').'";');
			}
		}

		eval('$inlinemod = "'.$templates->get('showthread_inlinemoderation').'";');

		// Build thread moderation dropdown
		if(!empty($customthreadtools))
		{
			eval('$customthreadtools = "'.$templates->get('showthread_moderationoptions_custom').'";');
		}

		eval('$moderationoptions = "'.$templates->get('showthread_moderationoptions').'";');
	}

	isset($lang->newthread_in) or $lang->newthread_in = '';

	$lang->newthread_in = $lang->sprintf($lang->newthread_in, $forum['name']);
	
	// Subscription status
	$add_remove_subscription = 'add';
	$add_remove_subscription_text = $lang->subscribe_thread;
	if($mybb->user['uid'])
	{
		$query = $db->simple_select('threadsubscriptions', 'tid', 'tid=\''.$tid.'\' AND uid=\''.(int)$mybb->user['uid'].'\'', array('limit' => 1));

		if($db->fetch_field($query, 'tid'))
		{
			$add_remove_subscription = 'remove';
			$add_remove_subscription_text = $lang->unsubscribe_thread;
		}
	}

	$classic_header = '';
	if($mybb->settings['postlayout'] == "classic")
	{
		eval('$classic_header = "'.$templates->get('showthread_classic_header').'";');
	}
	
	// Get users viewing this thread
	if($mybb->settings['browsingthisthread'])
	{
		$timecut = TIME_NOW-$mybb->settings['wolcutoff'];

		$comma = $guestsonline = $onlinesep = $invisonline = $onlinesep2 = '';
		$guestcount = $membercount = $inviscount = $onlinemembers = '';
		$doneusers = array();

		$query = $db->query('
			SELECT s.ip, s.uid, s.time, u.username, u.invisible, u.usergroup, u.displaygroup
			FROM '.TABLE_PREFIX.'sessions s
			LEFT JOIN '.TABLE_PREFIX.'users u ON (s.uid=u.uid)
			WHERE s.time>\''.$timecut.'\' AND s.location=\''.$db->escape_string(get_current_location()).'\' AND s.nopermission!=\'1\'
			ORDER BY u.username ASC, s.time DESC
		');

		while($user = $db->fetch_array($query))
		{
			if(!$user['uid'])
			{
				++$guestcount;
				continue;
			}

			if(!(empty($doneusers[$user['uid']]) || $doneusers[$user['uid']] < $user['time']))
			{
				continue;
			}

			++$membercount;
			$doneusers[$user['uid']] = $user['time'];

			$invisiblemark = '';
			if($user['invisible'])
			{
				$invisiblemark = '*';
				++$inviscount;
			}

			if(!$user['invisible'] || $mybb->usergroup['canviewwolinvis'] || $user['uid'] == $mybb->user['uid'])
			{
				$user['profilelink'] = get_profile_link($user['uid']);
				$user['username'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
				$user['reading'] = my_date($mybb->settings['timeformat'], $user['time']);

				eval('$onlinemembers .= "'.$templates->get('showthread_usersbrowsing_user', 1, 0).'";');
				$comma = $lang->comma;
			}
		}

		if($guestcount)
		{
			$guestsonline = $lang->sprintf($lang->users_browsing_thread_guests, $guestcount);
		}

		if($guestcount && $onlinemembers)
		{
			$onlinesep = $lang->comma;
		}

		if($inviscount && !$mybb->usergroup['canviewwolinvis'] && (!$inviscount && !$mybb->user['invisible']))
		{
			$invisonline = $lang->sprintf($lang->users_browsing_thread_invis, $inviscount);
		}

		if($invisonline && $guestcount)
		{
			$onlinesep2 = $lang->comma;
		}

		eval('$usersbrowsing = "'.$templates->get('showthread_usersbrowsing').'";');
	}
	
	$plugins->run_hooks('showthread_end');

	global $announcements;

	eval('$announcements = "'.$templates->get('ougcportalthread').'";');
	
	
	
	
	
	
	
	#ougc_portalthread_activate();
}

function ougc_portalthread_info_get_post_link($pid, $tid=0)
{
	$postlink = get_post_link($pid, $tid);
	if(my_strpos('showthread.php', $postlink) === 0)
	{
		return str_replace(array('showthread.php'), array('portal.php'), $postlink);
	}

	global $settings;

	return $settings['bburl'].'/portal.php?tid='.$postlink;
}

function ougc_portalthread_info_get_thread_link($tid, $page=0, $action='')
{
	$threadlink = get_thread_link($tid, $page, $action);
	if(my_strpos('showthread.php', $threadlink) === 0)
	{
		return str_replace(array('showthread.php'), array('portal.php'), $threadlink);
	}

	global $settings;

	return $settings['bburl'].'/portal.php?tid='.$threadlink;
}