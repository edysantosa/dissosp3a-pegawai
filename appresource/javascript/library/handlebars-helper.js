import moment from 'moment';
var Handlebars = require('handlebars/runtime');

Handlebars.registerHelper('eq', function () {
    const args = Array.prototype.slice.call(arguments, 0, -1);
    return args.every(function (expression) {
        return args[0] === expression;
    });
});

Handlebars.registerHelper('compare', function (v1, operator, v2, options) {
    'use strict';
    var operators = {
        '==': v1 == v2 ? true : false,
        '===': v1 === v2 ? true : false,
        '!=': v1 != v2 ? true : false,
        '!==': v1 !== v2 ? true : false,
        '>': v1 > v2 ? true : false,
        '>=': v1 >= v2 ? true : false,
        '<': v1 < v2 ? true : false,
        '<=': v1 <= v2 ? true : false,
        '||': v1 || v2 ? true : false,
        '&&': v1 && v2 ? true : false
    }
    if (operators.hasOwnProperty(operator)) {
        if (operators[operator]) {
            return options.fn(this);
        }
        return options.inverse(this);
    }
    return console.error('Error: Expression "' + operator + '" not found');
});

// Contoh
// {{#compare jobTitle.length '>' 20}}
//   {{#compare jobTitle.length '<=' 100}}
//     <p>Greater than 20 and less than or equal 100</p>
//   {{else}}
//     <p>Greater than 100</p>
//   {{/compare}}
// {{else}}
//   <p>Less than or equal to 20</p>
// {{/compare}}

Handlebars.registerHelper('abs', function(num) {
  return Math.abs(num);
});

Handlebars.registerHelper('findByProperty', function(propName, array, id) {
    for (var i = array.length - 1; i >= 0; i--) {
        if (array[i][propName] == id) {
            return array[i];
        }
    }
});

Handlebars.registerHelper('formatDateString', function(dateString, currentFormat, format) {
    let date = moment(dateString, currentFormat);
    if (date.isValid()) {
        return date.format(format);
    } else {
        return '';
    }
});

Handlebars.registerHelper('formatNumberIndonesia', function(n) {
    if (isNaN(parseFloat(n))) {
        return 0;
    } else {
        return parseFloat(n).toLocaleString('id-ID');
    }
});

Handlebars.registerHelper('times', function(n, block) {
    var accum = '';
    for(var i = 0; i < n; ++i)
        accum += block.fn(i);
    return accum;
});

Handlebars.registerHelper("math", function(lvalue, operator, rvalue, options) {
    lvalue = parseFloat(lvalue);
    rvalue = parseFloat(rvalue);
        
    return {
        "+": lvalue + rvalue,
        "-": lvalue - rvalue,
        "*": lvalue * rvalue,
        "/": lvalue / rvalue,
        "%": lvalue % rvalue
    }[operator];
});