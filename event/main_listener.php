<?php
/**
*
* @package phpBB Session Admin
* @copyright (c) 2015 Lucifer
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace anavaro\sessionadmin\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class main_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.session_create_after'		=>	'create_session_after',
			'core.session_gc_after'		=>	'gc_colector',
			'core.update_session_after'		=>	'update_session',
		);
	}

	protected $db;
	protected $config;
	protected $ghost_table;
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config,
	$ghost_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->ghost_table = $ghost_table;
	}

	/**
	* Create sessiona after
	* If config sa_active_count is 0 test check count
	* Get session info and add it to ghost table and increase 'sa_active_count' var
	*/
	public function create_session_after($event)
	{
		// Let's first check if there are no active sessions realy 
		if ($event['session_data']['session_user_id'] != ANONYMOUS)
		{
			$sql = 'INSERT INTO ' . $this->ghost_table . ' ' . $this->db->sql_build_array('INSERT', $event['session_data']);
			$this->db->sql_query($sql);
		}
	}

	public function gc_colector()
	{
		$sql = 'UPDATE ' . $this->ghost_table . '
			SET session_page = \'expired\'
			WHERE  session_time < ' . (time() - $this->config['session_length']) . ' AND (session_page NOT LIKE \'ucp.php?mode=logout\' AND session_page NOT LIKE \'expired\')';
		error_log($sql);
		$this->db->sql_query($sql);
		// Let's clean BOTS (no need to hoard info for them)
		$sql = 'SELECT user_id FROM ' . USERS_TABLE . ' WHERE group_id = 6';
		$result = $this->db->sql_query($sql);
		$clean_bots = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$clean_bots[] = $row['user_id'];
		}
		$this->db->sql_freeresult($result);
		$sql = 'DELETE FROM ' . $this->ghost_table . ' WHERE ' . $this->db->sql_in_set('session_user_id', $clean_bots);
		error_log($sql);
		$this->db->sql_query($sql);
	}

	public function update_session($event)
	{
		$sql = 'UPDATE ' . $this->ghost_table . ' SET ' . $this->db->sql_build_array('UPDATE', $event['session_data']) . "
			WHERE session_id = '" . $this->db->sql_escape($event['session_id']) . "'";
		$this->db->sql_query($sql);
	}
}