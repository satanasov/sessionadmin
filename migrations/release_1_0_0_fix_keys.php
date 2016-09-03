<?php
/**
*
* @package migration
* @copyright (c) 2014 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License v2
*
*/

namespace anavaro\sessionadmin\migrations;

class release_1_0_0_fix_keys extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\anavaro\sessionadmin\migrations\release_1_0_0');
	}
        public function update_data()
        {
		return array(
			array('custom', array(array(&$this, 'make_keys'))),
		);
	}

	public function make_keys()
	{
		$sql = 'ALTER TABLE ' . $this->table_prefix . 'session_ghost ADD INDEX `session_time` (`session_time`)';
		$this->db->sql_query($sql);

		$sql = 'ALTER TABLE ' . $this->table_prefix . 'session_ghost ADD INDEX `session_user_id` (`session_user_id`)';
                $this->db->sql_query($sql);

                $sql = 'ALTER TABLE ' . $this->table_prefix . 'session_ghost ADD INDEX `session_fid` (`session_forum_id`)';
                $this->db->sql_query($sql);
	}
}
