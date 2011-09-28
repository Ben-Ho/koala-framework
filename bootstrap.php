<?php
chdir(dirname(__FILE__).'/tests');
set_include_path(get_include_path().PATH_SEPARATOR.realpath(getcwd().'/..'));

require_once 'Vps/Setup.php';
Vps_Setup::setUp();

if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] == '/') {
    echo Vps_Registry::get('config')->application->vps->name.' '.Vps_Registry::get('config')->application->vps->version;
    exit;
}
Vps_Assets_Loader::load();

$front = Vps_Controller_Front::getInstance();
$response = $front->dispatch();
$response->sendResponse();
