<!DOCTYPE html>
<html lang="<?php echo $lang->getCode(); ?>" xml:lang="<?php echo $lang->getCode(); ?>">
  <head>
    <title><?php echo $lang->get( 'DEFAULT_PAGE_TITLE' ); ?></title>
    <meta name="description" content="<?php echo $lang->get( 'META_DESCRIPTION' ); ?>" />
    <meta name="keywords" content="<?php echo $lang->get( 'META_KEYWORDS' ); ?>" />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"  />
    <script type="text/javascript">if (top != self) { top.location.replace(self.location.href); }</script>
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_PATH; ?>css/main-<?php echo MAIN_CSS_LATEST_REVISION; ?>.css" />
    <!--[if lte IE 8]>
    <link rel="stylesheet" type="text/css" href="<?php echo BASE_PATH; ?>css/ie8-<?php echo IE8_CSS_LATEST_REVISION; ?>.css" />
    <![endif]-->
<?php

//load additional chinese styles if appropriate
if ($lang->getCode() === 'zh-Hant') {
    echo '<link rel="stylesheet" type="text/css" href="' .  BASE_PATH
         . 'css/cjk-' . CJK_CSS_LATEST_REVISION . '.css" />';

}

?>
    <link rel="shortcut icon" type="image/png" href="<?php echo BASE_PATH; ?>img/favicon.png" />
  </head>
  <body>
    <div id="masthead">
      <div class="centered">
        <a id="logo" href="<?php echo BASE_PATH; ?>" title="Home">
          <h1><?php echo $lang->get( 'DEFAULT_PAGE_TITLE' ); ?></h1>
        </a>
        <div id="compass-container">
          <div id="compass-face"></div>
          <span id="compass-needle"></span>
        </div>
        <div class="clear"></div>
      </div>
    </div>