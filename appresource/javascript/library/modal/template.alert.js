import _ from 'lodash';

export default function( options ){
    options = options || {};
    var defaultConfig = {
        title   : '',
        message : ''
    };

    var config = _.extend({} , defaultConfig , options);

    var btnClose = `
        <button type="button" class="btn-decision-close close" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;

    var header = `
        <div class="modal-header">
            <h5 class="modal-title">${config.title}</h5>
            ${config.closeButton ? btnClose : ''}
        </div>
    `;

    return `
        <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    ${ config.title.length <= 0 && !config.closeButton ? '' : header }
                    <div class="modal-body">
                        ${config.message}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
};