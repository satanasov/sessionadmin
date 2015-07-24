<?php
/**
*
* @package Anavaro Session Admin
* @copyright (c) 2015 Lucifer
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace anavaro\sessionadmin\ucp;

/**
* @package ucp
*/
class ucp_main_module
{
	var $u_action;
	function main($id, $mode)
	{
		global $db, $user, $template, $table_prefix;
		$this->db = $db;
		$this->user = $user;
		//$this->user->add_lang_ext('anavaro/sessionadmin', array('info_ucp_main_module'));
		$this->tpl_name = 'ucp_session_history';
		// Let's take some six month of access logs
		$six_months = time() - 15552000;
		$sql = 'SELECT * FROM ' . $table_prefix . 'session_ghost WHERE session_user_id = ' . $this->user->data['user_id'] . ' AND session_start > ' . $six_months . ' ORDER BY session_start DESC';
		$result = $this->db->sql_query($sql);
		$logs = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$logs[$row['session_start']][] = array(
				'type'	=> 'session',
				'ip'	=> $row['session_ip'],
				'start'	=> $row['session_start'],
				'time'	=> $row['session_time'],
				'action'	=> $row['session_page'],
				'extra'	=> $row['session_browser'],
			);
		}
		$this->db->sql_freeresult($result);

		// Now lets get user loged actions
		$sql = 'SELECT * FROM ' . LOG_TABLE . ' WHERE user_id = ' . $this->user->data['user_id'] .' AND log_time > ' . $six_months . ' AND (log_operation = \'LOGIN_ERROR_PASSWORD\' OR log_operation = \'LOG_USER_NEW_PASSWORD\') ORDER BY log_time DESC';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$logs[$row['log_time']][] = array(
				'type'	=> 'access',
				'ip'	=> $row['log_ip'],
				'start'	=> $row['log_time'],
				'time'	=> 0,
				'action'	=> $row['log_operation']
			);
		}
		$this->db->sql_freeresult($result);
		krsort($logs);
		foreach ($logs as $tmp)
		{
			foreach ($tmp as $var)
			{
				if ($var['type'] == 'access')
				{
					$template->assign_block_vars('logs', array(
						'TYPE' => 'access',
						'IP'	=> $var['ip'],
						'START'	=> $user->format_date($var['start'], 'd.m.Y, H:i'),
						'TIME'	=> '',
						'ACTION'	=> $this->user->lang($var['action']),
						'DECORATE'	=> ($var['action'] == 'LOGIN_ERROR_PASSWORD') ? ' pm_foe_colour' : ' pm_marked_colour'
					));
				}
				if ($var['type'] == 'session')
				{
					switch($var['action'])
					{
						case 'expired':
							$action_lang = $this->user->lang('SESSION_EXPIRED');
						break;
						case 'ucp.php?mode=logout':
							$action_lang = $this->user->lang('SESSION_LOGED_OUT');
						break;
						default:
							$action_lang = $var['action'];
					}
					$template->assign_block_vars('logs', array(
						'TYPE'	=> 'session',
						'IP'	=> $var['ip'],
						'START'	=> $user->format_date($var['start'], 'd.m.Y, H:i'),
						'TIME'	=> $user->format_date($var['time'], 'd.m.Y, H:i'),
						'ACTION'	=> $action_lang,
						'DECORATE'	=> '',
					));
				}
			}
		}
	}
}
?>