import lodash from 'lodash';
import store from 'store';
import sitebase from './sitebase';
export default Cart;

function Cart( storageProperty ){
    var me          = this;
    var cartField   = storageProperty || 'cart';
    var enable      = true;
    var event       = function(){};

    if( typeof window.cartLightHouse == 'undefined' ){
        window.cartLightHouse = document.createTextNode('');
    }

    me.add      = function( id , item ){
        var basket = fetch();
        var oldval = lodash.cloneDeep(basket);
        basket[id] = lodash.cloneDeep( item );
        
        set(lodash.cloneDeep(basket));

        me.emit(oldval , basket);
        return me;
    };

    me.set  = function( data ){
        var basket = fetch();
        var oldval = lodash.cloneDeep(basket);

        set(lodash.cloneDeep(data));

        me.emit( oldval , lodash.cloneDeep(data) );
        return me;
    };

    me.get      = function( id ){
        var basket = lodash.cloneDeep(fetch());

        if( typeof id == 'undefined' ){
            return basket;
        }

        if( typeof basket[id] == 'undefined' ){
            return undefined;
        }

        return basket[id];
    };

    me.clearAll = function(){
        store.remove( sitebase.cartField );
    };

    me.clearCurrent = function(){
        var oldval = fetch();
        var basket = {}

        set( lodash.cloneDeep(basket) );
        setTopUp(lodash.cloneDeep(basket));
        
        me.emit(oldval , basket);
        return me;
    };

    me.remove   = function( id ){
        var oldval = fetch();
        var basket = lodash.cloneDeep(oldval);

        delete basket[id];

        set( lodash.cloneDeep(basket) );

        me.emit(oldval , basket);
        return me;
    };

    me.clear    = function(){
        var oldval = lodash.cloneDeep(fetch());
        var basket = {};

        set(basket);
        setTopUp(basket);

        me.emit(oldval , basket);
        return me;
    };

    me.listen = function( callable ){
        event = callable;

        window.cartLightHouse.addEventListener('cart:change' , function( e ){
            event(e.detail.oldval , e.detail.newval);
        });

        return me;
    };

    me.emit = function( oldval , newval ){
        if( !enable ){
            return me;
        }

        oldval = oldval || lodash.cloneDeep(fetch());
        newval = newval || lodash.cloneDeep(fetch());

        window.cartLightHouse.dispatchEvent(new CustomEvent('cart:change' , {
            detail : {
                oldval : oldval,
                newval : newval
            }
        }));

        return me;
    };

    me.disable = function(){
        enable = false;
        return me;
    };

    me.enable = function(){
        enable = true;
        return me;
    };

    function fetch( field ){
        field = field || cartField;

        if( typeof sitebase.delivery == 'undefined' ){
            return {};
        }

        if( sitebase.delivery.length <= 0 ){
            return {};
        }

        var stored = getStored({});

        if( typeof stored[ sitebase.delivery ] == 'undefined' ){
            return {};
        }

        return stored[ sitebase.delivery ];
    }

    function set( data ){
        var stored = getStored({});
        stored[sitebase.delivery] = data;
        setStored( stored );
    }

}

function getStored( defaultValue ){
    defaultValue = defaultValue || {};
    var stored = store.get( sitebase.cartField , defaultValue );

    if( sitebase.cust in stored ){
        return stored[ sitebase.cust ];
    }

    return {};
}

function setStored( val ){
    var stored = store.get( sitebase.cartField , {});
    stored[ sitebase.cust ] = val;
    store.set( sitebase.cartField , stored );
}