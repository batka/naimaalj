<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <?php if ($success) { ?>
  <div class="success"><?php echo $success; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <a href="<?=$this->url->link('taoapi/down_category', '&token=' . $this->session->data['token'], 'SSL'); ?>"><h1><img src="view/image/country.png" alt="" /> <?php echo $heading_title; ?></h1></a>
    </div>
    <div class="content">
      <?php include_once(ROOT_PATH.'/taoapi/category/index.php'); ?>
    </div>
  </div>
</div>
<?php echo $footer; ?>