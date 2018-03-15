if (Product && Product.Bundle) {
    Product.Bundle.prototype.changeSelection = function (selection) {
        var parts = selection.id.split('-');
        if (this.config['options'][parts[2]].isMulti) {
            selected = new Array();
            if (selection.tagName == 'SELECT') {
                for (var i = 0; i < selection.options.length; i++) {
                    if (selection.options[i].selected && selection.options[i].value != '') {
                        selected.push(selection.options[i].value);
                    }
                }
            } else if (selection.tagName == 'INPUT') {
                selector = parts[0] + '-' + parts[1] + '-' + parts[2];
                selections = $$('.' + selector);
                for (var i = 0; i < selections.length; i++) {
                    if (selections[i].checked && selections[i].value != '') {
                        selected.push(selections[i].value);
                    }
                }
            }
            this.config.selected[parts[2]] = selected;
        } else {
            if (selection.value != '') {
                this.config.selected[parts[2]] = new Array(selection.value);
            } else {
                this.config.selected[parts[2]] = new Array();
            }
            this.populateQty(parts[2], selection.value);
            var tierPriceElement = $('bundle-option-' + parts[2] + '-tier-prices'),
                tierPriceHtml = '';
            if (selection.value != '' && this.config.options[parts[2]].selections[selection.value].customQty == 1) {
                tierPriceHtml = this.config.options[parts[2]].selections[selection.value].tierPriceHtml;
            }
            tierPriceElement.update(tierPriceHtml);
        }

        // \/ cashpresso
        if (typeof C2EcomWizard !== 'undefined') {
            var price = this.reloadPrice();

            if (document.getElementById("c2-financing-label-0")) {
                C2EcomWizard.refreshAmount('c2-financing-label-0', price);
            } else if (document.getElementById('cashpresso_product_id_' + this.productId)) {
                var C2link = document.getElementById('cashpresso_product_id_' + this.productId);

                C2link.onclick = function () {
                    C2EcomWizard.startOverlayWizard(price)
                }

                if (typeof C2EcomWizard.ls_template !== 'undefined') {
                    C2link.innerHTML = C2EcomWizard.ls_template(C2link, price);
                }
            }
        }
        // /\ cashpresso
    }

    Product.Bundle.prototype.changeOptionQty = function (element, event) {
        var checkQty = true;
        if (typeof(event) != 'undefined') {
            if (event.keyCode == 8 || event.keyCode == 46) {
                checkQty = false;
            }
        }
        if (checkQty && (Number(element.value) == 0 || isNaN(Number(element.value)))) {
            element.value = 1;
        }
        parts = element.id.split('-');
        optionId = parts[2];
        if (!this.config['options'][optionId].isMulti) {
            selectionId = this.config.selected[optionId][0];
            this.config.options[optionId].selections[selectionId].qty = element.value * 1;
            var price = this.reloadPrice();
        }

        // \/ cashpresso
        if (typeof C2EcomWizard !== 'undefined') {

            if (!price) {
                var price = this.reloadPrice();
            }

            if (document.getElementById("c2-financing-label-0")) {
                C2EcomWizard.refreshAmount('c2-financing-label-0', price);
            } else if (document.getElementById('cashpresso_product_id_' + this.productId)) {
                var C2link = document.getElementById('cashpresso_product_id_' + this.productId);
                C2link.onclick = function () {
                    C2EcomWizard.startOverlayWizard(price)
                }

                if (typeof C2EcomWizard.ls_template !== 'undefined') {
                    C2link.innerHTML = C2EcomWizard.ls_template(C2link, price);
                }
            }
        }
        // /\ cashpresso
    }
}