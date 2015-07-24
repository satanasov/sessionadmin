<?php
/**
*
* @package Anavaro.com Session Admin
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
class acp_session_storage_module
{
	function main($id, $mode)
	{
		global $db, $user, $template, $config, $request, $cache, $phpbb_container, $table_prefix, $phpbb_root_path;
		$db_tools = $phpbb_container->get('dbal.tools');
		$this->tpl_name		= 'acp_session_storage';
		$this->page_title = 'SESSION_STORAGE_TITLE';
		$config_text = $phpbb_container->get('config_text');

		// Let's see the file structure
		// We will use files directory of phpbb (it has to be writable)
		if (!is_writable($phpbb_root_path . 'files'))
		{
			$template->assign_vars(array(
				'U_FILES' => '<strong><font color="#CC0000">' . $user->lang('NO_WRITE_ACCESS_TO_FILES') . '</font></strong>',
				'U_STORAGE' => '<strong><font color="CC0000">' . $user->lang('NO_WRITE_ACCESS_TO_STORAGE') . '</font></strong>',
			));
		}
		else
		{
			$template->assign_vars(array(
				'U_FILES' => '<strong><font color="#009900">' . $user->lang('ACCESS_OK') . '</font></strong>',
			));
			if (!file_exists($phpbb_root_path . 'files/sessions'))
			{
				mkdir($phpbb_root_path . 'files/sessions', 0755, true);
				touch($phpbb_root_path . 'files/sessions/index.html');
				$template->assign_vars(array(
					'U_STORAGE' => '<strong><font color="#009900">' . $user->lang('STORAGE_CREATED') . '</font></strong>',
				));
			}
			else
			{
				if (!is_writable($phpbb_root_path . 'files/sessions'))
				{
					$template->assign_vars(array(
						'U_STORAGE' => '<strong><font color="#CC0000">' . $user->lang('NO_WRITE_ACCESS_TO_STORAGE') . '</font></strong>',
					));
				}
				else
				{
					$template->assign_vars(array(
						'U_STORAGE' => '<strong><font color="#009900">' . $user->lang('ACCESS_OK') . '</font></strong>',
					));
				}
			}
		}

		// Let's define image
		$image = array(
			'search'	=> '<img src="' . $phpbb_root_path . 'ext/anavaro/sessionadmin/adm/images/spyglass.png">',
		);

		// Add some variables
		$template->assign_vars(array(
			'TITLE'	=> $user->lang('SESSION_STORAGE_TITLE'),
			'EXPLAIN'	=> $user->lang('SESSION_STORAGE_EXPLAIN'),
		));

		// Furst update vars
		$sa_per_run = $request->variable('perrun', 0);
		$sa_per_file = $request->variable('perfile', 0);
		if ($sa_per_run > 0)
		{
			$config->set('sa_per_run', $sa_per_run);
		}
		if ($sa_per_file > 0)
		{
			$config->set('sa_per_file', $sa_per_file);
		}

		//We have update the variables, so we can check if we should show config or start archiving.
		$confirmstart = $request->variable('confirmstart', false);
		$reset = $request->variable('reset', false);
		if ($reset)
		{
			$config->delete('sa_temp_oldest');
			$config->delete('sa_temp_writen');
			$config_text->set('sa_info', json_encode(array()));
			$cache->destroy('_users_to_ip');
		}
		if ($confirmstart)
		{
			$months = $request->variable('months', 0);
			$period = $request->variable('period_select', '');
			if ($months < 1 && !$config['sa_temp_oldest'])
			{
				trigger_error($user->lang('NO_TIME_LIMIT_SELECETED'), E_USER_WARNING);
			}
			$time = $months * 2592000;
			if ($period == 'last' && !$config['sa_temp_oldest'])
			{
				$sql = 'SELECT session_time FROM ' . $table_prefix . 'session_ghost ORDER BY session_time ASC';
				$result = $db->sql_query_limit($sql, 1, 0);
				$row = $db->sql_fetchrow($result);
				$oldest_record = $row['session_time'];
				$db->sql_freeresult($result);
				
				$config->set('sa_temp_oldest', $oldest_record + $time);
			}
			if ($period == 'until' && !$config['sa_temp_oldest'])
			{
				$config->set('sa_temp_oldest', time() - $time);
			}

			// Get info for current file
			$info = json_decode($config_text->get('sa_info'), true);
			if (isset($info[$config['sa_cur']]))
			{
				$delta = $config['sa_per_file'] - $info[$config['sa_cur']]['stored'];
				$oldest = $info[$config['sa_cur']]['start'];
				$newest = $info[$config['sa_cur']]['end'];
			}
			else
			{
				$info[$config['sa_cur']] = array(
					'stored'	=> 0,
					'start'		=> 0,
					'end'		=> 0,
				);
				$delta = $config['sa_per_file'];
				$oldest = 0;
				$newest = 0;
			}
			// Check if we can record to file
			if ($delta == 0)
			{
				$config->increment('sa_cur', 1);
				$config->set('sa_temp_writen', 0);
				$info[$config['sa_cur']] = array(
					'stored'	=> 0,
					'start'		=> 0,
					'end'		=> 0,
				);
				$oldest = 0;
				$newest = 0;
				$stored = 0;
				$delta = $config['sa_per_file'] - $info[$config['sa_cur']]['stored'];
			}

			if ($delta > $config['sa_per_run'])
			{
				$delta = $config['sa_per_run'];
			}
			$sql = 'SELECT * FROM ' . $table_prefix . 'session_ghost WHERE session_time < ' . $config['sa_temp_oldest'] . ' ORDER BY session_time ASC';
			$result = $db->sql_query_limit($sql, $delta, 0);
			$rows_writen = 0;
			$users_to_ip = $storage = $session_keys = array();
			while ($row = $db->sql_fetchrow($result))
			{
				$rows_writen++;
				$users_to_ip[] = array(
					'user_id'	=> $row['session_user_id'],
					'ip'	=> $row['session_ip'],
				);
				$storage[] = $row;
				$session_keys[] = $row['session_id'];
				if ($newest == 0)
				{
					$oldest = $row['session_time'];
				}
				$oldest = ($oldest > $row['session_time'] ? $row['session_time'] : $oldest);
				$newest = ($newest < $row['session_time'] ? $row['session_time'] : $newest); 
			}
			$db->sql_freeresult($result);
			
			if ($rows_writen > 0)
			{
				$affected = $this->insert_in_db($users_to_ip);
				$json = json_encode($storage, JSON_PRETTY_PRINT);
				file_put_contents($phpbb_root_path . 'files/sessions/' . $config['sa_cur'] . '.json', $json, FILE_APPEND);
				$config->increment('sa_temp_writen', $rows_writen);
				
				$info[$config['sa_cur']]['stored'] = $info[$config['sa_cur']]['stored'] + $rows_writen;
				$info[$config['sa_cur']]['start'] = $oldest;
				$info[$config['sa_cur']]['end'] = $newest;
				
				$config_text->set('sa_info', json_encode($info));

				$sql = 'DELETE FROM ' . $table_prefix . 'session_ghost WHERE ' . $db->sql_in_set('session_id', $session_keys);
				$db->sql_query($sql);
				meta_refresh(2, $this->u_action . '&confirmstart=true');
				trigger_error($user->lang('CONTINUE_ARCHIVE', $rows_writen, $config['sa_cur'], $config['sa_temp_writen'], $config['sa_per_file'], $affected));
			}
			else
			{
				$config->delete('sa_temp_oldest');
				$config->delete('sa_temp_writen');
			}
			
		}

		$sql = 'SELECT session_time FROM ' . $table_prefix . 'session_ghost ORDER BY session_time ASC';
		$result = $db->sql_query_limit($sql, 1, 0);
		$row = $db->sql_fetchrow($result);
		$oldest_record = $row['session_time'];
		$db->sql_freeresult($result);

		$sql = 'SELECT COUNT(session_id) as count FROM ' . $table_prefix . 'session_ghost';
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$record_count = $row['count'];
		$db->sql_freeresult($result);

		$template->assign_vars(array(
			'TOTAL_RECORDS'	=> $record_count,
			'OLDEST_RECORD'	=> $user->format_date($oldest_record, 'd.m.Y, H:i'),
			'CURRENT_FILE'	=> $config['sa_cur'],
			'PER_RUN'		=> $config['sa_per_run'],
			'PER_FILE'		=> $config['sa_per_file'],
			'U_PERIOD_SELECT' => '<option value="until">' . $user->lang('UNTIL') . '</option><option value="last">' . $user->lang('LAST') . '</option>',
		));
		// Get info about cold storage

		$info = json_decode($config_text->get('sa_info'), true);
		//var_dump($info);
		if (empty($info))
		{
		}
		else
		{
		}
	}

	/**
	* insert_in_db
	* Insert array in DB
	*
	* @var	array	$input	array to insert
	*
	* return int affected rows
	*/
	private function insert_in_db($input)
	{
		global $db, $table_prefix;
		$db_layer = $db->get_sql_layer();
		$affected = 0;
		foreach ($input as $var)
		{
			switch ($db_layer)
			{
				case 'mysql4':
				case 'mysql':
				case 'mssql':
				case 'mssqlnative':
				case 'oracle':
					$sql = 'INSERT IGNORE INTO ' . $table_prefix . 'session_archive (user_id, user_ip) VALUES(' . $var['user_id'] . ', \'' . $var['ip'] . '\')';
				break;
				case 'postgres':
					$sql = 'INSERT INTO ' . $table_prefix . 'session_archive(user_id, user_ip) SELECT ' . $var['user_id'] .',\'' . $var['ip'] . '\' WHERE NOT EXISTS (SELECT 1 FROM ' . $table_prefix . 'session_archive WHERE user_id = ' . $var['user_id'] .' and user_ip = \'' . $var['ip'] . '\')';
				break;
				case 'sqlite':
				case 'sqlite3':
					$sql = 'INSERT OR IGNORE INTO ' . $table_prefix . 'session_archive user_id, user_ip VALUES(' . $var['user_id'] . ', \'' . $var['ip'] . '\')';
				break;
			}
			$db->sql_query($sql);
			$affected = $affected + $db->sql_affectedrows();
		}

		return $affected;
	}
}
