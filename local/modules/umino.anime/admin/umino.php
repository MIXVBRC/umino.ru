<? use Umino\Anime\Core;
use Umino\Anime\Shikimori\Manager;

setlocale(LC_ALL, 'ru_RU.utf8');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule("iblock");
IncludeModuleLangFile(__FILE__);
CJSCore::Init(array("jquery"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

/** @global CMain $APPLICATION */
global $APPLICATION;

if($_REQUEST && check_bitrix_sessid()) {
    if ($_REQUEST['form_priority']) {
        $typesPriority = json_decode($_REQUEST['priority'], true);
        Core::setImportTypesPriority($typesPriority);
    }
}

$aTabs = [
    [
        "DIV" => "priority",
        "TAB" => 'Приоритеты выгрузки',
        "TITLE" => 'Приоритеты выгрузки'
    ],
];

$tabControl = new CAdminTabControl("tabControl", $aTabs);

?>

<? if (!empty($errors)): ?>
    <div class="adm-info-message-wrap adm-info-message-red">
        <div class="adm-info-message">
            <div class="adm-info-message-title">Ошибка</div>
            <? foreach($errors as $error): ?>
                <?= $error ?><br>
            <? endforeach ?>
            <div class="adm-info-message-icon"></div>
        </div>
    </div>
<? endif ?>

<?$tabControl->Begin();?>

<?$tabControl->BeginNextTab();?>

<tr>
    <td>
        <form method="post" action="<?= $APPLICATION->GetCurPage() ?>" enctype="multipart/form-data">
            <?= bitrix_sessid_post() ?>

            <?
            $types = Core::getImportTypesPriority();
            if (empty($types)) {
                $types = Manager::getTypes();
                $num = 0;
                foreach ($types as &$type) {
                    $type = $num;
                    $num++;
                }
            }

            ?>

            <div>
                <? foreach ($types as $type => $priority): ?>
                    <div draggable="true" data-drag-n-drop data-umino-type="<?= $type ?>" data-umino-priority="<?= $priority ?>"><?= $type ?></div>
                <? endforeach; ?>
            </div>

            <input type="hidden" data-priority-input name="priority" value="1">

            <style>
                [data-umino-types] {
                    display: grid;
                    grid-template-columns: 1fr;
                    gap: 15px;
                }

                [data-umino-type] {
                    border: 2px solid #666;
                    background-color: #ddd;
                    border-radius: .5em;
                    padding: 5px;
                    cursor: move;
                    width: 150px;
                }

                [data-umino-type].over {
                    border: 2px dotted #666;
                }
            </style>

            <script>
                document.addEventListener('DOMContentLoaded', (event) => {

                    let items = document.querySelectorAll('[data-drag-n-drop]');

                    function setTypesPriority() {
                        let priority = {};
                        items.forEach(function(item) {
                            priority[item.dataset.uminoType] = item.dataset.uminoPriority;
                        });
                        console.log(JSON.stringify(priority));
                        document.querySelector('[data-priority-input]').value = JSON.stringify(priority);
                    }

                    setTypesPriority();

                    function handleDrop(e) {
                        e.stopPropagation(); // stops the browser from redirecting.

                        if (dragSrcEl !== this) {
                            dragSrcEl.innerHTML = this.innerHTML;
                            this.innerHTML = e.dataTransfer.getData('text/html');

                            let uminoType = this.dataset.uminoType;
                            this.dataset.uminoType = dragSrcEl.dataset.uminoType;
                            dragSrcEl.dataset.uminoType = uminoType;

                            setTypesPriority();
                        }

                        return false;
                    }

                    function handleDragStart(e) {
                        this.style.opacity = '0.4';

                        dragSrcEl = this;

                        e.dataTransfer.effectAllowed = 'move';
                        e.dataTransfer.setData('text/html', this.innerHTML);
                    }

                    function handleDragEnd(e) {
                        this.style.opacity = '1';

                        items.forEach(function (item) {
                            item.classList.remove('over');
                        });
                    }

                    function handleDragOver(e) {
                        e.preventDefault();
                        return false;
                    }

                    function handleDragEnter(e) {
                        this.classList.add('over');
                    }

                    function handleDragLeave(e) {
                        this.classList.remove('over');
                    }

                    items.forEach(function(item) {
                        item.addEventListener('dragstart', handleDragStart);
                        item.addEventListener('dragover', handleDragOver);
                        item.addEventListener('dragenter', handleDragEnter);
                        item.addEventListener('dragleave', handleDragLeave);
                        item.addEventListener('dragend', handleDragEnd);
                        item.addEventListener('drop', handleDrop);
                    });
                });
            </script>

            <br>
            <br>
            <input class="adm-btn" type="submit" name="form_priority" value="Сохранить" title="Сохранить">
        </form>
    </td>
</tr>

<?$tabControl->Buttons();?>

<?$tabControl->End();?>

<?= BeginNote() ?>
<span class="required">*</span> <?= "Какой-то текст"?>
<?= EndNote() ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
