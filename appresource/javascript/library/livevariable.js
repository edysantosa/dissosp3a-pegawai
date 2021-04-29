import lodash from 'lodash';
export default function( defaultValue ){
    var me = this;
    var val;
    var listener = function(){};

    if( lodash.isNumber(defaultValue) ){
        val = defaultValue;
    }else{
        val = defaultValue || undefined;
    }

    me.silenceSet = function(newval){
        var oldval = val;
        val = lodash.cloneDeep(newval);

        return me;
    };

    me.set = function( newval ){
        var oldval = val;
        val = lodash.cloneDeep(newval);

        dispatch( oldval , val );

        return me;
    };

    me.get = function( index ){
        if( typeof index != 'undefined' ){
            if( lodash.isArray(val) || lodash.isObject(val) ){
                return lodash.cloneDeep( val[index] );
            }

            return val;
        }
        
        return lodash.cloneDeep(val);
    };

    me.listen = function( callable ){
        listener = callable || listener;
        return me;
    };

    me.emit = function(){
        dispatch( me.get() , me.get() );
        return me;
    };

    function dispatch( oldval , newval ){
        listener( oldval , newval );
    }
};