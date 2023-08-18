<?php 
namespace Strangebrain\Exports;

use Bitrix\Main\XML;
use Bitrix\Main\Web\HttpClient;

/**
 * Class for getting actial 
 * exchange from remote source
 */
class Exchanger {
    private $method = 'GET';
    private $arRate;
    public $path;

    function __construct($path='https://www.cbr.ru/scripts/XML_daily.asp') {
        $this->path = $path;
    }
    
    /**
     * Get actual currencies from opened api (cbr)
     *
     * @param [array] $currencyCodes - not required, if set retuns only special currencies
     * @return void
     */
    public function getCurrencies( $currencyCodes=[] ) {
        $httpClient = new HttpClient();
        $httpClient->setHeader('Content-Type', 'application/xml; charset=UTF-8', true);
        $httpClient->query($this->method, $this->path, $entityBody = null);
        $content = $httpClient->getResult();

        $output = $this->parseXML($content, $currencyCodes);

        return $output;
    }

    /**
     * Parse XML by using Bitrix \CDataXML
     *
     * @param [array|null] $data - request data
     * @param array $currencyCodes
     * @return void
     */
    private function parseXML($data, $currencyCodes=[]) {
        if(!$data) return;

        $arCurrency = [];
        
        $xml = new \CDataXML();
        $xml->LoadString($data);
        $node = $xml->GetArray();

        if ($node = $xml->SelectNodes('/ValCurs')) {
            foreach ($node->children() as $arTabNode) {
                $id = $arTabNode->getAttribute('ID');
                
                foreach($arTabNode->children() as $el){
                    $arCurrency[$id][$el->name()] = iconv("windows-1251", "UTF-8", $el->textContent());
                }
            }
        }

        foreach($arCurrency as $val) {
            if(count($currencyCodes)) {
                if( in_array($val['CharCode'], $currencyCodes) ) {
                    $rates[$val['CharCode']] = array(
                        'NAME' => $val['Name'],
                        'CODE' => $val['CharCode'],
                        'VALUE' => str_replace(',', '.',$val['Value']),
                    );
                }
            } else {
                $rates[$val['CharCode']] = array(
                    'NAME' => $val['Name'],
                    'CODE' => $val['CharCode'],
                    'VALUE' => str_replace(',', '.',$val['Value']),
                );
            }
        }

        $this->arRate = $rates;

        return $rates;
    }
}