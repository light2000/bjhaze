<h2><?php echo defined('BJHAZE_DEBUG') ? $exception->getMessage() : (isset($message) ? $message : 'system error');?></h2>
<?php if (defined('BJHAZE_DEBUG')):?>
<h4>
<?php echo nl2br($exception->getTraceAsString()) ;?>
</h4>
<?php endif;?>