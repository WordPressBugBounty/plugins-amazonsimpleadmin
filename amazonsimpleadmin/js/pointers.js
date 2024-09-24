jQuery(document).ready( function($) {
    asa_open_pointer(0);
    function asa_open_pointer(i) {
        var pointer = asaPointer.pointers[i];
        var options = $.extend( pointer.options, {
            close: function() {
                $.post( ajaxurl, {
                    pointer: pointer.pointer_id,
                    action: 'dismiss-wp-pointer'
                });
            }
        });
        $(pointer.target).pointer( options ).pointer('open');
    }
});