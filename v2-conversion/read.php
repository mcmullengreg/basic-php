<?php
include('./inc/base.php');
$cms = new Cascade();
$page = $cms->read("e948cdbcac1e04cd3d3a61c2bf5cb51d", "page");

highlight_string(var_export($page["asset"]['page'], true));
