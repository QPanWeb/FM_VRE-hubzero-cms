<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$i = 1;

$dateFormat = '%d %b %Y';
$tz = 0;

if (version_compare(JVERSION, '1.6', 'ge'))
{
	$dateFormat = 'd M Y';
	$tz = false;
}
?>
<header id="content-header">
	<h2><?php echo $this->title; ?></h2>

	<div class="status-msg">
	<?php 
		// Display error or success message
		if ($this->getError()) { 
			echo ('<p class="witherror">' . $this->getError().'</p>');
		}
	?>
	</div>
</header><!-- / #content-header -->

<section id="introduction" class="section">
	<div id="introbody">
	<?php if ($this->contributable) { ?>
		<div class="grid">
			<div class="col span4">
	<?php } ?>
				<h3><?php echo JText::_('COM_PUBLICATIONS_BROWSE_PUBLICATIONS'); ?></h3>
				<ul class="extracontent">
				<?php foreach ($this->categories as $cat) { 
					if ($cat->itemCount <= 0) { continue; }
					if ($i > 3) { break; } ?>
					<li class="<?php echo $cat->alias; ?>"><a href="<?php echo JRoute::_('index.php?option=com_publications'. a . 'category=' . $cat->url_alias); ?>"><?php echo JText::_('COM_PUBLICATIONS_BROWSE') . ' ' . $cat->name; ?> &raquo;</a></li>
				<?php $i++; } ?>
					<li class="browseall"><a href="<?php echo JRoute::_('index.php?option=com_publications'.a.'task=browse'); ?>"><?php echo JText::_('COM_PUBLICATIONS_BROWSE_ALL'); ?> &raquo;</a></li>
				</ul>

	<?php if ($this->contributable) { ?>
			</div>
			<div class="col span8 omega">
				<h3><?php echo JText::_('COM_PUBLICATIONS_WHO_CAN_SUBMIT'); ?></h3>
				<p><?php echo JText::_('COM_PUBLICATIONS_WHO_CAN_SUBMIT_ANYONE'); ?></p>
				<p><a href="<?php echo JRoute::_('index.php?option=com_publications'.a.'task=submit'); ?>"><?php echo JText::_('COM_PUBLICATIONS_START_PUBLISHING'); ?> &raquo;</a></p>
			</div>
		</div>
	<?php } ?>
	</div>
</section><!-- / #introduction.section -->

<section class="section">
	<div class="grid">
		<div class="col span6">
			<h3><?php echo JText::_('Recent Publications'); ?></h3>
			<?php if($this->results && count($this->results) > 0) { ?>
				<ul class="mypubs">
					<?php foreach($this->results as $row) {
						// Get version authors
						$pa = new PublicationAuthor( $this->database );
						$authors = $pa->getAuthors($row->version_id); 
						$info = array();
						$info[] =  JHTML::_('date', $row->published_up, $dateFormat, $tz);	
						$info[] = $row->cat_name;	
						$info[] = JText::_('COM_PUBLICATIONS_CONTRIBUTORS').': '. $this->helper->showContributors( $authors, false, true );
						
						$pubthumb = $this->helper->getThumb($row->id, $row->version_id, $this->config, false, $row->cat_url);			
					?>
					<li>
						<span class="pub-thumb"><img src="<?php echo $pubthumb; ?>" alt=""/></span>
						<span class="pub-details">
							<a href="<?php echo JRoute::_('index.php?option=com_publications'.a.'id='.$row->id); ?>" title="<?php echo stripslashes($row->abstract); ?>"><?php echo \Hubzero\Utility\String::truncate(stripslashes($row->title), 100); ?></a>
							<span class="block details"><?php echo implode(' <span>|</span> ', $info); ?></span>
						</span>
					</li>	
					<?php }?>
				</ul>
			<?php } else { 
				echo ('<p class="noresults">'.JText::_('COM_PUBLICATIONS_NO_RELEVANT_PUBS_FOUND').'</a></p>'); 
			} ?>
		</div>
		<div class="col span6 omega">
			<h3><?php echo JText::_('Popular Publications'); ?></h3>
			<?php if ($this->best && count($this->best) > 0) { ?>
				<ul class="mypubs">
					<?php foreach($this->best as $row) {
						// Get version authors
						$pa = new PublicationAuthor( $this->database );
						$authors = $pa->getAuthors($row->version_id); 
						$info = array();
						$info[] =  JHTML::_('date', $row->published_up, $dateFormat, $tz);	
						$info[] = $row->cat_name;	
						$info[] = JText::_('COM_PUBLICATIONS_CONTRIBUTORS').': '. $this->helper->showContributors( $authors, false, true );
						
						$pubthumb = $this->helper->getThumb($row->id, $row->version_id, $this->config, false, $row->cat_url);			
					?>
					<li>
						<span class="pub-thumb"><img src="<?php echo $pubthumb; ?>" alt=""/></span>
						<span class="pub-details">
							<a href="<?php echo JRoute::_('index.php?option=com_publications'.a.'id='.$row->id); ?>" title="<?php echo stripslashes($row->abstract); ?>"><?php echo \Hubzero\Utility\String::truncate(stripslashes($row->title), 100); ?></a>
							<span class="block details"><?php echo implode(' <span>|</span> ', $info); ?></span>
						</span>
					</li>	
					<?php }?>
				</ul>
			<?php } else { 
				echo ('<p class="noresults">'.JText::_('COM_PUBLICATIONS_NO_RELEVANT_PUBS_FOUND').'</a></p>'); 
			} ?>
		</div>
	</div>
</section><!-- / .section -->
