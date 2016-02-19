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
		global $db, $user, $template, $config, $request, $phpbb_container, $table_prefix, $phpbb_root_path;
		$db_tools = $phpbb_container->get('dbal.tools');

		$case = $request->variable('case', '');
		$this->tpl_name		= 'acp_session_search';

		// Let's define image
		$image = array(
			'search'	=> '<img src="' . $phpbb_root_path . 'ext/anavaro/sessionadmin/adm/images/spyglass.png">',
		);
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
				$template->assign_vars(array(
					'SESSION_SEARCH_USER'	=> $user->lang('SESSION_SEARCH_USER_BASE', $username),
				));
				$this->page_title = $user->lang('SESSION_SEARCH_USER_BASE', $username);
			case 'userid':
				if (!isset($user_id))
				{
					$user_id = $request->variable('username', '', true);
					if ($user_id == '')
					{
						trigger_error('USERID_MISSING', E_USER_WARNING);
					}
				}
				if (!isset($username))
				{
					$template->assign_vars(array(
						'SESSION_SEARCH_USER'	=> $user->lang('SESSION_SEARCH_ID_BASE', $user_id),
					));
					$this->page_title = $user->lang('SESSION_SEARCH_ID_BASE', $user_id);
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
						'USER_IP'	=> '<a href="' . $this->u_action . '&case=userip&username=' . $row['session_ip'] .'">' . $row['session_ip'] . '</a>',
						'SESSION_START'	=> $user->format_date($row['session_start'], 'd.m.Y, H:i'),
						'SESSION_TIME'	=> $user->format_date($row['session_time'], 'd.m.Y, H:i'),
						'SESSION_PAGE'	=> $row['session_page'],
						'SESSION_VIEWONLINE'	=> $row['session_viewonline'],
						'SESSION_BROWSER'	=> $row['session_browser'],
					));
				}
				$db->sql_freeresult($result);
				$template->assign_var('S_SESSION_SEARCH_USER', true);
				break;
			case 'userip':
				$user_ip = $request->variable('username', '', true);
				if ($user_ip == '')
				{
					trigger_error('USERIP_MISSING', E_USER_WARNING);
				}

				$template->assign_vars(array(
					'SESSION_SEARCH'	=> $user->lang('SESSION_SEARCH_IP_BASE', $user_ip),
				));
				$this->page_title = $user->lang('SESSION_SEARCH_IP_BASE', $user_ip);
				// Let's get unique user IDs for this IP
				$sql = 'SELECT DISTINCT(session_user_id) as user_id FROM ' . $table_prefix . 'session_ghost WHERE session_ip = \'' . $user_ip . '\' ORDER BY session_time DESC';
				$result = $db->sql_query($sql);
				$user_ids = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$user_ids[] = $row['user_id'];
				}
				$db->sql_freeresult($result);

				if (empty($user_ids))
				{
					trigger_error('NO_USER', E_USER_WARNING);
				}

				// Let's get some live users from all the IP's (we could have deleted users and if we use some other extension we could have users_deleted table
				$users_array = array();
				$sql = 'SELECT user_id, username, user_colour
				FROM ' . USERS_TABLE . '
				WHERE ' . $db->sql_in_set('user_id', $user_ids) . '
				ORDER BY user_id ASC';
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result)) {
					$users_array[$row['user_id']] = array(
						'id'	=> $row['user_id'],
						'username'	=> $row['username'],
						'colour'	=> $row['user_colour'],
					);
				}
				$db->sql_freeresult($result);

				if ($db_tools->sql_table_exists($table_prefix . 'users_deleted'))
				{
					// If the db table exists we are going to use it to get deleted users
					$sql = 'SELECT user_id, username
					FROM ' . $table_prefix . 'users_deleted
					WHERE ' . $db->sql_in_set('user_id', $user_ids) . '
					ORDER BY user_id ASC';
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result)) {
						$users_array[$row['user_id']] = array(
							'id'	=> $row['user_id'],
							'username'	=> $row['username'],
							'colour'	=> '000000',
						);
					}
					$db->sql_freeresult($result);
				}

				foreach($users_array as $var)
				{
					$template->assign_block_vars('usernames', array(
						'USERNAME'	=> '<a class="username-coloured" style="color: #'.(isset($var['colour']) ? $var['colour'] : '') . ';" href="' .append_sid($phpbb_root_path. 'memberlist.php?mode=viewprofile&u=' . $var['id']) . '" target="_blank">' . $var['username'] .'</a> <a href="' . $this->u_action . '&case=userid&username=' . $var['id'] .'">' . $image['search'] . '</a>',
					));
				}

				// We will now build all sessions for this user ... but no admin needs more then 1000 sessions so we limit them to a 1000
				// Better use cold storage for sessions older then 6 months or a year
				$sql = 'SELECT * FROM ' . $table_prefix . 'session_ghost WHERE session_ip = \'' . $user_ip . '\' ORDER BY session_time DESC';
				$result = $db->sql_query_limit($sql, 1000, 0);
				while ($row = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('sessions', array(
						'USER_NAME'	=> '<a class="username-coloured" style="color: #'.(isset($users_array[$row['session_user_id']]['colour']) ? $users_array[$row['session_user_id']]['colour'] : '') . ';" href="' .append_sid($phpbb_root_path. 'memberlist.php?mode=viewprofile&u=' . $row['session_user_id']) . '" target="_blank">' . $users_array[$row['session_user_id']]['username'] .'</a>  <a href="' . $this->u_action . '&case=userid&username=' . $row['session_user_id'] .'">' . $image['search'] . '</a>',
						'SESSION_START'	=> $user->format_date($row['session_start'], 'd.m.Y, H:i'),
						'SESSION_TIME'	=> $user->format_date($row['session_time'], 'd.m.Y, H:i'),
						'SESSION_PAGE'	=> $row['session_page'],
						'SESSION_VIEWONLINE'	=> $row['session_viewonline'],
						'SESSION_BROWSER'	=> $row['session_browser'],
					));
				}
				$db->sql_freeresult($result);
				$template->assign_var('S_SESSION_SEARCH_IP', true);
			break;
			default:
				$template->assign_var('S_SESSION_SEARCH_SELECT', true);
			break;
		}
	}
}
