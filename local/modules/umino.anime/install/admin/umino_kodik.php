<?setlocale(LC_ALL, 'ru_RU.utf8');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule("iblock");
IncludeModuleLangFile(__FILE__);
CJSCore::Init(array("jquery"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/** @global CMain $APPLICATION */
global $APPLICATION;

if($_REQUEST['param'] && check_bitrix_sessid()) {
    // Здесь обработка форм
}

$aTabs = array(
    array("DIV" => "edit1", "TAB" => GetMessage("SITECORE_HOMEPAGE_TAB1_HEAD"), "TITLE" => GetMessage("SITECORE_HOMEPAGE_TAB1_HEAD")),
    array("DIV" => "edit2", "TAB" => GetMessage("SITECORE_HOMEPAGE_TAB2_HEAD"), "TITLE" => GetMessage("SITECORE_HOMEPAGE_TAB2_HEAD")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

?>

<?if (!empty($arErrors)):?>
    <div class="adm-info-message-wrap adm-info-message-red">
        <div class="adm-info-message">
            <div class="adm-info-message-title">Ошибка</div>
            <?foreach($arErrors as $error):?>
                <?=$error?><br />
            <?endforeach?>
            <div class="adm-info-message-icon"></div>
        </div>
    </div>
<?endif?>

<?$tabControl->Begin();?>

<?$tabControl->BeginNextTab();?>
<tr><td>
        <form method="post" action="<?echo $APPLICATION->GetCurPage()?>" enctype="multipart/form-data" name="post_form" id="post_form">
            <?echo bitrix_sessid_post();?>
            Форма первого таба
            <br/><br/>
            <input class="adm-btn" type="submit" name="Update_tab1" value="<?=GetMessage("SITECORE_HOMEPAGE_SUBMIT"); ?>" title="<?=GetMessage("SITECORE_HOMEPAGE_SUBMIT"); ?>">
        </form>
    </td></tr>

<?$tabControl->BeginNextTab();?>
<tr><td>
        <form method="post" action="<?echo $APPLICATION->GetCurPage()?>" enctype="multipart/form-data" name="post_form2" id="post_form2">
            <?echo bitrix_sessid_post();?>
            Форма второго таба
            <br/><br/>
            <input class="adm-btn" type="submit" name="Update_tab2" value="<?=GetMessage("SITECORE_HOMEPAGE_SUBMIT"); ?>" title="<?=GetMessage("SITECORE_HOMEPAGE_SUBMIT"); ?>">
        </form>
    </td></tr>

<?$tabControl->Buttons();?>
<?$tabControl->End();?>

<?echo BeginNote();?>
<span class="required">*</span> <?echo GetMessage("SITECORE_HOMEPAGE_REQUIRED_FIELDS")?>
<?echo EndNote();?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
