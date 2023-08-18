<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;

Loc::loadMessages(__FILE__);

class strangebrain_exports extends CModule
{

    public function __construct()
    {
        if (is_file(__DIR__ . '/version.php')) {
            include_once(__DIR__ . '/version.php');
            $this->MODULE_ID           = pathinfo(dirname(__DIR__))['basename'];
            $this->MODULE_VERSION      = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
            $this->MODULE_NAME         = Loc::getMessage('STRANGEBRAIN_MODULE_NAME');
            $this->PARTNER_NAME        = Loc::getMessage('STRANGEBRAIN_PARTNER_NAME');
            $this->PARTNER_URI         = Loc::getMessage('STRANGEBRAIN_PARTNER_URI');
            $this->MODULE_DESCRIPTION  = Loc::getMessage('STRANGEBRAIN_MODULE_DESCRIPTION');
        } else {
            CAdminMessage::ShowMessage(
                Loc::getMessage('STRANGEBRAIN_NO_VERSION')
            );
        }
    }

    public function DoInstall()
    {
        global $APPLICATION;

        if (CheckVersion(ModuleManager::getVersion('main'), '14.00.00')) {
            $this->InstallFiles();
            $this->InstallDB();
            ModuleManager::registerModule($this->MODULE_ID);
            $this->InstallEvents();
        } else {
            CAdminMessage::ShowMessage(
                Loc::getMessage('STRANGEBRAIN_MODULE_ERROR')
            );
            return;
        }

        return;
    }

    public function InstallFiles()
    {
        return;
    }

    public function InstallDB()
    {
        return;
    }

    public function InstallEvents()
    {
        EventManager::getInstance()->registerEventHandler(
            "main",
            "OnBeforeEndBufferContent",
            $this->MODULE_ID,
            "Strangebrain\Exports\Main",
            "appendScriptsToPage"
        );

        return;
    }

    public function DoUninstall()
    {
        global $APPLICATION;

        $this->UnInstallFiles();
        $this->UnInstallDB();
        $this->UnInstallEvents();

        ModuleManager::unRegisterModule($this->MODULE_ID);
        return;
    }

    public function UnInstallFiles()
    {
        Option::delete($this->MODULE_ID);

        return;
    }

    public function UnInstallDB()
    {
        return;
    }

    public function UnInstallEvents()
    {
        EventManager::getInstance()->unRegisterEventHandler(
            "main",
            "OnBeforeEndBufferContent",
            $this->MODULE_ID,
            "Strangebrain\Exports\Main",
            "appendScriptsToPage"
        );

        return;
    }
}
