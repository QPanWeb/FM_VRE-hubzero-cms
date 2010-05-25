<?php
/**
 * @package		HUBzero CMS
 * @author		Nicholas J. Kisseberth <nkissebe@purdue.edu>
 * @copyright	Copyright 2005-2009 by Purdue Research Foundation, West Lafayette, IN 47906
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 *
 * Copyright 2005-2009 by Purdue Research Foundation, West Lafayette, IN 47906.
 * All rights reserved.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License,
 * version 2 as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$juri =& JURI::getInstance();

$sef = JRoute::_('index.php?option='.$this->option.'&task=question&id='.$this->id);
if (!strstr($sef,'http')) {
	if (substr($sef,0,1) == '/') {
		$sef = substr($sef,1,strlen($sef));
	}
}
$message  = JText::_('COM_ANSWERS_EMAIL_AUTO_RENERATED')."\n";
$message .= '----------------------------'."\n";
$message  = JText::_('COM_ANSWERS_EMAIL_Q_REMOVED');
$message .= JText::_('COM_ANSWERS_EMAIL_Q_REMOVED_NO_POINTS')."\n";
$message .= '----------------------------'."\n\n";
$message .= strtoupper(JText::_('COM_ANSWERS_QUESTION')).': '.$this->id."\n";
$message .= strtoupper(JText::_('COM_ANSWERS_SUMMARY')).': '.$this->question->subject."\n";
$message .= '----------------------------'."\n\n";

echo $message;
?>