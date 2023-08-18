<?php

namespace Strangebrain\Exports\Controller;

use \Bitrix\Main\Engine\Controller;
use \Bitrix\Main\XmlWriter;
use Strangebrain\Exports\Exchanger;

/**
 * Class controller
 * Handle Ajax requests
 */
class Exports extends Controller
{
    public $currencyRates;

    /*
        BITRIX ACTIONS
    */

    /**
     * Execute export action
     *
     * @return void
     */
    public function exportAction()
    {
        $request = $this->getRequest();

        $this->initCurrencies($request);
        $this->initExports($request);
    }

    /**
     * Get dynamic currency rates action
     *
     * @return void
     */
    public function currencyAction()
    {
        $exchanger = new Exchanger();
        $output = $exchanger->getCurrencies(['USD', 'EUR']);

        return $output;
    }

    /*
        INITIALIZATIONS
    */

    /**
     * Initialize currencies logic
     *
     * @param $request - request data from javascript
     * @return void
     */
    private function initCurrencies($request)
    {
        $exportsCurrency = $request->getPost('sbexport_currency');
        if ($exportsCurrency['is_dynamic_currency']) {
            $exchanger = new Exchanger();
            $this->currencyRates = $exchanger->getCurrencies(['USD', 'EUR']);
        } else {
            $usd = $exportsCurrency['usd'] ? $exportsCurrency['usd'] : 1;
            $eur = $exportsCurrency['eur'] ? $exportsCurrency['eur'] : 1;

            $this->currencyRates = array(
                'USD' => ['NAME' => 'Доллар США', 'CODE' => 'USD', 'VALUE' => $usd],
                'EUR' => ['NAME' => 'Доллар США', 'CODE' => 'EUR', 'VALUE' => $eur]
            );
        }
    }

    /**
     * Initialze list of exports and starts them in loop
     *
     * @param $request - request data from javascript
     * @return void
     */
    private function initExports($request)
    {
        $exports = $request->getPost('sbexport');
        foreach ($exports as $export) {
            $this->doSingleExport($export);
        }
    }

    /*
        PARTIAL METHODS
    */
    
    /**
     * Singular export item logic
     * Writing result in xml file
     *
     * @param [array] $exportData - array of request data
     * @return void
     */
    private function doSingleExport($exportData)
    {
        \Bitrix\Main\Loader::includeModule('iblock');

        $xml = new XmlWriter(array(
            'file' => $exportData['path'],
            'create_file' => true,
            'charset' => 'UTF-8',
            'lowercase' => false
        ));

        //prepare options
        $optionsAr = explode(',', $exportData['options']);
        $selectFields = array("ID", "IBLOCK_ID", "PREVIEW_PICTURE", "DETAIL_PAGE_URL", "NAME", "CODE", "IBLOCK_SECTION_ID", "PREVIEW_TEXT");

        foreach ($optionsAr as $opt) {
            $selectFields[] = 'PROPERTY_' . $opt;
        }

        //add price option
        if ($exportData['exchange_option']) {
            $selectFields[] = $exportData['exchange_option'];
        }

        $xml->openFile();
        $xml->writeBeginTag('export');

        $this->addSectionsToXml($exportData, $xml);
        $this->addItemsToXml($exportData, $xml, $selectFields, $optionsAr);

        $xml->writeEndTag('export');
        $xml->closeFile();
    }

    /**
     * Exchange price if necessary
     *
     * @param [array] $exportData - array of request data
     * @param [float] $price - price from iblock
     * @return void
     */
    private function getExchangedPrice($exportData, $price, $currencyCurrent)
    {
        $outCurrency = 'RUB';
            
        if ($exportData['is_exchange'] == 'on' && $price) {

            $outCurrency = $exportData['is_exchange_to'];
            $exTo = $outCurrency;
            $exFrom = $currencyCurrent;

            $exFromRate = $this->currencyRates[$exFrom] ? $this->currencyRates[$exFrom]['VALUE'] : 1;
            $exToRate = $this->currencyRates[$exTo] ? $this->currencyRates[$exTo]['VALUE'] : 1;

            $outputPrice = round(($price / $exToRate) * $exFromRate);

            return [
                'price' => $outputPrice,
                'currency' => $outCurrency   
            ];
        } else {
            return [
                'price' => $price,
                'currency' => $currencyCurrent   
            ];
        }
    }

    /**
     * Write sections in XML file of singular export
     *
     * @param [array] $exportData - array of request data
     * @param [XmlWriter] $xml - instance of xml writer
     * @return void
     */
    private function addSectionsToXml($exportData, $xml)
    {
        $sectionsDB = \CIBlockSection::GetList(
            array("SORT" => "ASC"),
            array("GLOBAL_ACTIVE" => "Y", 'IBLOCK_ID' => $exportData['iblock']),
            false,
            array('IBLOCK_ID', 'ID', 'IBLOCK_SECTION_ID', 'NAME', 'SECTION_ID')
        );

        $xml->writeBeginTag('categories');

        while ($sectionsAr = $sectionsDB->Fetch()) {
            $tag  = 'category ';
            $tag .= 'name="' .$sectionsAr['NAME']. '" ';
            $tag .= 'id="' .$sectionsAr['ID']. '" ';
            $tag .= ($sectionsAr['IBLOCK_SECTION_ID']) ? 'parentId="' .$sectionsAr['IBLOCK_SECTION_ID']. '"' : '';

            $xml->writeFullTag($tag, '');
        }

        $xml->writeEndTag('categories');
    }

    /**
     * Write items amnd options in XML file 
     * of singular export
     *
     * @param [array] $exportData - array of request data
     * @param [XmlWriter] $xml - instance of xml writer
     * @param [array] $selectFields - array of select fields for CIBlockElement::GetList
     * @param [array] $optionsAr - array of options that we need to add in xml export
     * @return void
     */
    private function addItemsToXml($exportData, $xml, $selectFields, $optionsAr) {
        \Bitrix\Main\Loader::includeModule('iblock');

        $elementsDB = \CIBlockElement::GetList(
            array(
                "ID" => "ASC"
            ),
            array(
                "IBLOCK_ID" => $exportData['iblock'],
                "ACTIVE" => "Y",
                "GLOBAL_ACTIVE" => "Y",
            ),
            false,
            false,
            $selectFields
        );

        $xml->writeBeginTag('items');
        
        $lastID = 0;

        while ($obj = $elementsDB->GetNextElement()) {
            $fields = $obj->GetFields();

            //Strange things, if field is multiple 
            //it duplicates... in previos versions
            //don't remember such problems...
            if($lastID == $fields['ID']) continue;
            $lastID = $fields['ID'];

            $props = $obj->GetProperties();

            $propsOutput = [
                'id'            => $fields['ID'],
                'code'          => $fields['CODE'],
                'name'          => $fields['NAME'],
                'preview_text'  => $fields['PREVIEW_TEXT'],
                'url'           => $fields['DETAIL_PAGE_URL'],
                'picture'       => $fields['PREVIEW_PICTURE'] ? \CFile::GetPath($fields['PREVIEW_PICTURE']) : null,
                'section'       => $fields['IBLOCK_SECTION_ID']
            ];

            // Element start
            $xml->writeBeginTag('item');
            $xml->writeItem($propsOutput);

            //write params
            $xml->writeBeginTag('params');
            foreach ($props as $prop) {

                if (!in_array($prop['CODE'], $optionsAr)) continue;

                //FILES
                if($prop['PROPERTY_TYPE'] == "F") {
                    if(is_array($prop['VALUE'])) {
                        $value = [];

                        foreach($prop['VALUE'] as $val) {
                            $value[] = \CFile::GetPath($val);
                        }

                        $value = htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE));
                    } else {
                        $value = \CFile::GetPath($prop['VALUE']);
                    }

                //DOCS BY RELATION
                } elseif ($prop['CODE'] == 'MODEL_DOWNLOADS_DOC') {

                    $xml->writeBeginTag('model_downloads_doc');
                    foreach($prop['VALUE'] as $id) {
                        $db_props = \CIBlockElement::GetProperty(117, $id, array("SORT" => "ASC"), array("CODE"=>'FILE'));
                        $doc_el = \CIBlockElement::GetByID($id)->Fetch();

                        if($ar_props = $db_props->Fetch()) {
                            if(isset($ar_props['VALUE'])) {

                                $docName = isset($doc_el['NAME']) ? $doc_el['NAME'] : null;
                                $url = \CFile::GetPath($ar_props['VALUE']);

                                $tag  = 'doc ';
                                $tag .= 'name="' . $docName . '" ';
                                $tag .= 'url="'. $url . '" ';

                                $xml->writeFullTag($tag, '');
                            }
                        }
                    }
                    $xml->writeEndTag('model_downloads_doc');
                
                //LOCOLIZED
                } elseif (($prop['USER_TYPE'] == 'SB_LOCOLIZED_STRING') || ($prop['USER_TYPE'] == 'SB_LOCOLIZED_HTML')) {

                    if(is_array($prop['VALUE'])) {
                        $valueInner = [];
                        $valCount = 0;
                        
                        foreach($prop['VALUE'] as $val) {
                            if( is_string($val['TEXT']) ) {
                                $valueInner[] = $val['TEXT'];
                            }
                        }

                        $valCount = count($valueInner);
                        $value = htmlspecialchars(json_encode($valueInner, JSON_UNESCAPED_UNICODE));

                    } else {
                        $value = $prop['VALUE'];
                    }

                    if(is_array($prop['DESCRIPTION'])) {
                        $description = array_slice($prop['DESCRIPTION'], 0, $valCount);
                        $description = htmlspecialchars(json_encode($description, JSON_UNESCAPED_UNICODE));
                    }

                } else {
                    $value = is_array($prop['VALUE'])
                        ? (count($prop['VALUE']) ? htmlspecialchars(json_encode($prop['VALUE'], JSON_UNESCAPED_UNICODE)) : '')
                        : $prop['VALUE'];
                }
                
                //TAG PREPARING
                if($prop['CODE'] != 'MODEL_DOWNLOADS_DOC') {
                    $tag  = 'param ';
                    $tag .= 'code="' . $prop['CODE'] . '" ';
                    $tag .= 'name="' . $prop['NAME'] . '" ';
                    $tag .= 'value="' . $value . '" ';
    
                    if( ($prop['USER_TYPE'] == 'SB_LOCOLIZED_STRING') || ($prop['USER_TYPE'] == 'SB_LOCOLIZED_HTML') ) {
                        $tag .= 'description="' . $description . '" ';
                        $tag .= (($prop['USER_TYPE'] == 'SB_LOCOLIZED_STRING') || ($prop['USER_TYPE'] == 'SB_LOCOLIZED_HTML')) ? ' localized="1" ' : ' ';
                    }
    
                    $tag .= ($prop['MULTIPLE'] == 'Y') ? 'multiple="1"' : '';
    
                    $xml->writeFullTag($tag, '');
                }
            }
            $xml->writeEndTag('params');

            //PRICE EXCHANGE
            $price = $props[$exportData['exchange_option']]['VALUE'] 
                ? $props[$exportData['exchange_option']]['VALUE']
                : 0;

            $currencyInOtions = $props[$exportData['currency_option']]['VALUE'] 
                ? $props[$exportData['currency_option']]['VALUE']
                : (($exportData['is_exchange_to']) ? $exportData['is_exchange_to'] : 'RUB');
            
            $priceData = $this->getExchangedPrice($exportData, $price, $currencyInOtions);
            $xml->writeItem(['price' => $priceData['price']]);
            $xml->writeItem(['currency' => $priceData['currency']]);

            // Element end
            $xml->writeEndTag('item');
        }
        $xml->writeEndTag('items');
    }
}
