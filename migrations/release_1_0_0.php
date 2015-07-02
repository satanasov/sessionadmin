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
			array('config.add', array('sa_active_count', '0')),
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
					),
					'PRIMARY_KEY'	=> 'session_id',
					'KEYS'	=> array(
						'session_time'	=> array('INDEX', 'session_time'),
						'session_user_id'	=> array('INDEX', 'session_user_id'),
					),
				),
			),
		);
	}
	public function revert_schema()
	{
		return array(
			'drop_tables'		=> array(
				//$this->table_prefix . 'session_ghost',
			),
		);
	}
}
