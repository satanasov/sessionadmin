<?php
/**
*
* Session Admin [English]
*
* @package phpBB Session Admin
* @version $Id$
*
**/

/**
* DO NOT CHANGE
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACP_SESSION_GRP'	=> 'Session Administration',
	'ACP_SESSION_ACTIVE'	=> 'Active sessions',
	'ACP_SESSION_SEARCH'	=> 'Search in sessions',
	'ACP_SESSION_STORAGE'	=> 'Cold storage',
));