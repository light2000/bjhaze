<h2><?php echo isset($message) ? $message : mb_convert_encoding($exception->getMessage(), 'utf8', 'gbk');?></h2>
<?php if (!isset($message)):?>
<h4>
<?php echo nl2br($exception->getTraceAsString()) ;?>
</h4>
<?php endif;?>