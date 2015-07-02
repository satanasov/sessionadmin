<?php
/**
*
* @package acp
* @version $Id: acp_my_page.php,v 1.10 2006/12/31 16:56:14 acydburn Exp $
* @copyright (c) 2006 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

namespace anavaro\sessionadmin\acp;

/**
* @package module_install
*/
class acp_session_search_info
{
	function module()
	{
		return array(
			'filename'	=> 'anavaro\sessionadmin\acp\acp_session_search_module',
			'title'		=> 'ACP_SESSION_SEARCH', // define in the lang/xx/acp/common.php language file
			'version'	=> '1.0.0',
			'modes'		=> array(
				'main'		=> array(
					'title'		=> 'ACP_SESSION_SEARCH',
					'auth' 		=> 'ext_anavaro/sessionadmin && acl_a_user',
					'cat'		=> array('ACP_SESSION_GRP')
				),
			),
		);
	}
}
