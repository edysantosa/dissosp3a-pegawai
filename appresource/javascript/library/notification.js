import '../limitless/js/main/jquery.min.js';
import PNotify from '../limitless/js/plugins/notifications/pnotify.min.js';

export default Notification;

function Notification(options){
    options = options || {};
    var config = $.extend({} , Notification.defaultOptions , options);
    return new PNotify(config);    
}

Notification.defaultOptions = {
    title: 'Notice',
    text: ''
};

Notification.primary = function(config){
    if( config === Object(config) ){
        config.addclass = 'bg-primary border-primary'
        return Notification(config);
    }

    return Notification({
        title   : 'Notification',
        text    : config,
        addclass: 'bg-primary border-primary'
    });
}    

Notification.info = function(config){
    if( config === Object(config) ){
        config.addclass = 'bg-info border-info'
        return Notification(config);
    }

    return Notification({
        title   : 'Info',
        text    : config,
        addclass: 'bg-info border-info'
    });
}     

Notification.success = function(config){
    if( config === Object(config) ){
        config.addclass = 'bg-success border-success'
        return Notification(config);
    }

    return Notification({
        title   : 'Success',
        text    : config,
        addclass: 'bg-success border-success'
    });
}

Notification.error = function(config){
    if( config === Object(config) ){
        config.addclass = 'bg-danger border-danger'
        return Notification(config);
    }

    return Notification({
        title   : 'Error',
        text    : config,
        addclass: 'bg-danger border-danger'
    });
}

Notification.warning = function(config){
    if( config === Object(config) ){
        config.addclass = 'bg-warning border-warning'
        return Notification(config);
    }

    return Notification({
        title   : 'Warning',
        text    : config,
        addclass: 'bg-warning border-warning'
    });
}

