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
class acp_session_search_module
{
	function main($id, $mode)
	{
		global $db, $user, $template, $config, $request, $table_prefix, $phpbb_root_path;

		$case = $request->variable('case', '');
		$this->tpl_name		= 'acp_session_search';

		switch($case)
		{
			case 'username':
				$username = utf8_normalize_nfc($request->variable('username', '', true));
				if ($username == '')
				{
					// To DO LANg
					trigger_error('USERNAME_MISSING', E_USER_WARNING);
				}
				$sql = 'SELECT user_id FROM ' . USERS_TABLE . '
				WHERE username_clean = \'' . $db->sql_escape(utf8_clean_string($username)) . '\'';
				$result = $db->sql_query($sql);
				$user_id = (int) $db->sql_fetchfield('user_id');
				$db->sql_freeresult($result);
				if (!$user_id)
				{
					trigger_error($user->lang['NO_USER'] . adm_back_link($this->u_action), E_USER_WARNING);
				}
			case 'userid':
				if (!$user_id)
				{
					$user_id = $request->variable('username', '', true);
					if ($user_id == '')
					{
						trigger_error('USERID_MISSING', E_USER_WARNING);
					}
				}

				// We are not going to build advanced request (users per user IP) as it takes to much time and rescources. 
				// So we will do simple request - all IPs for user, then we can manualy check every IP
				$sql = 'SELECT DISTINCT(session_ip) FROM ' . $table_prefix . 'session_ghost WHERE session_user_id = ' . $user_id . ' ORDER BY session_time DESC';
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('user_ips', array(
						'USER_IP'	=> '<a href="' . $this->u_action . '&case=userip&username=' . $row['session_ip'] .'">' . $row['session_ip'] . '</a>',
					));
				}
				$db->sql_freeresult($result);

				// We will now build all sessions for this user ... but no admin needs more then 1000 sessions so we limit them to a 1000
				// Better use cold storage for sessions older then 6 months or a year
				$sql = 'SELECT * FROM ' . $table_prefix . 'session_ghost WHERE session_user_id = ' . $user_id . ' ORDER BY session_time DESC';
				$result = $db->sql_query_limit($sql, 1000, 0);
				while ($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('sessions', array(
						'USER_IP'	=> $row['session_ip'],
						'SESSION_START'	=> $user->format_date($row['session_start'], 'd.m.Y, H:i'),
						'SESSION_TIME'	=> $user->format_date($row['session_time'], 'd.m.Y, H:i'),
						'SESSION_PAGE'	=> $row['session_page'],
						'SESSION_VIEWONLINE'	=> $row['session_viewonline'],
						'SESSION_BROWSER'	=> $row['session_browser'],
					));
				}
				$template->assign_var('S_SESSION_SEARCH_USER', true);
				break;
			default:
				$template->assign_var('S_SESSION_SEARCH_SELECT', true);
			break;
		}
	}
}
