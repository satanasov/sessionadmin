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
	protected $user;
	protected $ghost_table;
	protected $hosts_table;
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\user $user,
	$ghost_table, $hosts_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;
		$this->ghost_table = $ghost_table;
		$this->hosts_table = $hosts_table;
	}

	/**
	* Create sessiona after
	* If config sa_active_count is 0 test check count
	* Get session info and add it to ghost table and increase 'sa_active_count' var
	*/
	public function create_session_after($event)
	{
		// Let's first check if there are no active sessions realy 
		if ($event['session_data']['session_user_id'] != ANONYMOUS && !$this->user->data['is_bot'])
		{
			$sql = 'INSERT INTO ' . $this->ghost_table . ' ' . $this->db->sql_build_array('INSERT', $event['session_data']);
			$this->db->sql_query($sql);
		}
		if (!$this->user->data['is_bot'])
		{
			// We get the get host by address
			$hostname = gethostbyaddr($event['session_data']['session_ip']);
			if ($hostname != $event['session_data']['session_ip'])
			{
				$this->insert_in_db($event['session_data']['session_ip'], $hostname);
			}
		}
	}

	public function gc_colector()
	{
		$sql = 'UPDATE ' . $this->ghost_table . '
			SET session_page = \'expired\'
			WHERE  session_time < ' . (time() - $this->config['session_length']) . ' AND (session_page NOT LIKE \'ucp.php?mode=logout\' AND session_page NOT LIKE \'expired\')';
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
		$this->db->sql_query($sql);
	}

	public function update_session($event)
	{
		$sql = 'UPDATE ' . $this->ghost_table . ' SET ' . $this->db->sql_build_array('UPDATE', $event['session_data']) . "
			WHERE session_id = '" . $this->db->sql_escape($event['session_id']) . "'";
		$this->db->sql_query($sql);
	}
	
	private function insert_in_db($IP, $hostname)
	{
		$db_layer = $this->db->get_sql_layer();
		switch ($db_layer)
		{
			case 'mysql4':
			case 'mysql':
			case 'mssql':
			case 'mssqlnative':
			case 'oracle':
				$sql = 'INSERT IGNORE INTO ' . $this->hosts_table . ' (ip, hostname) VALUES(\'' . $IP . '\', \'' . $hostname . '\')';
			break;
			case 'postgres':
				$sql = 'INSERT INTO INTO ' . $this->hosts_table . ' (ip, hostname) SELECT \'' . $IP .'\',\'' . $hostname . '\' WHERE NOT EXISTS (SELECT 1 FROM INTO ' . $this->hosts_table . ' WHERE ip = \'' . $IP .'\' and hostname = \'' . $hostname . '\')';
			break;
			case 'sqlite':
			case 'sqlite3':
				$sql = 'INSERT OR IGNORE INTO INTO ' . $this->hosts_table . ' ip, hostname VALUES(\'' . $IP . '\', \'' . $hostname . '\')';
			break;
		}
		$this->db->sql_query($sql);
	}
}