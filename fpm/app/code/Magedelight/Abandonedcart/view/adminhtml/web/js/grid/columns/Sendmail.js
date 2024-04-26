require(
    [
        'jquery',
        'Magento_Ui/js/modal/modal'
    ],
    function (
        $,
        modal
    ) {
        var options = {
            type: 'popup',
            title: 'Contact Form',
            buttons: [{
                text: $.mage.__('Close'),
                class: '',
                click: function () {
                    this.closeModal();
                }
            }]
        };

        var popup = modal(options, $('#popup-modal'));
        $("#click-me").on('click', function () {
            $("#popup-modal").modal("openModal");
        });

    }
);