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
	'UCP_ANAVARO_SESSION_ADMIN'	=> 'История',
	'SESSION_ADMIN'	=> 'История',

	'UCP_HISTORY_TITLE'	=> 'История',
	'UCP_HISTORY_EXPLAIN'	=> 'Тук можете да видите действия свързани с вашия профил - влизания, адреси, смяна на паролата, както и неупешни опити за влизане',

	'LAST_ACTION'	=> 'Последно действие',

	'LOG_USER_NEW_PASSWORD'	=> 'Сменена парола',
	'SESSION_EXPIRED'		=> 'Изтекла сесия',
	'SESSION_LOGED_OUT'		=> 'Разлогната сесия',
	'LOGIN_ERROR_PASSWORD'	=> 'Въвеждане на грешна парола',

	'ACTION'				=> 'Действие',
));