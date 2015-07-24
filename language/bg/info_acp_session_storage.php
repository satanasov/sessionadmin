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
	'SESSION_STORAGE_TITLE'				=> 'Session admin Cold storage',
	'SESSION_STORAGE_EXPLAIN'			=> 'As storage admin logs all user sessions it generates a lot of overhead regarding database entries. To simplify things - use this to put older entries in "cold storage" (put them in files and away from database). You could download the cold storage entries from here.',

	'SESSION_ARCHIVE'	=> 'Archive settings',
	'TOTAL_RECORDS'		=> 'Total records',
	'OLDEST_RECORD'		=> 'Oldest record',

	'NO_WRITE_ACCESS_TO_FILES'	=> 'Can not write to phpBB <b> files </b> directory',
	'NO_WRITE_ACCESS_TO_STORAGE'	=> 'Con not write to phpBB <b>files/sessions</b> directory',
	'STORAGE_CREATED'				=> 'Storage created',

	'CURRENT_FILE'		=> 'Current storage file',
	'PER_RUN'			=> 'Per run limit',
	'PER_FILE'			=> 'Per file limit',

	'ACCESS_OK'					=> 'OK',

	'SESSION_CONFORM_START'		=> 'Confirm start archiving',
	'SESSION_CONFORM_START_EXPLAIN'	=> 'If you do not check this box the archiving will not start. This will continue already running archive job.',

	'TIME_PERIOD'	=> 'Time period',
	'TIME_PERIOD_EXP'	=> 'Choose time period to be archived. <br> <strong>Until</strong> will archive all sessions until given months ago. <br> <strong>Last</strong> will archive last given months.',
	'LAST'	=> 'Last',
	'UNTIL'	=> 'Until',
	'MONTHS'	=> 'months',

	'NO_TIME_LIMIT_SELECETED'	=> 'There is no period selected for archiving or period is invalid',

	'CONTINUE_ARCHIVE'			=> 'Using file %2$s.<br>Written %3$s from %4$s lines limit<br>%1$s lines written this pass', 
));