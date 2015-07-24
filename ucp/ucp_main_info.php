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
* @package module_install
*/
class ucp_main_info
{
	function module()
	{
		return array(
			'filename'	=> '\anavaro\sessionadmin\ucp\ucp_main_module',
			'title'		=> 'SESSION_ADMIN',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'main'			=> array(
					'title' => 'UCP_SESSION_ADMIN',
					'auth' => 'ext_anavaro/sessionadmin',
					'cat' => array('UCP_ANAVARO_SESSION_ADMIN')
				),
			),
		);
	}
}