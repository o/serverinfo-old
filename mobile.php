<?php
require_once('config.php');
$view = new MobileView;
$view->viewhead();
if ($view->info->shellenabled) {
    $view->viewheader();
    $view->viewuptime();
    $view->viewload();
    $view->viewmemory();
    $view->viewdisks();
}
if ($view->info->sockenabled) {
    $view->viewcheckedports();
}
if ($view->info->apcenabled) {
    $view->viewapcstats();
}
$view->viewfooter();