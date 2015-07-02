<?php
/**
*
* @package Anavaro.com PM Search
* @copyright (c) 2013 Lucifer
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
/**
* @ignore
*/
namespace anavaro\sessionadmin\acp;

/**
* @package acp
*/
class acp_session_active_module
{
	function main($id, $mode)
	{
		global $db, $user, $template, $config, $request, $table_prefix, $phpbb_root_path;

		$this->tpl_name		= 'acp_session_active';

		$ouptut = $users = array();
		$sql = 'SELECT * FROM phpbb_session_ghost WHERE session_page NOT LIKE \'expired\' AND session_page NOT LIKE \'ucp.php?mode=logout\' ORDER BY session_time';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$output[] = array(
				'user_id'		=> $row['session_user_id'],
				'session_start'	=> $user->format_date($row['session_start'], 'd.m.Y, H:i'),
				'session_time'	=> $user->format_date($row['session_time'], 'd.m.Y, H:i'),
				'session_ip'	=> $row['session_ip'],
				'session_page'	=> $row['session_page'],
				'session_browser'	=> $row['session_browser'],
				'session_viewonline'	=> $row['session_viewonline'],
				'session_forum_id'	=> $row['session_forum_id']
			);
			$users[] = $row['session_user_id'];
		}
		$db->sql_freeresult($result);
		// Let's request some users
		$users_array = array();
		$sql = 'SELECT user_id, username, user_colour
				FROM ' . USERS_TABLE . '
				WHERE group_id != 6 AND ' . $db->sql_in_set('user_id', $users) . '
				ORDER BY user_id ASC';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result)) {
			$users_array[$row['user_id']] = array(
				'id'	=> $row['user_id'],
				'username'	=> $row['username'],
				'colour'	=> $row['user_colour'],
			);
		}
		foreach ($output as $var)
		{
			$var['username'] = '<a class="username-coloured" style="color:#'.(isset($users_array[$var['user_id']]['colour']) ? $users_array[$var['user_id']]['colour'] : "000000") . '" href="' .append_sid($phpbb_root_path. 'memberlist.php?mode=viewprofile&u=' . $var['user_id']) . '" target="_blank">' . $users_array[$var['user_id']]['username'] .'</a>';
			// Let's buttify page a bit
			switch ($var['session_page'])
			{
				default:
					$var['session_page'] = '<a href="' . append_sid($phpbb_root_path . $var['session_page']) . '" target="_blank">' . $var['session_page'] . '</a>';
			}
			$template->assign_block_vars('sessions_active', array(
				'USERNAME'	=> $var['username'],
				'USER_IP'	=> $var['session_ip'],
				'SESSION_START'	=> $var['session_start'],
				'SESSION_TIME'	=> $var['session_time'],
				'SESSION_PAGE'	=> $var['session_page'],
				'SESSION_VIEWONLINE'	=> $var['session_viewonline'],
				'SESSION_BROWSER'	=> $var['session_browser'],
			));
		}
	}
}
