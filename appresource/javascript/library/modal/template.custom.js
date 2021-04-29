export default function( options ){

    options = options || {};

    var defaultConfig = {
        size    : '',
        title   : '',
        message : ''
    };

    var config = _.extend({} , defaultConfig , options);

    config.subtitle = config.subtitle || '';

    var btnClose = `
        <button type="button" class="btn-decision-close close" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;

    var header = `
        <div class="modal-header">
            <div class="modal-title">
                <h5 class="mb-0">${config.title}</h5>
                <small>${config.subtitle}</small>
            </div>
            ${config.closeButton ? btnClose : ''}
        </div>
    `;

    var size = (function( size ){
        if( size.toLowerCase() == 'extralarge' )
            return 'modal-xl';

        if( size.toLowerCase() == 'large' )
            return 'modal-lg';
        
        if( size.toLowerCase() == 'small' )
            return 'modal-sm';

        if( size.toLowerCase() == 'full' )
            return 'modal-full';

        return '';
    })( config.size );

    return `
        <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog${ size.length > 0 ? " " + size : "" }" role="document">
                <div class="modal-content">
                    ${ config.title.length <= 0 && !config.closeButton ? '' : header }
                    <div class="modal-custom modal-body">
                        ${config.message}
                    </div>
                    <div class="modal-footer"></div>
                </div>
            </div>
        </div>
    `;
};