<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);
$request = HttpApplication::getInstance()->getContext()->getRequest();
$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);
$option_export = 'sbexport';
$option_cyrrency = 'sbexport_currency';
Loader::includeModule($module_id);

if ($request->isPost() && check_bitrix_sessid()) {
    $value_export = $request->getPost($option_export);
    Option::set($module_id, $option_export, json_encode($value_export));

    $value_cyrrency = $request->getPost($option_cyrrency);
    Option::set($module_id, $option_cyrrency, json_encode($value_cyrrency));
}

$currenciesList = ['RUB', 'USD', 'EUR'];

?>

<div class="sbexport__message js-sbexport-message">
    <div class="sbexport__mesinner js-sbexport-message-text"></div>
</div>

<form class="sbexport__form js-sbexport-message-form" action="<? echo ($APPLICATION->GetCurPage()); ?>?mid=<? echo ($module_id); ?>&lang=<? echo (LANG); ?>" method="post">
    <?= bitrix_sessid_post(); ?>
    
    <div class="adm-detail-content-wrap">
        <div class="adm-detail-content-item-block">
            <div class="adm-detail-title">
                <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_MAIN_TITLE') ?>
            </div>

            <button 
                type="button" 
                class="sbexport__addbtn sbexport__inlinebtn js-sbexport-do-export"
                >
                <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_WITHOUT_QUEUE') ?>
            </button>

            <? // CURRENCY ?>
            <h3 class="sbexport__heading">
                <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_CURRENCY_TITLE') ?>
            </h3>

            <div class="adm-detail-content-table edit-table">
                <?
                    $currenciesOptions = Option::get($module_id, $option_cyrrency);
                    $currenciesOptions = $currenciesOptions ? json_decode($currenciesOptions) : null;
                    
                    $isDynamicCurrency = ( isset($currenciesOptions->is_dynamic_currency) && $currenciesOptions->is_dynamic_currency );
                ?>
                <div class="sbexport__fgroup">
                    <div class="sbexport__field">
                        <input 
                            type="checkbox" 
                            id="is_dynamic_currency"
                            class="sbexport__chbx js-sbexport-dynamic-currency-chbx" 
                            name="<?= $option_cyrrency ?>[is_dynamic_currency]" 
                            <? if( $isDynamicCurrency ) echo 'checked="checked"' ?>
                        >
                        <label for="is_dynamic_currency">
                            <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_AUTOEXCHANGE_TITLE') ?>
                        </label>
                    </div>

                    <div class="sbexport__dynamicgroup js-sbexport-dynamic-currency-group <? if(!$isDynamicCurrency) echo 'active' ?>">
                        <div class="sbexport__field">
                            <div class="sbexport__label">
                                <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_RATE_DOLLAR_TITLE') ?>
                            </div>
                            <input 
                                type="number" 
                                pattern="[0-9]+([\.,][0-9]+)?" 
                                step="0.0001"
                                class="sbexport__inp js-sbexport-usd" 
                                name="<?= $option_cyrrency ?>[usd]" 
                                value="<? if(isset($currenciesOptions->usd)) echo $currenciesOptions->usd ?>"
                            >
                        </div>

                        <div class="sbexport__field">
                            <div class="sbexport__label">
                                <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_RATE_EURO_TITLE') ?>
                            </div>
                            <input 
                                type="number" 
                                pattern="[0-9]+([\.,][0-9]+)?" 
                                step="0.0001"
                                class="sbexport__inp js-sbexport-eur" 
                                name="<?= $option_cyrrency ?>[eur]" 
                                value="<? if(isset($currenciesOptions->eur)) echo $currenciesOptions->eur ?>"
                            >
                        </div>

                        <button 
                            type="button" 
                            class="sbexport__addbtn sbexport__inlinebtn js-sbexport-get-currency">
                            <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_ACTUALIZE_RATE') ?>
                        </button>
                    </div>
                </div>
            </div>

            <? // EXPORT OPTIONS ?>
            <h3 class="sbexport__heading">
                <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_EXPORT_HEADING') ?>
            </h3>

            <button type="button" class="sbexport__addbtn sbexport__inlinebtn js-sbexport-addgroup">
                <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_ADD') ?>
            </button>
    
            <div class="adm-detail-content-table edit-table">
                <!-- Fields is here -->
                <div class="js-sbexport-cont sbexport__fcont">
                    <?
                        $settedOptions = Option::get($module_id, $option_export);
                        $opt_counter = 0;

                        $settedOptions = $settedOptions ? json_decode($settedOptions) : null;
                    ?>

                    <? foreach ($settedOptions as $option): ?>
                        <div class="sbexport__fgroup js-sbexport-fgroup">
                            <div class="sbexport__field">
                                <div class="sbexport__label">
                                    <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_IBLOCK_TITLE') ?>
                                </div>
                                <input 
                                    class="sbexport__inp js-sbexport-iblock-inp" 
                                    type="number"
                                    min="1" 
                                    value="<?= $option->iblock?>" 
                                    name="<?= $option_export . '[' . $opt_counter . '][iblock]' ?>" 
                                    placeholder="<?= Loc::getMessage('STRANGEBRAIN_OPTIONS_IBLOCK_PLACEHOLDER') ?>"
                                >
                            </div>
                            
                            <div class="sbexport__field">
                                <div class="sbexport__label">
                                    <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_PATH_TITLE') ?>
                                </div>
                                <input 
                                    class="sbexport__inp js-sbexport-path-inp" 
                                    type="text"
                                    value="<?= $option->path?>" 
                                    name="<?= $option_export . '[' . $opt_counter . '][path]' ?>" 
                                    placeholder="<?= Loc::getMessage('STRANGEBRAIN_OPTIONS_PATH_PLACEHOLDER') ?>"
                                >
                            </div>

                            <div class="sbexport__field">
                                <div class="sbexport__label">
                                    <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_OPTS_TITLE') ?>
                                </div>
                                <textarea
                                    class="sbexport__inp sbexport__inp--area js-sbexport-options-inp" 
                                    rows="5"
                                    name="<?= $option_export . '[' . $opt_counter . '][options]' ?>"
                                    placeholder="<?= Loc::getMessage('STRANGEBRAIN_OPTIONS_OPTS_PLACEHOLDER') ?>"
                                ><?= trim($option->options) ?></textarea>
                            </div>

                            <div class="sbexport__field">
                                <label>
                                    <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_PRICE_TITLE') ?>
                                </label>
                                <input 
                                    class="sbexport__inp sbexport__inp--sm" 
                                    type="text"
                                    value="<?= $option->exchange_option ?>" 
                                    name="<?= $option_export . '[' . $opt_counter . '][exchange_option]' ?>" 
                                    placeholder="<?= Loc::getMessage('STRANGEBRAIN_OPTIONS_PRICE_PLACEHOLDER') ?>"
                                >
                            </div>

                            <div class="sbexport__field">
                                <label>
                                    <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_CURRENCY_FG_TITLE') ?>
                                </label>
                                <input 
                                    class="sbexport__inp sbexport__inp--sm js-sbexport-exchange-option-inp" 
                                    type="text"
                                    name="<?= $option_export . '[' . $opt_counter . '][currency_option]' ?>" 
                                    value="<?= $option->currency_option  ?>"
                                    placeholder=<?= Loc::getMessage('STRANGEBRAIN_OPTIONS_CURRENCY_FG_PLACEHOLDER') ?>"
                                >
                                <div class="sbexport__info">
                                    <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_CURRENCY_FG_DESCR'); ?>
                                </div>
                            </div>

                            <div class="sbexport__field">
                                <input 
                                    type="checkbox" 
                                    id="is_exchange_<?= $opt_counter ?>"
                                    class="sbexport__chbx js-sbexport-exchange-chbx" 
                                    name="<?= $option_export . '[' . $opt_counter . '][is_exchange]' ?>" 
                                    <? if( $option->is_exchange ) echo 'checked="checked"' ?>
                                >
                                <label for="is_exchange_<?= $opt_counter ?>">
                                    <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_DO_EXCHANGE_TITLE') ?>
                                </label>
                            </div>

                            <div class="sbexport__exchangegroup js-sbexport-exchange-group <? if( $option->is_exchange ) echo 'active'?>">
                                <!-- 
                                    Customer asks to delete "exchange from" setting.
                                    Yeah, I know... Now they have to set currency to every elment.
                                    instead of doing convertation just ones... 
                                    However, let's do not delete this code ;)
                                -->
                                <!-- <label>
                                    <?//= Loc::getMessage('STRANGEBRAIN_OPTIONS_PRICE_FROM_TITLE'); ?>
                                </label>
                                <select class="sbexport__select--sm" name="<?//= $option_export . '[' . $opt_counter . '][is_exchange_from]' ?>">
                                    <?// foreach ($currenciesList as $currency):?>
                                        <option 
                                            value="<?//= $currency ?>" 
                                            <?// if($option->is_exchange_from && $option->is_exchange_from == $currency) echo ' selected="selected"' ?>
                                            >
                                            <?//= $currency ?>
                                        </option>
                                    <?// endforeach; ?>
                                </select> -->

                                <label>
                                    <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_PRICE_TO_TITLE') ?>
                                </label>
                                <select class="sbexport__select--sm" name="<?= $option_export . '[' . $opt_counter . '][is_exchange_to]' ?>">
                                    <? foreach ($currenciesList as $currency): ?>
                                        <option 
                                            value="<?= $currency ?>" 
                                            <? if($option->is_exchange_to && $option->is_exchange_to == $currency) echo ' selected="selected"'?>
                                            >
                                            <?= $currency ?>
                                        </option>
                                    <? endforeach; ?>
                                </select>
                            </div>

                            <button type="button" class="sbexport__delbtn js-sbexport-delgroup">
                                <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_REMOVE'); ?>
                            </button>

                            <? $opt_counter++ ?>
                        </div>
                    <? endforeach; ?>
                </div>
            </div>

            <input type="submit" value="<?= Loc::GetMessage('STRANGEBRAIN_OPTIONS_INPUT_APPLY'); ?>" class="adm-btn-save sbexport__submit" />
        </div>
    </div>

    <? echo (bitrix_sessid_post()); ?>
</form>

<!-- Field group template -->
<template id="sbexportGroupTemplate">
    <div class="sbexport__fgroup js-sbexport-fgroup">
        <div class="sbexport__field">
            <div class="sbexport__label">
                <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_IBLOCK_TITLE') ?>
            </div>
            <input 
                class="sbexport__inp js-sbexport-iblock-inp" 
                type="number" 
                min="1" 
                value="" 
                placeholder="<?= Loc::getMessage('STRANGEBRAIN_OPTIONS_IBLOCK_PLACEHOLDER') ?>"
            >
        </div>

        <div class="sbexport__field">
            <div class="sbexport__label">
                <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_PATH_TITLE') ?>
            </div>
            <input 
                class="sbexport__inp js-sbexport-path-inp" 
                type="text" 
                value="" 
                placeholder="<?= Loc::getMessage('STRANGEBRAIN_OPTIONS_PATH_PLACEHOLDER') ?>"
            >
        </div>

        <div class="sbexport__field">
            <div class="sbexport__label">
                <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_OPTS_TITLE') ?>
            </div>
            <textarea 
                class="sbexport__inp sbexport__inp--area js-sbexport-options-inp" 
                rows="5" 
                value="" 
                placeholder="<?= Loc::getMessage('STRANGEBRAIN_OPTIONS_OPTS_PLACEHOLDER') ?>"></textarea>
        </div>

        <div class="sbexport__field">
            <label><?= Loc::getMessage('STRANGEBRAIN_OPTIONS_PRICE_TITLE') ?></label>
            <input 
                class="sbexport__inp sbexport__inp--sm js-sbexport-exchange-option-inp" 
                type="text"
                value="PRICE" 
                placeholder=<?= Loc::getMessage('STRANGEBRAIN_OPTIONS_PRICE_PLACEHOLDER') ?>"
            >
        </div>

        <div class="sbexport__field">
            <label><?= Loc::getMessage('STRANGEBRAIN_OPTIONS_CURRENCY_FG_TITLE') ?></label>
            <input 
                class="sbexport__inp sbexport__inp--sm js-sbexport-exchange-option-inp" 
                type="text"
                value="CYRRENCY" 
                placeholder=<?= Loc::getMessage('STRANGEBRAIN_OPTIONS_CURRENCY_FG_PLACEHOLDER') ?>"
            >
        </div>

        <div class="sbexport__field">
            <input 
                type="checkbox" 
                id="is_exchange_<?= $opt_counter ?>"
                class="sbexport__chbx js-sbexport-exchange-chbx js-sbexport-is-exchange-inp" 
            >

            <label for="is_exchange_<?= $opt_counter ?>">
                <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_DO_EXCHANGE_TITLE') ?>
            </label>
        </div>

        <div class="sbexport__exchangegroup js-sbexport-exchange-group">
            <!-- 
                Customer asks to delete "exchange from" setting.
                Yeah, I know... Now they have to set currency to every elment.
                instead of doing convertation just ones... 
                However, let's do not delete this code ;)
            -->
            <!-- <label>
                <?//= Loc::getMessage('STRANGEBRAIN_OPTIONS_PRICE_FROM_TITLE'); ?>
            </label>
            <select class="sbexport__select--sm js-sbexport-from-exchange">
                <?// foreach ($currenciesList as $currency):?>
                    <option value="<?//= $currency ?>">
                        <?//= $currency ?>
                    </option>
                <?// endforeach; ?>
            </select> -->

            <label>
                <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_PRICE_TO_TITLE') ?>
            </label>
            <select class="sbexport__select--sm js-sbexport-to-exchange">
                <? foreach ($currenciesList as $currency): ?>
                    <option value="<?= $currency ?>" >
                        <?= $currency ?>
                    </option>
                <? endforeach; ?>
            </select>
        </div>

        <button type="button" class="sbexport__delbtn js-sbexport-delgroup">
            <?= Loc::getMessage('STRANGEBRAIN_OPTIONS_REMOVE'); ?>
        </button>
    </div>
</template>
