<?php
/**
*
* Session Admin extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 Lucifer <http://www.anavaro.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace anavaro\sessionadmin\controller;

class input
{
	/**
	* Constructor
	* NOTE: The parameters of this method must match in order and type with
	* the dependencies defined in the services.yml file for this service.
	*
	* @param \phpbb\db\driver	$db		Database object
	* @param \phpbb\user		$user		User object
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user,
	$fingerprint_table)
	{
		$this->db = $db;
		$this->user = $user;
		$this->fingerprint_table = $fingerprint_table;
	}

	public function base ($key)
	{
		if (!$this->user->data['is_bot'] && $this->user->data['user_id'] != ANONYMOUS && $key !== 0)
		{
			$sql = 'INSERT INTO ' . $this->fingerprint_table . ' (user_id, fingerprint, session_start) VALUES (' . $this->user->data['user_id'] . ', \'' . $key .'\', ' . $this->user->data['session_start'] . ')';
			$this->db->sql_query($sql);
			return new \Symfony\Component\HttpFoundation\JsonResponse(array(
				'result'	=> 'OK'
			));
		}
	}
}
