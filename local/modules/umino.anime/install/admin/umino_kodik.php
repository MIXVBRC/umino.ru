<?
$bitrixPath = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/umino.anime/admin/'.basename(__FILE__);
$localPath = $_SERVER['DOCUMENT_ROOT'].'/local/modules/umino.anime/admin/'.basename(__FILE__);
if (file_exists($bitrixPath)) {
    require($bitrixPath);
}
else if (file_exists($localPath)){
    require ($localPath);
}
?>