import 'jquery';
import '../limitless/js/main/bootstrap.bundle.min.js';
import templates from './modal/templates';
export default Modal;

Modal.alert     = ModalAlert;
Modal.confirm   = ModalConfirm;
Modal.prompt    = ModalPrompt;
Modal.dialog    = ModalCustom;
Modal.closeAll  = function(){
    $('.modal').modal('hide');
    return Modal;
};

Modal.defaults = { config : {
    closeButton : true,
    backdrop    : true, 
    keyboard    : true,
    focus       : true,
    show        : true
} };

function Modal( options ){
    var deferred = $.Deferred();
    var me = this;
    var template;
    var shake = null;
    var shakeData = undefined;
    
    var config = $.extend({} , Modal.defaults.config , options );

    switch( config.type ){
        case "alert" : 
            template = templates.alert(config);
        break;
        case "confirm" : 
            template = templates.confirm(config);
        break;
        case "custom" :
            template = templates.custom(config);
        break;
    }

    me.element = undefined;

    me.show = function(init){
        return render(init);
    };

    me.done = function( data ){
        //data = data || undefined;
        me.element.modal('hide');
        //deferred.resolve(data);
        shake = true;
        shakeData = arguments;
        return me;
    };

    me.cancel = function(){
        //data = data || undefined;
        me.element.modal('hide');
        //deferred.reject(data);
        shake = false;
        shakeData = arguments;
        return me;
    };

    me.override = function( dcontent ){
        if( me.element.find('.modal-custom').length > 0 ){
            me.element.find('.modal-custom').html( dcontent );
        }

        return me;
    };

    me.promise = function(){
        return deferred.promise();
    };

    function render( initiator ){
        initiator = initiator || function(){};
        me.element = $(template);
        $('body').append(me.element);

        me.element.modal(config);        

        events();

        initiator.apply(me , []);

        if( config.type.toLowerCase() == "alert" || 
            config.type.toLowerCase() == "custom" ){
            return me;
        }

        if( config.type.toLowerCase() == "confirm" ||
            config.type.toLowerCase() == "prompt" ){
            return deferred.promise();
        }
    }

    function events(){
        me.element.find('.modal-header').css({
            'border-bottom' : '0'
        });

        me.element.find('.modal-footer').css({
            'border-top' : '0'
        });

        me.element.find('.modal-dialog.modal-full').css({
            'max-width' : '96%'
        });

        me.element.on('click' , '.btn-decision-close.close' , function( ev ){
            ev.preventDefault();
            me.cancel();
        });

        me.element.on('hidden.bs.modal' , function( ev ){
            $(this).remove();

            if( shake === true ){
                deferred.resolve.apply( deferred , shakeData );
                shake = null;
                shakeData = undefined;
                return;
            }

            if( shake === false ){
                deferred.reject.apply( deferred , shakeData );
                shake = null;
                shakeData = undefined;
                return;
            }

            deferred.reject();
            shake = null;
            shakeData = undefined;
        });

        if( config.type.toLowerCase() == "confirm" ){
            me.element.on('click' , '.btn-decision' , function(){
                if( $(this).data('decision') == "yes" ){
                    me.done();
                }else{
                    me.cancel();
                }
            });
        }

        if( config.type.toLowerCase() == "prompt" ){
            me.element.on('click' , '.btn-decision' , function(){
                if( $(this).data('decision') == "yes" ){
                    me.done();
                }else{
                    me.cancel();
                }
            });
        }
    }
}

function ModalAlert( message , title ){
    var config = {
        type : 'alert',
        message : message
    };

    if( typeof title != 'undefined' ){
        config.title = title;
    }

    return (new Modal(config)).show();
}

function ModalConfirm( message , title , config ){
    config = config || {};

    config.backdrop = 'static';
    config.closeButton = false;
    config.keyboard = config.keyboard || false;
    config.type = 'confirm';
    config.message = message;

    if( typeof title != 'undefined' ){
        config.title = title;
    }

    return (new Modal(config)).show();
}

function ModalCustom( options ){
    options.type = "custom";
    return new Modal(options);
}

function ModalPrompt(){}