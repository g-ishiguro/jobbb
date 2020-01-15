<?php
ini_set('display_errors', 1);
ini_set('extension', 'php_fileinfo.dll');
ini_set('extension', 'php_gd2.dll');
ini_set("date.timezone", "Asia/Tokyo");

require_once('htmlspecialchars.php');
require_once('autoload.php');
require_once('phpQuery-onefile.php');
require_once('../vendor/autoload.php');

const JOBCAN_URL = 'https://ssl.jobcan.jp/m/work/conditions?code=';
const TEMP_XLSX_PATH = __DIR__ . '/../xlsx/temp.xlsx';
const SITE_URL = __DIR__ . '/../public/index.php';

session_start();