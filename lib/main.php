<?php 
namespace Strangebrain\Exports;

use Bitrix\Main\Page\Asset;

class Main {
    
    /**
     * Initialize function
     *
     * @return void
     */
    public function appendScriptsToPage() {
        $module_id = pathinfo(dirname(__DIR__))['basename'];
        $base_path = '/local/modules/'. $module_id .'/assets';

        if (defined('ADMIN_SECTION') && ADMIN_SECTION == true) { 
            Asset::getInstance()->addString('<link rel="stylesheet" type="text/css" href="'. $base_path .'/styles/style.css' .'">');
            Asset::getInstance()->addJs( $base_path .'/scripts/script.js');
        }
    }
}