import AutoNumeric from 'autonumeric';

export function formatElement(selector){
    new AutoNumeric.multiple(selector, {
        // currencySymbol: "Rp.",
        decimalCharacter: ",",
        decimalPlaces: 0,
        digitGroupSeparator: "."
    });
}

export function getUnformattedValue($elem){
    if (typeof $elem !== 'undefined') {
        let result = AutoNumeric.getAutoNumericElement($elem[0]);
        if(result) {
            return result.getNumericString();
        } else {
            return $elem.val();
        }
    } else {
        return 0;
    }
}

export function set($elem, value){
    if (typeof $elem !== 'undefined' && $.isNumeric(value)) {
        for (var i = $elem.length - 1; i >= 0; i--) {
            let result = AutoNumeric.getAutoNumericElement($elem[i]);
            if (result == null) {
                // Also, no need to querySelector `inputSel` here:
                result = new AutoNumeric($elem[i], {
                    // currencySymbol: "Rp.",
                    decimalCharacter: ",",
                    decimalPlaces: 0,
                    digitGroupSeparator: "."
                });
            }
            result.set(value);
        }
    } else {
        return 0;
    }
}

export function serializeForm($elem){
    let element;
    for (var i = $elem[0].length - 1; i >= 0; i--) {
        if (AutoNumeric.isManagedByAutoNumeric($elem[0][i])) {
            element = $elem[0][i];
            break;
        }
    }
    let result = AutoNumeric.getAutoNumericElement(element);
    return result.formNumericString();
}

export function serializeArrayForm($elem){
    let element;
    for (var i = $elem[0].length - 1; i >= 0; i--) {
        if (AutoNumeric.isManagedByAutoNumeric($elem[0][i])) {
            element = $elem[0][i];
            break;
        }
    }
    let result = AutoNumeric.getAutoNumericElement(element);
    return result.formArrayNumericString();
}