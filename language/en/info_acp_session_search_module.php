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
	'SESSION_SEARCH'				=> 'Search in session',
	'SESSION_SEARCH_EXPLAIN'			=> 'Search in DB session ghost tables. This will take some time.',

	'SESSION_SEARCH_FOR'	=> 'Search for',
	'SELECT_USERNAME'	=> 'Username',
	'SELECT_IP'	=> 'IP',
	'SELECT_ID'	=> 'Select user ID',

	'SESSION_SEARCH_USER_BASE'	=> 'Search in sessions for %1$s',
	'SESSION_SEARCH_ID_BASE'	=> 'Search in sessions for user id <b>%1$s</b>',
	'SESSION_SEARCH_IP_BASE'	=> 'Search in sessions for IP <b>%1$s</b>',
	'SESSION_SEARCH_USER_UNIQUE_IPS' => 'User\'s unique IP addresses',
	'SESSION_SEARCH_SESSIONS'	=> 'Stored user sessions',
	'SESSION_SEARCH_USER_UNIQUE_USERS'	=> 'Unique users used this IP',
));