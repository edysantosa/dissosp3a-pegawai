export default function( options ){

    options = options || {};
    var defaultConfig = {
        title   : '',
        message : '',
        yesText : 'Yes',
        noText  : 'Cancel'
    };

    var config = _.extend({} , defaultConfig , options);

    var btnClose = `
        <button type="button" class="btn-decision-close close" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;

    var header = `
        <div class="modal-title"><i class="fa fa-info-circle"></i> ${config.title}</div>
        ${config.closeButton ? btnClose : ''}
    `;

    return `
        <div class="modal max-modal modal-confirmation fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        ${ config.title.length <= 0 && !config.closeButton ? '' : header }
                    </div>
                    <div class="modal-body">
                        ${config.message}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-decision" data-decision="yes">${config.yesText}</button>
                        <button type="button" class="btn btn-outline-secondary btn-decision" data-decision="no">${config.noText}</button>
                    </div>
                </div>
            </div>
        </div>
    `;
};