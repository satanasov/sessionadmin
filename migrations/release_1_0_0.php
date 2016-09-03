<?php
/**
*
* @package migration
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace anavaro\sessionadmin\migrations;

class release_1_0_0 extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return array(
			//set configs
			array('config.add', array('sa_version', '1.0.0')),
			array('config.add', array('sa_cur', '100000')),
			array('config.add', array('sa_per_run', '200')),
			array('config.add', array('sa_per_file', '10000')),
			// Add text config - here we will store info about the cold storage
			array('config_text.add', array('sa_info', '{}')),
			// Add ACP Modules
			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_SESSION_GRP'
			)),
			array('module.add', array(
				'acp',
				'ACP_SESSION_GRP',
				array(
					'module_basename'	=> '\anavaro\sessionadmin\acp\acp_session_active_module',
					'module_mode'		=> array('main'),
					'module_auth'        => 'ext_anavaro/sessionadmin && acl_a_user',
				)
			)),
			array('module.add', array(
				'acp',
				'ACP_SESSION_GRP',
				array(
					'module_basename'	=> '\anavaro\sessionadmin\acp\acp_session_search_module',
					'module_mode'		=> array('main'),
					'module_auth'        => 'ext_anavaro/sessionadmin && acl_a_user',
				)
			)),
			array('module.add', array(
				'acp',
				'ACP_SESSION_GRP',
				array(
					'module_basename'	=> '\anavaro\sessionadmin\acp\acp_session_storage_module',
					'module_mode'		=> array('main'),
					'module_auth'        => 'ext_anavaro/sessionadmin && acl_a_user',
				)
			)),
		);
	}

	//lets create the needed table
	public function update_schema()
	{
		return array(
			'add_tables'    => array(
				$this->table_prefix . 'session_ghost'	=> array(
					'COLUMNS'	=> array(
						'session_id'	=> array('CHAR:32', ''),
						'session_user_id'	=> array('UINT', 0),
						'session_last_visit'	=> array('TIMESTAMP', 0),
						'session_start'	=> array('TIMESTAMP', 0),
						'session_time'	=> array('TIMESTAMP', 0),
						'session_ip'	=> array('VCHAR:40', ''),
						'session_browser'	=> array('VCHAR:150', ''),
						'session_forwarded_for'	=> array('VCHAR:255', ''),
						'session_page'	=> array('VCHAR_UNI', ''),
						'session_viewonline'	=> array('BOOL', 1),
						'session_autologin'	=> array('BOOL', 0),
						'session_admin'	=> array('BOOL', 0),
						'session_forum_id' => array('UINT', 0),
					),
					'PRIMARY_KEY'	=> 'session_id',
					'KEYS'	=> array(
//						'session_time'	=> array('INDEX', 'session_time'),
//						'session_user_id'	=> array('INDEX', 'session_uid'),
					),
				),
				$this->table_prefix . 'session_archive'	=> array(
					'COLUMNS'	=> array(
						'user_id'	=> array('UINT', 0),
						'user_ip'	=> array('VCHAR:40', ''),
					),
					'KEYS'	=> array(
						'prmry'	=> array('UNIQUE', array('user_id', 'user_ip')),
						'user_id'	=> array('INDEX', 'user_id'),
						'user_ip'	=> array('INDEX', 'user_ip'),
					),
				),
				$this->table_prefix . 'sessions_host'	=> array(
					'COLUMNS'	=> array(
						'ip'	=>  array('VCHAR:40', ''),
						'hostname'	=>  array('MTEXT_UNI', ''),
					),
					'KEYS'	=> array(
						'prmry'	=> array('UNIQUE', array('ip', 'hostname(255)')),
					),
				),
				/*$this->table_prefix . 'session_fingerprint'	=> array(
					'COLUMNS'	=> array(
						'user_id'	=> array('UINT', 0),
						'fingerprint'	=> array('VCHAR:40', ''),
						'session_start'	=> array('TIMESTAMP', 0),
					),
					'KEYS'	=> array(
						'pr'	=> array('UNIQUE', array('session_start', 'fingerprint', 'user_id'))
					),
				),*/
			),
		);
	}
	public function revert_schema()
	{
		return array(
			'drop_tables'		=> array(
				$this->table_prefix . 'session_ghost',
				$this->table_prefix . 'session_archive',
				$this->table_prefix . 'sessions_host',
//				$this->table_prefix . 'session_fingerprint',
			),
		);
	}
}
