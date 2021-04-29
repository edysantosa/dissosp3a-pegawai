import 'jquery';
import bowser from 'bowser';
import _ from 'lodash';

export default exec();

function exec(){    
    if( typeof window.__sitebase != 'undefined' ){
        return window.__sitebase;
    }
    
    var sitebase = $('#sitebase').data();

    window.__sitebase = _.cloneDeep(sitebase);
    window.__sitebase.browser       = bowser;
    window.__sitebase.cartField     = 'slimbasecart';
    
    return window.__sitebase;
}