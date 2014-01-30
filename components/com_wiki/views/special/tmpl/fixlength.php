<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$pathway = JFactory::getApplication()->getPathway();
$pathway->addItem(
	JText::_('Fix Length'),
	$this->page->link()
);

$jconfig = JFactory::getConfig();
$juser = JFactory::getUser();

$database = JFactory::getDBO();

$query = "SELECT wv.id, wv.pageid, wv.pagetext FROM #__wiki_version AS wv WHERE wv.length = '0'";

$database->setQuery($query);
$rows = $database->loadObjectList();

?>
<form method="get" action="<?php echo JRoute::_($this->page->link()); ?>">
	<p>
		This special page updates the page length for every page revision.
	</p>
	<div class="container">
		<table class="entries">
			<thead>
				<tr>
					<th scope="col">
						<?php echo JText::_('Revision ID'); ?>
					</th>
					<th scope="col">
						<?php echo JText::_('Page ID'); ?>
					</th>
					<th scope="col">
						<?php echo JText::_('Length'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
<?php
if ($rows) 
{
	foreach ($rows as $row)
	{
		$lngth = strlen($row->pagetext);
		$database->setQuery("UPDATE #__wiki_version SET `length` = '" . $lngth . "' WHERE `id`='" . $row->id . "'");
		if (!$database->query()) 
		{
			$this->setError($database->getErrorMsg());
		}
?>
				<tr>
					<td>
						<?php echo $row->id; ?>
					</td>
					<td>
						<?php echo $row->pageid; ?>
					</td>
					<td>
						<?php echo $lngth; ?> bytes
					</td>
				</tr>
<?php
	}
}
else
{
?>
				<tr>
					<td colspan="4">
						<?php echo JText::_('No revisions needed updating.'); ?>
					</td>
				</tr>
<?php
}
?>
			</tbody>
		</table>

		<div class="clearfix"></div>
	</div>
</form>