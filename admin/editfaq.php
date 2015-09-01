<?php
/***************************************************************************
 *   copyright				: (C) 2008 - 2014 WeBid
 *   site					: http://www.webidsupport.com/
 ***************************************************************************/

/***************************************************************************
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version. Although none of the code may be
 *   sold. If you have been sold this script, get a refund.
 ***************************************************************************/

define('InAdmin', 1);
$current_page = 'contents';
include '../common.php';
include $include_path . 'functions_admin.php';
include 'loggedin.inc.php';

// Default for error message (blank)
unset($ERR);

// Update message
if (isset($_POST['action']) && $_POST['action'] == 'update')
{
	if (empty($_POST['question'][$system->SETTINGS['defaultlanguage']])
		|| empty($_POST['answer'][$system->SETTINGS['defaultlanguage']]))
	{
		$ERR = $ERR_067;
		$faq = $_POST;
	}
	else
	{
		$query = "UPDATE " . $DBPrefix . "faqs SET category = :category,
			question = :question,
			answer = :answer
			WHERE id = :faq_id";
		$params = array();
		$params[] = array(':category', $_POST['category'], 'int');
		$params[] = array(':question', $_POST['question'][$system->SETTINGS['defaultlanguage']], 'str');
		$params[] = array(':answer', $_POST['answer'][$system->SETTINGS['defaultlanguage']], 'str');
		$params[] = array(':faq_id', $_POST['id'], 'int');
		$db->query($query, $params);
		reset($LANGUAGES);
		foreach ($LANGUAGES as $k => $v)
		{
			$query = "SELECT question FROM " . $DBPrefix . "faqs_translated WHERE lang = :lang AND id = :faq_id";
			$params = array();
			$params[] = array(':lang', $k, 'str');
			$params[] = array(':faq_id', $_POST['id'], 'int');
			$db->query($query, $params);
			$params = array();
			$params[] = array(':lang', $k, 'str');
			$params[] = array(':question', $_POST['question'][$k], 'str');
			$params[] = array(':answer', $_POST['answer'][$k], 'str');
			if ($db->numrows() > 0)
			{
				$query = "UPDATE " . $DBPrefix . "faqs_translated SET 
					question = :question,
					answer = :answer
					WHERE id = :faq_id AND lang = :lang";
				
			}
			else
			{
				$query = "INSERT INTO " . $DBPrefix . "faqs_translated VALUES
					(:faq_id, :lang, :question, :answer)";
				$params[] = array(':faq_id', $_POST['id'], 'int');
			}
			$db->query($query, $params);
		}  
		header('location: faqs.php');
		exit;
	}
}

// load categories
$query = "SELECT * FROM " . $DBPrefix . "faqscategories ORDER BY category";
$db->direct_query($query);
while ($row = $db->fetch())
{
	$template->assign_block_vars('cats', array(
			'ID' => $row['id'],
			'CAT' => $row['category']
			));
}

// Get data from the database
$query = "SELECT * FROM " . $DBPrefix . "faqs_translated WHERE id = :faq_id";
$params = array();
$params[] = array(':faq_id', $_GET['id'], 'int');
$db->query($query, $params);
while ($row = $db->fetch()))
{
	$QUESTION_tr[$row['lang']] = $row['question'];
	$ANSWER_tr[$row['lang']] = $row['answer'];
}
				
reset($LANGUAGES);
foreach ($LANGUAGES as $k => $v)
{
	$template->assign_block_vars('qs', array(
			'LANG' => $k,
			'QUESTION' => (isset($_POST['question'][$k])) ? $_POST['question'][$k] : $QUESTION_tr[$k]
			));
	$template->assign_block_vars('as', array(
			'LANG' => $k,
			'ANSWER' => (isset($_POST['answer'][$k])) ? $_POST['answer'][$k] : $ANSWER_tr[$k]
			));
}

// Get data from the database
$query = "SELECT * FROM " . $DBPrefix . "faqs WHERE id = :faq_id";
$params = array();
$params[] = array(':faq_id', $_GET['id'], 'int');
$db->query($query, $params);
$faq = $db->result();

$template->assign_vars(array(
		'ERROR' => (isset($ERR)) ? $ERR : '',
		'ID' => $faq['id'],
		'FAQ_NAME' => $faq['question'],
		'FAQ_CAT' => $faq['category']
		));

$template->set_filenames(array(
		'body' => 'editfaq.tpl'
		));
$template->display('body');

?>
