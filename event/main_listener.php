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
		if ($this->config['sa_active_count'] == 0)
		{
			$sql = 'SELECT COUNT(session_id) as count FROM ' . $this->ghost_table . ' WHERE session_page <> (\'expired\' OR \'ucp.php?mode=logout\')';
			$result = $this->db->sql_query($sql);
			$row = $this->db->sql_fetchrow($result);
			$this->config->set('sa_active_count', $row['count']);
		}
		if ($event['session_data']['session_user_id'] != ANONYMOUS)
		{
			$sql = 'INSERT INTO ' . $this->ghost_table . ' ' . $this->db->sql_build_array('INSERT', $event['session_data']);
			$this->db->sql_query($sql);
			$this->config->increment('sa_active_count', 1);
		}
	}

	public function gc_colector()
	{
		$sql = 'UPDATE ' . $this->ghost_table . '
			SET session_page = \'expired\'
			WHERE  session_time < ' . (time() - $this->config['session_length']) . ' AND (session_page <> \'ucp.php?mode=logout\' OR session_page <> \'expired\')';
		error_log($sql);
		$this->db->sql_query($sql);
		$affected_rows = $this->db->sql_affectedrows();
		$this->config->increment('sa_active_count', ($affected_rows * -1));
	}

	public function update_session($event)
	{
		$sql = 'UPDATE ' . $this->ghost_table . ' SET ' . $this->db->sql_build_array('UPDATE', $event['session_data']) . "
			WHERE session_id = '" . $this->db->sql_escape($event['session_id']) . "'";
		$this->db->sql_query($sql);
	}
}