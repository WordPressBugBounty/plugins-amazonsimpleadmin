jQuery( document ).ready(function($) {
    if (jQuery('#asa_content #manage_colection').length > 0) {
        asa_load_collection_shortcode();
        jQuery('#select_manage_collection').change(function () {
            asa_load_collection_shortcode();
        })

        jQuery('#manage_colection .selectable').each(function(index, element) {
            jQuery(this).on('click', function(event) {
                event.preventDefault();
                asa_select_string(element);
            });
        });
    }

    if (jQuery('.media-modal-backdrop').length > 0) {
        jQuery('.media-modal-backdrop').css('visibility', 'hidden');
    }
    if ($('#setup_reset').length > 0) {
        $('#setup_reset').click(function (event) {
            event.preventDefault();
            let url = $(event.target).attr('href');
            if (confirm('Do you really want to reset the API setup?')) {
                document.location.href = url;
            }
            return false;
        });
    }
});

function asa_checkAll() {
    var form = document.getElementById("collection-filter");
    for (i = 0, n = form.elements.length; i < n; i++) {
        if(form.elements[i].type == "checkbox" && !(form.elements[i].getAttribute('onclick',2))) {
            if(form.elements[i].checked == true)
                form.elements[i].checked = false;
            else
                form.elements[i].checked = true;
        }
    }
}

function asa_deleteCollectionItems( message ) {
    if (confirm(message)) {
        return true;
    }

    return false;
}

function asa_deleteCollection () {
    if (confirm('Do you realy want to delete this collection?')) {
        return true;
    }

    return false;
}

function asa_load_collection_shortcode () {
    var selected = jQuery( "#select_manage_collection option:selected" ).text();
    if (selected != '') {
        jQuery('#asa_collection_shortcode span').text('[asa_collection]' + selected + '[/asa_collection]');
        jQuery('#asa_collection_shortcode').show();
    } else {
        jQuery('#asa_collection_shortcode').hide();
    }
}

function asa_set_latest (id, message) {

    if (confirm(message)) {
        return true;
    }

    return false;
}

function asa_select_string(element) {
    var doc = document
        , text = element
        , range, selection
        ;
    if (doc.body.createTextRange) {
        range = document.body.createTextRange();
        range.moveToElementText(text);
        range.select();
    } else if (window.getSelection) {
        selection = window.getSelection();
        range = document.createRange();
        range.selectNodeContents(text);
        selection.removeAllRanges();
        selection.addRange(range);
    }
}

String.prototype.hashCode = function() {
    var hash = 0, i, chr;
    if (this.length === 0) return hash;
    for (i = 0; i < this.length; i++) {
        chr   = this.charCodeAt(i);
        hash  = ((hash << 5) - hash) + chr;
        hash |= 0; // Convert to 32bit integer
    }
    return hash;
};