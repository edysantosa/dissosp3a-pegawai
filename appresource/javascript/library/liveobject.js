export default LiveObject;

function LiveObject(){}

LiveObject.events = {};

LiveObject.handler = {
    set : function(target , property , value){
        var oldval = typeof target[property] == 'undefined' ? undefined : target[property];
        target[property] = value;

        if( property == '__events' || property == 'watch' ){
            return true;
        }

        LiveObject.emit( target , property , oldval , value);
        return true;
    },

    get : function(target , key){
        return target[key];
    },

    deleteProperty : function(target , property){
        delete target[property];

        if( typeof target.__events[property] !== 'undefined' ){
            delete target.__events[property];
        }
    }
};

LiveObject.init = function( defaultValue ){
    defaultValue = defaultValue || {};

    defaultValue.__events = {};
    var $obj = new Proxy( defaultValue , LiveObject.handler );

    $obj.watch = function( property , callable ){
        LiveObject.watch($obj , property , callable);
    };

    return $obj;
};

LiveObject.watch = function( target , property , callable ){
    callable = callable || function(){};
    target.__events[property] = callable;
};

LiveObject.emit = function( target , property , oldval , newval ){
    if( typeof target.__events[property] !== 'undefined' ){
        target.__events[property]( oldval , newval );
    }
};