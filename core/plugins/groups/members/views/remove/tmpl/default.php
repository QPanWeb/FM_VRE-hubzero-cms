<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();
?>
<form action="<?php echo Route::url('index.php?option='.$this->option.'&cn='.$this->group->get('cn').'&active=members'); ?>" method="post" id="hubForm">
	<div class="explaination">
		<p class="info"><?php echo Lang::txt('PLG_GROUPS_MEMBERS_REMOVE_EXPLANATION'); ?></p>
	</div>
	<fieldset>
		<legend><?php echo Lang::txt('PLG_GROUPS_MEMBERS_REMOVE_MEMBERSHIP'); ?></legend>

		<?php
		$names = array();
		foreach ($this->users as $user)
		{
			$u = User::getInstance($user);
			$names[] = $this->escape($u->get('name'));
			?>
			<input type="hidden" name="users[]" value="<?php echo $this->escape($user); ?>" />
			<?php
		}
		?>

		<label>
			<?php echo Lang::txt('PLG_GROUPS_MEMBERS_REMOVE_USERS'); ?><br />
			<strong><?php echo implode(', ', $names); ?></strong>
		</label>

		<label>
			<?php echo Lang::txt('PLG_GROUPS_MEMBERS_REMOVE_REASON'); ?>
			<textarea name="reason" id="reason" rows="12" cols="50"></textarea>
		</label>
	</fieldset><div class="clear"></div>

	<input type="hidden" name="cn" value="<?php echo $this->group->get('cn'); ?>" />
	<input type="hidden" name="active" value="members" />
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="action" value="confirmremove" />

	<p class="submit">
		<input type="submit" value="<?php echo Lang::txt('PLG_GROUPS_MEMBERS_SUBMIT'); ?>" />
	</p>
</form>
