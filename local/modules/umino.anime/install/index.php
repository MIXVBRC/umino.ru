<?

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

Class umino_anime extends CModule
{
    public $MODULE_ID;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_CSS;
    public $MODULE_GROUP_RIGHTS = 'Y';
    public $MODULE_PATH;

    public $connection;

    public function __construct()
    {
        $this->MODULE_ID = $this->getModuleID();
        $this->MODULE_NAME = Loc::getMessage("UMINO_ANIME_INSTALL_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("UMINO_ANIME_INSTALL_DESCRIPTION");
        $this->MODULE_PATH = $this->getModulePath();

        $arModuleVersion = [];
        include __DIR__ . '/version.php';

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $this->connection = Application::getConnection();
    }

    private function getModulePath(): string
    {
        $explode = explode('/', __FILE__);
        $arraySlice = array_slice($explode, 0, array_search($this->MODULE_ID, $explode) + 1);
        return implode('/', $arraySlice);
    }

    public function getModuleID() {
        return str_ireplace('_', '.', get_class($this));
    }

    function InstallDB(): bool
    {
        $sqlBatch = file_get_contents($this->MODULE_PATH . '/install/db/mysql/install.sql');
        $sqlBatchErrors = $this->connection->executeSqlBatch($sqlBatch);
        if (sizeof($sqlBatchErrors) > 0) {
            return false;
        }
        return true;
    }

    function UnInstallDB(): bool
    {
        $sqlBatch = file_get_contents($this->MODULE_PATH . '/install/db/mysql/uninstall.sql');
        $sqlBatchErrors = $this->connection->executeSqlBatch($sqlBatch);
        if (sizeof($sqlBatchErrors) > 0) {
            return false;
        }
        return true;
    }

    function InstallEvents(): bool
    {
        RegisterModuleDependences("main", "OnBuildGlobalMenu", $this->MODULE_ID, "\Umino\Anime\Core", "OnBuildGlobalMenuHandler");

        return true;
    }

    function UnInstallEvents(): bool
    {
        UnRegisterModuleDependences("main", "OnBuildGlobalMenu", $this->MODULE_ID, "\Umino\Anime\Core", "OnBuildGlobalMenuHandler");

        return true;
    }

    function InstallFiles(): bool
    {
        CopyDirFiles($this->MODULE_PATH . '/install/themes', getenv('DOCUMENT_ROOT') . '/bitrix/themes', true, true);
        CopyDirFiles($this->MODULE_PATH . '/install/images', getenv('DOCUMENT_ROOT') . '/bitrix/images', true, true);
        CopyDirFiles($this->MODULE_PATH . '/install/admin', getenv('DOCUMENT_ROOT') . '/bitrix/admin', true, true);

        return true;
    }

    function UnInstallFiles(): bool
    {
        DeleteDirFiles($this->MODULE_PATH . '/install/admin', getenv('DOCUMENT_ROOT') . '/bitrix/admin');
        DeleteDirFiles($this->MODULE_PATH . '/install/themes/.default', getenv('DOCUMENT_ROOT') . '/bitrix/themes/.default');
        DeleteDirFilesEx('/bitrix/images/'.$this->MODULE_ID.'/');

        return true;
    }

    function DoInstall()
    {
        RegisterModule($this->MODULE_ID);

        $this->InstallFiles();
        $this->InstallEvents();
        $this->InstallDB();
    }

    function DoUninstall()
    {
        $this->UnInstallDB();
        $this->UnInstallEvents();
        $this->UnInstallFiles();

        UnRegisterModule($this->MODULE_ID);
    }
}
?>