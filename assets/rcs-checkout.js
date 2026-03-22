/* global rcsCheckout */

jQuery(function ($) {
    if (!rcsCheckout || !rcsCheckout.communesUrl) {
        return;
    }

    function setInlineFieldMessage($field, suffix, message, className) {
        if (!$field || !$field.length) {
            return;
        }

        const id = ($field.attr('id') || $field.attr('name') || 'field') + suffix;
        let $message = $('#' + id);

        if (!message) {
            if ($message.length) {
                $message.remove();
            }
            return;
        }

        if (!$message.length) {
            $message = $('<div></div>', {
                id,
                class: className || '',
                role: 'alert'
            });
            $field.after($message);
        }

        $message.text(message);
    }

    function initAddressCommaGuard(fieldSelectorOrName) {
        let $field = $(fieldSelectorOrName);
        if (!$field.length && typeof fieldSelectorOrName === 'string' && fieldSelectorOrName[0] === '#') {
            const name = fieldSelectorOrName.slice(1);
            $field = $('input[name="' + name + '"]');
        }
        if (!$field.length) {
            return;
        }

        function update() {
            const value = String($field.val() || '');
            const hasComma = value.indexOf(',') !== -1;
            setInlineFieldMessage(
                $field,
                '-rcs-comma-warning',
                hasComma ? 'Solo Calle y Número. Info adiconal abajo' : '',
                'rcs-field-warning'
            );
        }

        $field.on('input.rcsCommaGuard change.rcsCommaGuard blur.rcsCommaGuard', update);
        update();
    }

    function normalizeString(str) {
        return String(str || '')
            .trim()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[’']/g, '')
            .replace(/[^a-zA-ZñÑ0-9\s]/g, ' ')
            .toLowerCase()
            .replace(/\s+/g, ' ');
    }

    function ensureCountryCL() {
        if (!$('#billing_country').length) {
            $('<input>', { type: 'hidden', id: 'billing_country', name: 'billing_country', value: 'CL' }).appendTo('form.checkout');
        } else {
            $('#billing_country').val('CL').trigger('change');
        }
    }

    function buildRegionCodeMap(states) {
        const map = {};
        if (states && typeof states === 'object') {
            Object.keys(states).forEach(code => {
                map[normalizeString(states[code])] = code;
            });
        }

        const aliases = {
            "ohiggins": "Libertador General Bernardo O'Higgins",
            "libertador general bernardo ohiggins": "Libertador General Bernardo O'Higgins",
            "metropolitana": "Región Metropolitana de Santiago",
            "metropolitana de santiago": "Región Metropolitana de Santiago",
            "region metropolitana": "Región Metropolitana de Santiago",
            "región metropolitana": "Región Metropolitana de Santiago",
        };

        Object.keys(aliases).forEach(alias => {
            const canonical = aliases[alias];
            const normCanonical = normalizeString(canonical);
            if (map[normCanonical]) {
                map[normalizeString(alias)] = map[normCanonical];
            }
        });

        return map;
    }

    function getRegionPriceMap(states, regionPrices) {
        const normalizedPrices = {};
        const rawPrices = regionPrices && typeof regionPrices === 'object' ? regionPrices : {};

        Object.keys(rawPrices).forEach(key => {
            normalizedPrices[normalizeString(key)] = Number(rawPrices[key]) || 0;
        });

        if (states && typeof states === 'object') {
            Object.keys(states).forEach(code => {
                const label = String(states[code] || '');
                const normalizedCode = normalizeString(code);
                const normalizedLabel = normalizeString(label);

                if (Object.prototype.hasOwnProperty.call(rawPrices, code)) {
                    normalizedPrices[normalizedCode] = Number(rawPrices[code]) || 0;
                    normalizedPrices[normalizedLabel] = Number(rawPrices[code]) || 0;
                }
                if (Object.prototype.hasOwnProperty.call(rawPrices, label)) {
                    normalizedPrices[normalizedCode] = Number(rawPrices[label]) || 0;
                    normalizedPrices[normalizedLabel] = Number(rawPrices[label]) || 0;
                }
                if (Object.prototype.hasOwnProperty.call(rawPrices, label.toUpperCase())) {
                    normalizedPrices[normalizedCode] = Number(rawPrices[label.toUpperCase()]) || 0;
                    normalizedPrices[normalizedLabel] = Number(rawPrices[label.toUpperCase()]) || 0;
                }
            });
        }

        const regionAliases = {
            "region metropolitana de santiago": "metropolitana de santiago",
            "región metropolitana de santiago": "metropolitana de santiago",
            "metropolitana": "metropolitana de santiago",
            "region metropolitana": "metropolitana de santiago",
            "región metropolitana": "metropolitana de santiago",
            "libertador general bernardo ohiggins": "libertador general bernardo ohiggins",
            "libertador general bernardo o higgins": "libertador general bernardo ohiggins",
            "ohiggins": "libertador general bernardo ohiggins",
            "bio bio": "biobio",
            "bío bío": "biobio",
            "la araucania": "araucania",
            "araucania": "araucania",
            "nuble": "ñuble",
            "aysen": "aysen del general carlos ibanez del campo",
            "aysén": "aysen del general carlos ibanez del campo",
            "magallanes": "magallanes y la antartica chilena",
            "magallanes y la antartica chilena": "magallanes y la antartica chilena"
        };

        Object.keys(regionAliases).forEach(alias => {
            const normalizedAlias = normalizeString(alias);
            const normalizedCanonical = normalizeString(regionAliases[alias]);

            if (Object.prototype.hasOwnProperty.call(normalizedPrices, normalizedCanonical)) {
                normalizedPrices[normalizedAlias] = normalizedPrices[normalizedCanonical];
            }
        });

        return normalizedPrices;
    }

    function parseDisplayedMoney(text) {
        const cleaned = String(text || '').replace(/[^\d.,-]/g, '');
        if (!cleaned) return 0;

        const decimalSep = String(rcsCheckout.currencyDecimalSep || '.');
        const thousandSep = String(rcsCheckout.currencyThousandSep || ',');
        let normalized = cleaned.split(thousandSep).join('');

        if (decimalSep !== '.') {
            normalized = normalized.replace(decimalSep, '.');
        }

        const value = Number(normalized);
        return Number.isFinite(value) ? value : 0;
    }

    function formatMoney(amount) {
        const value = Number(amount) || 0;
        const decimals = Number(rcsCheckout.currencyDecimals || 0);
        const fixed = value.toFixed(decimals);
        const parts = fixed.split('.');
        const integer = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, rcsCheckout.currencyThousandSep || ',');
        const decimal = decimals > 0 ? (rcsCheckout.currencyDecimalSep || '.') + (parts[1] || '') : '';
        return String(rcsCheckout.currencySymbol || '$') + integer + decimal;
    }

    function updateCheckoutSummaryForComuna(communeName, comunaToRegion, regionPriceMap) {
        const normalizedCommune = normalizeString(communeName);
        const $shippingLabel = $('#red-cultural-checkout-shipping-label');

        if (!normalizedCommune) {
            if ($shippingLabel.length) {
                $shippingLabel.text('Envío');
            }
            return;
        }

        const regionName = comunaToRegion[normalizedCommune];
        if (!regionName) {
            if ($shippingLabel.length) {
                $shippingLabel.text('Envío');
            }
            return;
        }

        const normalizedRegion = normalizeString(regionName);
        const shippingCost = Object.prototype.hasOwnProperty.call(regionPriceMap, normalizedRegion)
            ? Number(regionPriceMap[normalizedRegion]) || 0
            : 0;

        const $shippingValue = $('#red-cultural-checkout-shipping-value');
        const $totalValue = $('#red-cultural-checkout-total-value');
        const $subtotalValue = $('#red-cultural-checkout-subtotal-value');

        if (!$shippingValue.length || !$totalValue.length || !$subtotalValue.length) {
            return;
        }

        const subtotal = parseDisplayedMoney($subtotalValue.text());
        const total = subtotal + shippingCost;

        if ($shippingLabel.length) {
            $shippingLabel.text('Envío [' + regionName + ']');
        }
        $shippingValue
            .text(formatMoney(shippingCost))
            .removeClass('rcs-shipping-missing');
        $totalValue.text(formatMoney(total));
    }

    function resetCheckoutSummary() {
        const $shippingLabel = $('#red-cultural-checkout-shipping-label');
        const $shippingValue = $('#red-cultural-checkout-shipping-value');
        const $totalValue = $('#red-cultural-checkout-total-value');
        const $subtotalValue = $('#red-cultural-checkout-subtotal-value');

        if ($shippingLabel.length) {
            $shippingLabel.text('Envío');
        }

        if ($shippingValue.length) {
            $shippingValue
                .text('falta agregar comuna')
                .addClass('rcs-shipping-missing');
        }

        if ($totalValue.length && $subtotalValue.length) {
            $totalValue.text($subtotalValue.text());
        }
    }

    function swapSelectToInput($field) {
        if (!$field.length) return $field;
        if ($field.prop('tagName').toLowerCase() !== 'select') return $field;

        const id = $field.attr('id');
        const name = $field.attr('name');
        const classes = $field.attr('class') || '';
        const required = $field.prop('required');
        const value = $field.val() || '';

        $field.attr('id', id + '__rcs_old');
        $field.attr('name', name + '__rcs_old');
        $field.prop('disabled', true);
        $field.hide();

        const $input = $('<input>', {
            type: 'text',
            id,
            name,
            class: classes,
            value
        });

        if (required) $input.prop('required', true);
        $field.after($input);
        return $input;
    }

    function levenshteinDistance(a, b) {
        const matrix = [];
        for (let i = 0; i <= b.length; i++) matrix[i] = [i];
        for (let j = 0; j <= a.length; j++) matrix[0][j] = j;

        for (let i = 1; i <= b.length; i++) {
            for (let j = 1; j <= a.length; j++) {
                if (b.charAt(i - 1) === a.charAt(j - 1)) {
                    matrix[i][j] = matrix[i - 1][j - 1];
                } else {
                    matrix[i][j] = Math.min(
                        matrix[i - 1][j - 1] + 1,
                        matrix[i][j - 1] + 1,
                        matrix[i - 1][j] + 1
                    );
                }
            }
        }
        return matrix[b.length][a.length];
    }

    function findClosest(value, list) {
        const normalizedValue = normalizeString(value);
        if (!normalizedValue) return null;

        let closest = null;
        let bestScore = 0;

        list.forEach(item => {
            const normalizedItem = normalizeString(item);
            const distance = levenshteinDistance(normalizedValue, normalizedItem);
            const maxLength = Math.max(normalizedValue.length, normalizedItem.length) || 1;
            const similarity = 1 - (distance / maxLength);
            if (similarity > bestScore) {
                bestScore = similarity;
                closest = item;
            }
        });

        return bestScore >= 0.6 ? closest : null;
    }

    function setStateFromRegion($cityField, comunaToRegion, regionCodeMap) {
        const value = String($cityField.val() || '');
        const normalized = normalizeString(value);
        const regionName = comunaToRegion[normalized];
        if (!regionName) return false;

        const stateCode = regionCodeMap[normalizeString(regionName)];
        if (!stateCode) return false;

        const isBilling = $cityField.attr('id') === 'billing_city';
        const $state = $(isBilling ? '#billing_state' : '#shipping_state');
        if (!$state.length) return false;

        if ($state.val() !== stateCode) {
            $state.val(stateCode).trigger('change');
            if (isBilling && $('#shipping_state').length && !$('#ship-to-different-address-checkbox').is(':checked')) {
                $('#shipping_state').val(stateCode).trigger('change');
            }
            return true;
        }

        return false;
    }

    function initAutocomplete(communes) {
        const comunaList = [];
        const comunaToRegion = {};
        const comunaExactMap = {};

        communes.forEach(entry => {
            if (!entry || !entry.name) return;
            const name = String(entry.name);
            const regionName = String(entry.region_name || (entry.region && entry.region.name) || '');
            comunaList.push(name);
            const key = normalizeString(name);
            comunaExactMap[key] = name;
            if (regionName) comunaToRegion[key] = regionName;
        });

        const regionCodeMap = buildRegionCodeMap(rcsCheckout.states || {});
        const regionPriceMap = getRegionPriceMap(rcsCheckout.states || {}, rcsCheckout.regionPrices || {});

        let $billingCity = swapSelectToInput($('#billing_city'));
        let $shippingCity = swapSelectToInput($('#shipping_city'));

        const $fields = $billingCity.add($shippingCity).filter(function () {
            return $(this).length && $(this).prop('tagName').toLowerCase() === 'input';
        });

        if (!$fields.length || !$.ui || !$.ui.autocomplete) {
            return;
        }

        function triggerCheckoutUpdate() {
            // Trigger immediately and once more in the next tick for aggressive custom checkouts.
            $('body').trigger('update_checkout');
            setTimeout(() => $('body').trigger('update_checkout'), 50);
        }

        function getFieldErrorElement($field) {
            const errorId = $field.attr('id') + '-rcs-error';
            let $error = $('#' + errorId);

            if (!$error.length) {
                $error = $('<div></div>', {
                    id: errorId,
                    class: 'rcs-comuna-error',
                    'aria-live': 'polite'
                });
                $field.after($error);
            }

            return $error;
        }

        function setFieldError($field, message) {
            const $error = getFieldErrorElement($field);

            if (message) {
                $field.addClass('rcs-comuna-invalid');
                $error.text(message).show();
            } else {
                $field.removeClass('rcs-comuna-invalid');
                $error.text('').hide();
            }
        }

        function isKnownComuna(value) {
            return !!comunaExactMap[normalizeString(value)];
        }

        function updateSummaryForField($field) {
            const isBilling = $field.attr('id') === 'billing_city';
            const usingAlternateShipping = $('#red-cultural-checkout-ship-toggle-input').is(':checked');

            if (!usingAlternateShipping && !isBilling) {
                return;
            }

            if (usingAlternateShipping && isBilling) {
                return;
            }

            updateCheckoutSummaryForComuna($field.val(), comunaToRegion, regionPriceMap);
        }

        function getActiveComunaField() {
            const usingAlternateShipping = $('#red-cultural-checkout-ship-toggle-input').is(':checked');
            return usingAlternateShipping ? $('#shipping_city') : $('#billing_city');
        }

        function needsShippingGuard() {
            return $('#red-cultural-checkout-shipping-row').length > 0;
        }

        function triggerComunaShake($field) {
            if (!$field.length) {
                return;
            }

            $field.removeClass('rcs-comuna-shake');
            if ($field[0]) {
                void $field[0].offsetWidth;
            }
            $field.addClass('rcs-comuna-shake');

            setTimeout(function () {
                $field.removeClass('rcs-comuna-shake');
            }, 450);
        }

        function updatePlaceOrderGuard() {
            const $button = $('#red-cultural-checkout-place-order');
            if (!$button.length) {
                return;
            }

            if (!$button.attr('data-auth-disabled-initialized')) {
                $button.attr('data-auth-disabled', $button.prop('disabled') ? '1' : '0');
                $button.attr('data-auth-disabled-initialized', '1');
            }

            const authDisabled = $button.attr('data-auth-disabled') === '1';
            const mustRequireComuna = needsShippingGuard();
            const $activeField = getActiveComunaField();
            const hasComuna = !!normalizeString($activeField.val());
            const hasValidComuna = isKnownComuna($activeField.val());
            const shouldDisable = !authDisabled && mustRequireComuna && ( !hasComuna || !hasValidComuna );

            $button.prop('disabled', shouldDisable);
            $button.attr('aria-disabled', shouldDisable ? 'true' : 'false');
            $button.toggleClass('rcs-button-disabled', shouldDisable);

            let $overlay = $('#rcs-place-order-guard');
            if (shouldDisable) {
                if (!$overlay.length) {
                    $overlay = $('<div id="rcs-place-order-guard" class="rcs-place-order-guard" aria-hidden="true"></div>');
                    $button.after($overlay);
                }

                $overlay.off('.rcsGuard');
                $overlay.on('mouseenter.rcsGuard click.rcsGuard mousedown.rcsGuard touchstart.rcsGuard', function (event) {
                    event.preventDefault();
                    triggerComunaShake($activeField);
                    $activeField.trigger('focus');
                });
            } else if ($overlay.length) {
                $overlay.remove();
            }
        }

        function clearComunaFieldsOnLoad() {
            [$billingCity, $shippingCity].forEach(function ($field) {
                if (!$field || !$field.length) {
                    return;
                }

                $field.val('');

                const legacyId = $field.attr('id') + '__rcs_old';
                const $legacyField = $('#' + legacyId);
                if ($legacyField.length) {
                    $legacyField.val('');
                }

                setFieldError($field, '');
            });
        }

        function forceCloseMenu($field) {
            try { $field.autocomplete('close'); } catch (e) {}
            const $menu = $field.autocomplete('widget');
            if ($menu && $menu.length) {
                $menu.hide();
                $menu.empty();
            }
        }

        $fields.autocomplete({
            source: function (request, response) {
                if ($(this.element).data('rcs-selecting')) return response([]);
                const term = String(request.term || '');
                if (!term) return response([]);

                const regex = new RegExp("^" + $.ui.autocomplete.escapeRegex(term), "i");
                const matches = comunaList
                    .filter(comuna => regex.test(comuna))
                    .map(comuna => ({
                        label: comuna,
                        value: comuna,
                        region: comunaToRegion[normalizeString(comuna)] || ''
                    }));
                response(matches.slice(0, 3));
            },
            minLength: 1,
            select: function (event, ui) {
                const $field = $(this);
                event.preventDefault();
                $field.val(ui.item.value);
                
                // Set a flag to prevent re-opening if input/change events are fired
                $field.data('rcs-selecting', true);
                
                $field.trigger('input').trigger('change');
                if ($field[0]) {
                    $field[0].dispatchEvent(new Event('change', { bubbles: true }));
                }

                setStateFromRegion($field, comunaToRegion, regionCodeMap);
                updateSummaryForField($field);
                setFieldError($field, '');
                
                // Close menu immediately and then once more after a short delay to catch late triggers
                forceCloseMenu($field);
                setTimeout(() => {
                    forceCloseMenu($field);
                    $field.data('rcs-selecting', false);
                    $field.trigger('blur'); // Force blur to ensure menu is gone
                }, 50);

                triggerCheckoutUpdate();
                return false;
            },
            change: function () {
                const $field = $(this);
                const normalized = normalizeString($field.val());

                if (comunaExactMap[normalized]) {
                    $field.val(comunaExactMap[normalized]);
                    setStateFromRegion($field, comunaToRegion, regionCodeMap);
                    updateSummaryForField($field);
                    setFieldError($field, '');
                    forceCloseMenu($field);
                    updatePlaceOrderGuard();
                    triggerCheckoutUpdate();
                    return;
                }

                if (normalized) {
                    setFieldError($field, 'No existe esa comuna');
                    resetCheckoutSummary();
                    updatePlaceOrderGuard();
                    forceCloseMenu($field);
                    triggerCheckoutUpdate();
                } else {
                    setFieldError($field, '');
                }
            }
        });

        $fields.each(function () {
            const instance = $(this).autocomplete('instance');
            if (!instance) {
                return;
            }

            instance._renderItem = function (ul, item) {
                const $row = $('<div class="rcs-comuna-option"></div>');

                $('<span class="rcs-comuna-option-name"></span>')
                    .text(item.label || item.value || '')
                    .appendTo($row);

                $('<span class="rcs-comuna-option-region"></span>')
                    .text(item.region || '')
                    .appendTo($row);

                return $('<li></li>').append($row).appendTo(ul);
            };
        });

        $fields.on('autocompleteopen', function () {
            const $field = $(this);
            const $menu = $field.autocomplete('widget');
            if ($menu && $menu.length) {
                $menu.addClass('rcs-comuna-menu');
                $menu.css('width', $field.outerWidth() + 'px');
            }
        });

        $(window).on('resize.rcsComunaMenu', function () {
            const $focused = $fields.filter(':focus');
            if (!$focused.length) return;
            const $menu = $focused.autocomplete('widget');
            if ($menu && $menu.is(':visible')) {
                $menu.css('width', $focused.outerWidth() + 'px');
            }
        });

        $(document).on('mousedown.rcsComunaMenu touchstart.rcsComunaMenu', function (e) {
            const $target = $(e.target);
            const clickedMenu = $target.closest('.ui-autocomplete.rcs-comuna-menu').length > 0;
            const clickedField = $target.closest($fields).length > 0;
            if (!clickedMenu && !clickedField) {
                $fields.each(function () { forceCloseMenu($(this)); });
            }
        });

        $fields.on('blur', function () {
            const $field = $(this);
            if (isKnownComuna($field.val())) {
                setStateFromRegion($field, comunaToRegion, regionCodeMap);
                updateSummaryForField($field);
                setFieldError($field, '');
            } else if (normalizeString($field.val())) {
                setFieldError($field, 'No existe esa comuna');
                resetCheckoutSummary();
            } else {
                setFieldError($field, '');
            }
            updatePlaceOrderGuard();
            forceCloseMenu($field);
        });

        // If the user types a valid comuna (without selecting), still update totals.
        $fields.on('input', function () {
            const $field = $(this);
            const normalized = normalizeString($field.val());
            if (comunaExactMap[normalized]) {
                updateSummaryForField($field);
                setFieldError($field, '');
            } else if (!normalized) {
                resetCheckoutSummary();
                setFieldError($field, '');
            } else {
                setFieldError($field, 'No existe esa comuna');
                resetCheckoutSummary();
            }
            updatePlaceOrderGuard();
            triggerCheckoutUpdate();
        });

        $('#red-cultural-checkout-ship-toggle-input').on('change', function () {
            const usingAlternateShipping = $(this).is(':checked');
            const $activeField = usingAlternateShipping ? $('#shipping_city') : $('#billing_city');

            if ($activeField.length) {
                if (normalizeString($activeField.val())) {
                    updateSummaryForField($activeField);
                } else {
                    resetCheckoutSummary();
                }
            }

            updatePlaceOrderGuard();
            triggerCheckoutUpdate();
        });

        $('body').on('updated_checkout', function () {
            const usingAlternateShipping = $('#red-cultural-checkout-ship-toggle-input').is(':checked');
            const $activeField = usingAlternateShipping ? $('#shipping_city') : $('#billing_city');
            if ($activeField.length) {
                if (normalizeString($activeField.val())) {
                    updateSummaryForField($activeField);
                } else {
                    resetCheckoutSummary();
                }
            }
            updatePlaceOrderGuard();
        });

        clearComunaFieldsOnLoad();
        resetCheckoutSummary();
        updatePlaceOrderGuard();
    }

    ensureCountryCL();
    initAddressCommaGuard('#billing_address_1');
    initAddressCommaGuard('#shipping_address_1');

    fetch(rcsCheckout.communesUrl, { credentials: 'same-origin' })
        .then(r => r.json())
        .then(data => {
            if (Array.isArray(data)) initAutocomplete(data);
        })
        .catch(() => {
            $.getJSON(rcsCheckout.communesUrl, function (data) {
                if (Array.isArray(data)) initAutocomplete(data);
            });
        });
});
