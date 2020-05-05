<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright 2005-2019 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$this->css()
     ->js();
?>

<form action="<?php echo Route::url($this->member->link() . '&active=messages&task=archive'); ?>" method="post">

	<div id="filters">
		<input type="hidden" name="inaction" value="archive" />
		<?php echo Lang::txt('PLG_MEMBERS_MESSAGES_FROM'); ?>
		<select class="option" name="filter">
			<option value=""><?php echo Lang::txt('PLG_MEMBERS_MESSAGES_ALL'); ?></option>
			<?php
				if ($this->components) {
					foreach ($this->components as $component)
					{
						$component = substr($component->component, 4);
						$sbjt  = "\t\t\t".'<option value="'.$component.'"';
						$sbjt .= ($component == $this->filters['filter']) ? ' selected="selected"' : '';
						$sbjt .= '>'.$component.'</option>'."\n";
						echo $sbjt;
					}
				}
			?>
		</select>
		<input class="btn" type="submit" value="<?php echo Lang::txt('PLG_MEMBERS_MESSAGES_FILTER'); ?>" />
	</div>

	<div id="actions">
		<select class="option" name="action">
			<option value=""><?php echo Lang::txt('PLG_MEMBERS_MESSAGES_MSG_WITH_SELECTED'); ?></option>
			<option value="sendtoinbox"><?php echo Lang::txt('PLG_MEMBERS_MESSAGES_MSG_SEND_TO_INBOX'); ?></option>
			<option value="sendtotrash"><?php echo Lang::txt('PLG_MEMBERS_MESSAGES_MSG_SEND_TO_TRASH'); ?></option>
		</select>
		<input type="hidden"name="activetab" value="archive" />
		<input class="btn" type="submit" value="<?php echo Lang::txt('PLG_MEMBERS_MESSAGES_MSG_APPLY'); ?>" />
	</div>
	<br class="clear" />

	<table class="data">
		<thead>
			<tr>
				<th scope="col"><input type="checkbox" name="msgall" id="msgall" value="all" /></th>
				<th scope="col"> </th>
				<th scope="col"><?php echo Lang::txt('PLG_MEMBERS_MESSAGES_SUBJECT'); ?></th>
				<th scope="col"><?php echo Lang::txt('PLG_MEMBERS_MESSAGES_FROM'); ?></th>
				<th scope="col"><?php echo Lang::txt('PLG_MEMBERS_MESSAGES_DATE_RECEIVED'); ?></th>
				<th scope="col"> </th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="6">
					<?php
					$pageNav = new \Hubzero\Pagination\Paginator(
						$this->total,
						$this->filters['start'],
						$this->filters['limit']
					);
					$pageNav->setAdditionalUrlParam('id', $this->member->get('id'));
					$pageNav->setAdditionalUrlParam('active', 'messages');
					$pageNav->setAdditionalUrlParam('task', 'archive');
					$pageNav->setAdditionalUrlParam('action', '');

					echo $pageNav->render();
					?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php if ($this->rows) : ?>
				<?php foreach ($this->rows as $row) : ?>
					<?php
						$check = "<input class=\"chkbox\" type=\"checkbox\" id=\"msg{$row->id}\" value=\"{$row->id}\" name=\"mid[]\" />";

						//get the message status
						$status = ($row->whenseen && $row->whenseen != '0000-00-00 00:00:00') ? '<span class="read">read</span>' : '<span class="unread">unread</span>';

						//get the component that created message
						$component = (substr($row->component, 0, 4) == 'com_') ? substr($row->component, 4) : $row->component;

						//url to view message
						$url = Route::url($this->member->link() . '&active=messages&msg=' . $row->id);

						//get the message subject
						$subject = $row->subject;

						//support - special
						if ($component == 'support')
						{
							$fg = explode(' ', $row->subject);
							$fh = array_pop($fg);
							$subject = implode(' ', $fg);
						}

						//get the message
						$preview = ($row->message) ? "<h3>Message Preview:</h3>" . nl2br(stripslashes($row->message)) : "";

						//subject link
						$subject_cls = "message-link";
						$subject_cls .= ($row->whenseen && $row->whenseen != '0000-00-00 00:00:00') ? "" : " unread";

						$subject  = "<a class=\"{$subject_cls}\" href=\"{$url}\">{$subject}";
						//$subject .= "<div class=\"preview\"><span>" . $preview . "</span></div>";
						$subject .= "</a>";

						//get who the message is from
						if (substr($row->type, -8) == '_message')
						{
							$from = Lang::txt('JANONYMOUS');
							if (!$row->anonymous)
							{
								$u = User::getInstance($row->created_by);
								$from = '<a href="' . Route::url('index.php?option='.$this->option.'&id='.$u->get('id')) . '">' . $u->get('name') . '</a>';
							}
						}
						else
						{
							$from = Lang::txt('PLG_MEMBERS_MESSAGES_SYSTEM', $component);
						}

						//date received
						$date = Date::of($row->created)->toLocal(Lang::txt('DATE_FORMAT_HZ1'));

						//delete link
						$del_link = Route::url($this->member->link() . '&active=messages&mid[]=' . $row->id . '&action=sendtotrash&activetab=archive&' . Session::getFormToken() . '=1');
						$delete = '<a title="' . Lang::txt('PLG_MEMBERS_MESSAGES_REMOVE_MESSAGE') . '" class="trash" href="' . $del_link . '">' . Lang::txt('PLG_MEMBERS_MESSAGES_TRASH') . '</a>';
					?>

					<tr>
						<td class="check"><?php echo $check; ?></td>
						<td class="status"><?php echo $status; ?></td>
						<td><?php echo $subject; ?></td>
						<td><?php echo $from; ?></td>
						<td><?php echo $date; ?></td>
						<td><?php echo $delete; ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else: ?>
				<tr>
					<td colspan="6"><?php echo Lang::txt('PLG_MEMBERS_MESSAGES_NONE'); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<?php echo Html::input('token'); ?>
</form>