<? use Umino\Anime\Core;

setlocale(LC_ALL, 'ru_RU.utf8');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule("iblock");
IncludeModuleLangFile(__FILE__);
CJSCore::Init(array("jquery"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/** @global CMain $APPLICATION */
global $APPLICATION;

if($_REQUEST && check_bitrix_sessid()) {
    if ($_REQUEST['form_api']) {
        Core::setShikimoriAPIUrl(trim($_REQUEST['form_api_url']));
    }
}

$aTabs = [
    [
        "DIV" => "api",
        "TAB" => 'API настройки',
        "TITLE" => 'API настройки'
    ],
];

$tabControl = new CAdminTabControl("tabControl", $aTabs);

?>

<?if (!empty($errors)):?>
    <div class="adm-info-message-wrap adm-info-message-red">
        <div class="adm-info-message">
            <div class="adm-info-message-title">Ошибка</div>
            <?foreach($errors as $error):?>
                <?=$error?><br />
            <?endforeach?>
            <div class="adm-info-message-icon"></div>
        </div>
    </div>
<?endif?>

<? $tabControl->Begin(); ?>

<? $tabControl->BeginNextTab(); ?>
<tr>
    <td>
        <form method="post" action="<?= $APPLICATION->GetCurPage() ?>" enctype="multipart/form-data">
            <?= bitrix_sessid_post() ?>

            <table>
                <tr>
                    <td><label for="form_api_url">API ссылка:</label></td>
                    <td><input type="text" size="32" name="form_api_url" id="form_api_url" value="<?= Core::getShikimoriAPIUrl() ?>"></td>
                </tr>
            </table>

            <br><br>
            <input class="adm-btn" type="submit" name="form_api" value="Сохранить" title="Сохранить">
        </form>
    </td>
</tr>

<? $tabControl->Buttons(); ?>
<? $tabControl->End(); ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
