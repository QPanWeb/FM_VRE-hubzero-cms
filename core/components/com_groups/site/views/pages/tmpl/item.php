<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();
$cls = '';
$style = '';
if ($this->category !== null)
{
	$cls .= ' category-' . $this->page->get('category');
	$this->css('.category-' . $this->page->get('category') . '{ border-left-color: #' .  $this->category->get('color') . '; }');
}
if (isset($this->version) && $this->version->get('approved') == 0)
{
	$cls .= ' not-approved';
}
?>
<div class="item-container <?php echo $cls; ?>">
	<div class="item-title">
		<?php if ($this->page->get('privacy') == 'members') : ?>
			<span class="icon-lock tooltips" title="<?php echo Lang::txt('COM_GROUPS_PAGES_PAGE_PRIVATE'); ?>"></span>
		<?php endif; ?>
		<a href="<?php echo Route::url('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=pages&task=edit&pageid='.$this->page->get('id')); ?>">
			<?php echo $this->page->get('title'); ?>
		</a>
	</div>

	<div class="item-sub" >
		<span tabindex="-1"><?php echo $this->page->url(); ?></span>
	</div>

	<?php if ($this->checkout) : ?>
		<div class="item-checkout">
			<?php $user = User::getInstance($this->checkout->userid); ?>
			<img width="15" src="<?php echo $user->picture(); ?>" />
			<?php echo Lang::txt('COM_GROUPS_PAGES_PAGE_CHECKED_OUT', $user->get('id'), $user->get('name')); ?>
		</div>
	<?php endif; ?>

	<?php if (isset($this->version) && $this->version->get('approved') == 0) : ?>
		<div class="item-approved">
			<?php echo Lang::txt('COM_GROUPS_PAGES_PAGE_PENDING_APPROVAL'); ?>
		</div>
	<?php endif; ?>

	<?php if ($this->page->get('home') == 0) : ?>
		<div class="item-state">
			<?php if ($this->page->get('state') == 0) : ?>
				<a class="unpublished tooltips" title="<?php echo Lang::txt('COM_GROUPS_PAGES_PUBLISH_PAGE'); ?>" href="<?php echo Route::url('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=pages&task=publish&pageid='.$this->page->get('id')); ?>"><?php echo Lang::txt('COM_GROUPS_PAGES_PUBLISH_PAGE'); ?></a>
			<?php else : ?>
				<a class="published tooltips" title="<?php echo Lang::txt('COM_GROUPS_PAGES_UNPUBLISH_PAGE'); ?>" href="<?php echo Route::url('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=pages&task=unpublish&pageid='.$this->page->get('id')); ?>"><?php echo Lang::txt('COM_GROUPS_PAGES_UNPUBLISH_PAGE'); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="item-preview">
		<a class="tooltips page-preview" title="<?php echo Lang::txt('COM_GROUPS_PAGES_PREVIEW_PAGE'); ?>" href="<?php echo Route::url('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=pages&task=preview&pageid='.$this->page->get('id')); ?>"><?php echo Lang::txt('COM_GROUPS_PAGES_PREVIEW_PAGE'); ?></a>
	</div>

	<div class="item-controls btn-group dropdown">
		<a href="<?php echo Route::url('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=pages&task=edit&pageid='.$this->page->get('id')); ?>" class="btn"><?php echo Lang::txt('COM_GROUPS_PAGES_MANAGE_PAGE'); ?></a>
		<span class="btn dropdown-toggle"></span>
		<ul class="dropdown-menu">
			<li><a class="icon-edit" href="<?php echo Route::url('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=pages&task=edit&pageid='.$this->page->get('id')); ?>"> <?php echo Lang::txt('COM_GROUPS_PAGES_EDIT_PAGE_BACK'); ?></a></li>
			<li><a class="icon-search page-preview" href="<?php echo Route::url('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=pages&task=preview&pageid='.$this->page->get('id')); ?>"> <?php echo Lang::txt('COM_GROUPS_PAGES_PREVIEW_PAGE'); ?></a></li>
			<?php if ($this->page->get('home') == 0) : ?>
				<?php if ($this->page->get('state') == 0) : ?>
					<li><a class="icon-ban-circle" href="<?php echo Route::url('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=pages&task=publish&pageid='.$this->page->get('id')); ?>"> <?php echo Lang::txt('COM_GROUPS_PAGES_PUBLISH_PAGE'); ?></a></li>
				<?php else : ?>
					<li><a class="icon-success" href="<?php echo Route::url('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=pages&task=unpublish&pageid='.$this->page->get('id')); ?>"> <?php echo Lang::txt('COM_GROUPS_PAGES_UNPUBLISH_PAGE'); ?></a></li>
				<?php endif; ?>
			<?php endif; ?>
			<li class="divider"></li>
			<li><a class="icon-history page-history" href="<?php echo Route::url('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=pages&task=versions&pageid='.$this->page->get('id')); ?>"> <?php echo Lang::txt('COM_GROUPS_PAGES_VERSION_HISTORY_PAGE'); ?></a></li>
			

			<?php if ($this->page->get('home') == 0) : ?>
				<li class="divider"></li>
				<li><a class="icon-delete" href="<?php echo Route::url('index.php?option=com_groups&cn='.$this->group->get('cn').'&controller=pages&task=delete&pageid='.$this->page->get('id')); ?>"> <?php echo Lang::txt('COM_GROUPS_PAGES_DELETE_PAGE'); ?></a></li>
			<?php endif; ?>
		</ul>
	</div>

	<?php if ($this->page->get('home') == 0) : ?>
		<div class="item-mover"></div>
	<?php endif; ?>
</div>
