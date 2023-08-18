BX.ready(() => {
    class ExportUtils {
        constructor() {
            this.els = {
                addBtn:             '.js-sbexport-addgroup',
                template:           '#sbexportGroupTemplate',
                cont:               '.js-sbexport-cont',
                delBtn:             '.js-sbexport-delgroup',
                fgroup:             '.js-sbexport-fgroup',
                iblockInp:          '.js-sbexport-iblock-inp',
                exOptionInp:        '.js-sbexport-exchange-option-inp',
                exIsInp:            '.js-sbexport-is-exchange-inp',
                exFromSelect:       '.js-sbexport-from-exchange',
                exToSelect:         '.js-sbexport-to-exchange',
                pathInp:            '.js-sbexport-path-inp',
                optionsInp:         '.js-sbexport-options-inp',
                message:            '.js-sbexport-message',
                messageTxt:         '.js-sbexport-message-text',
                form:               '.js-sbexport-message-form',
                //currency els
                currencyChbx:       '.js-sbexport-dynamic-currency-chbx',
                currencyGroup:      '.js-sbexport-dynamic-currency-group',
                //exchange els
                exchangeChbx:       '.js-sbexport-exchange-chbx',
                exchangeGroup:      '.js-sbexport-exchange-group',
                //ajaxSettings
                loadingClass:       'sbloading',
                ajaxExportBtn:      '.js-sbexport-do-export',
                ajaxCurrencyBtn:    '.js-sbexport-get-currency',
                usdInput:           '.js-sbexport-usd',
                eurInput:           '.js-sbexport-eur'
            }

            this.fieldName = 'sbexport';
            
            //ajax options
            this.isLoadingExports = false;
            this.isLoadingExports = false;
        }

        init() {
            this.initAddingFields();
            this.initDeletingFields();
            this.initAjaxExport();
            this.initAjaxGetCurrancies();
            this.initDynamicCurrencyToggler();
            this.initExchangeToggler();
        }

        //INIT BLOCK
        
        initAddingFields() {
            const $addBtn = document.querySelector(this.els.addBtn);
            const $template = document.querySelector(this.els.template);
            const $cont = document.querySelector(this.els.cont);
            
            if(!$addBtn || !$template || !$cont) return;

            $addBtn.addEventListener('click', () => {
                this.cloneTemplate();
            });
        }

        initDeletingFields() {
            const $delBtns = document.querySelectorAll(this.els.delBtn);

            if(!$delBtns) return;

            $delBtns.forEach((el) => {
                el.addEventListener('click', () => {
                    const fieldGroup = el.closest(this.els.fgroup);
                    if(fieldGroup) fieldGroup.remove();
                });
            });
        }

        initAjaxExport() {
            const btn = document.querySelector(this.els.ajaxExportBtn);
            if(!btn) return;
            
            btn.addEventListener('click', () => {
                if(this.isLoadingExports) return;

                btn.classList.add(this.els.loadingClass);
                this.isLoadingExports = true;

                const data = this.getFormData();

                BX.ajax.runAction('strangebrain:exports.api.exports.export', {data:data})
                .then((response) => {
                    btn.classList.remove(this.els.loadingClass);
                    this.isLoadingExports = false;

                    this.showMessage('Экспорт произведен успешно');
                    console.log(response);
                }, 
                (response) => {
                    console.log(response);
                    btn.classList.remove(this.els.loadingClass);
                    this.isLoadingExports = false;

                    this.showMessage('Ошибка экспорта');
                });
            });
        }

        initAjaxGetCurrancies() {
            const btn = document.querySelector(this.els.ajaxCurrencyBtn);
            if(!btn) return;
            
            btn.addEventListener('click', () => {
                if(this.isCurrencyExports) return;

                btn.classList.add(this.els.loadingClass);
                this.isCurrencyExports = true;

                const data = this.getFormData();

                BX.ajax.runAction('strangebrain:exports.api.exports.currency', {data:data})
                .then((response) => {
                    btn.classList.remove(this.els.loadingClass);
                    this.isCurrencyExports = false;

                    const $usdInput = document.querySelector(this.els.usdInput);
                    if($usdInput && response.data && response.data.USD && response.data.USD.VALUE) {
                        $usdInput.value = response.data.USD.VALUE;
                    }

                    const $eurInput = document.querySelector(this.els.eurInput);
                    if($eurInput && response.data && response.data.EUR && response.data.EUR.VALUE) {
                        $eurInput.value = response.data.EUR.VALUE;
                    }

                    this.showMessage('Курсы валют обнавлены успешно');
                    console.log(response);
                }, 
                (response) => {
                    console.log(response);
                    btn.classList.remove(this.els.loadingClass);
                    this.isCurrencyExports = false;

                    this.showMessage('Ошибка получения курсов валют');
                });
            });
        }

        initDynamicCurrencyToggler() {
            const $toggle = document.querySelector(this.els.currencyChbx);
            const $toggleGroup = document.querySelector(this.els.currencyGroup);
            if(!$toggle || !$toggleGroup) return;

            $toggle.addEventListener('change', (e) => {
                (e.target.checked) 
                    ? $toggleGroup.classList.remove('active') 
                    : $toggleGroup.classList.add('active');
            });
        }

        initExchangeToggler() {
            const $togglers = document.querySelectorAll(this.els.exchangeChbx);
            if(!$togglers && !$togglers.length) return;

            $togglers.forEach((el) => {
                el.addEventListener('change', (e) => {
                    const group = el.closest(this.els.fgroup);
                    if(!group) return;
                    
                    const toggleGroup = group.querySelector(this.els.exchangeGroup);
                    if(!toggleGroup) return;
                    
                    (el.checked) 
                        ? toggleGroup.classList.add('active') 
                        : toggleGroup.classList.remove('active');
                });
            });
        }

        //UTILS BLOCK

        cloneTemplate() {
            const $template = document.querySelector(this.els.template);
            const $cont = document.querySelector(this.els.cont);
            const $clone = $template.content.cloneNode(true);
            const fieldIndex = document.querySelectorAll(this.els.fgroup).length;

            $cont.prepend($clone);

            this.setNameForClonedNode($cont, fieldIndex);

            //exchange Group toggler
            const $toggler = $cont.querySelector(this.els.exchangeChbx);
            if(!$toggler) return;

            $toggler.addEventListener('change', (e) => {
                const group = $toggler.closest(this.els.fgroup);
                if(!group) return;
                
                const toggleGroup = group.querySelector(this.els.exchangeGroup);
                if(!toggleGroup) return;
                
                ($toggler.checked) 
                    ? toggleGroup.classList.add('active') 
                    : toggleGroup.classList.remove('active');
            });
        }

        setNameForClonedNode($cont, fieldIndex) {
            const $lastAdded = $cont.querySelector(this.els.fgroup);
            if(!$lastAdded) return;

            const $iblockInp = $lastAdded.querySelector(this.els.iblockInp);
            if($iblockInp) $iblockInp.setAttribute('name', `${this.fieldName}[${fieldIndex}][iblock]`);

            const $pathInp = $lastAdded.querySelector(this.els.pathInp);
            if($pathInp) $pathInp.setAttribute('name', `${this.fieldName}[${fieldIndex}][path]`);

            const $optionsInp = $lastAdded.querySelector(this.els.optionsInp);
            if($optionsInp) $optionsInp.setAttribute('name', `${this.fieldName}[${fieldIndex}][options]`);

            const $exOptionInp = $lastAdded.querySelector(this.els.exOptionInp);
            if($exOptionInp) $exOptionInp.setAttribute('name', `${this.fieldName}[${fieldIndex}][exchange_option]`);
            
            const $exIsInp = $lastAdded.querySelector(this.els.exIsInp);
            if($exIsInp) $exIsInp.setAttribute('name', `${this.fieldName}[${fieldIndex}][is_exchange]`);
            
            const $exFromSelect = $lastAdded.querySelector(this.els.exFromSelect);
            if($exFromSelect) $exFromSelect.setAttribute('name', `${this.fieldName}[${fieldIndex}][is_exchange_from]`);
            
            const $exToSelect = $lastAdded.querySelector(this.els.exToSelect);
            if($exToSelect) $exToSelect.setAttribute('name', `${this.fieldName}[${fieldIndex}][is_exchange_to]`);
        }

        showMessage(message) {
            const el = document.querySelector(this.els.message);
            const elTxt = document.querySelector(this.els.messageTxt);
            if(!el || !elTxt) return;

            el.classList.add('active');
            elTxt.innerText = message;

            setTimeout(() => {
                el.classList.remove('active');
                elTxt.innerText = '';
            }, 3000);
        }

        getFormData() {
            const $form = document.querySelector(this.els.form);
            if(!$form) return;

            const formData = new FormData($form);
            return formData;
        }
    }

    const exportUtil = new ExportUtils();
    exportUtil.init();
});