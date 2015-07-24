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
	'SESSION_ACTIVE_TITLE'				=> 'Active ghost sessions',
	'SESSION_ACTIVE_EXPLAIN'			=> 'Active sessions that are logged in session ghost. From here you can see all current actions the users have taken.',

	'SESSION_START'		=> 'Session start time',
	'SESSION_TIME'		=> 'Last action time',
	'SESSION_PAGE'		=> 'Last page loaded',
	'SESSION_BROWSER'	=> 'User browser',
));