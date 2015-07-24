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
	'UCP_ANAVARO_SESSION_ADMIN'	=> 'History',
	'SESSION_ADMIN'	=> 'History',

	'UCP_HISTORY_TITLE'	=> 'History',
	'UCP_HISTORY_EXPLAIN'	=> 'Here you could see actions relevant to your account - loged sessions, changed passwords, failed login attempts for the last 6 months',

	'LAST_ACTION'	=> 'Last action',

	'LOG_USER_NEW_PASSWORD'	=> 'Password changed',
	'SESSION_EXPIRED'		=> 'Session expired',
	'SESSION_LOGED_OUT'		=> 'Session loged out',
	'LOGIN_ERROR_PASSWORD'	=> 'Incorrect password entered',
));