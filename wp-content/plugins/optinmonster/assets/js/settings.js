/* ==========================================================
 * edit.js
 * ==========================================================
 * Copyright 2015 Thomas Griffin.
 * https://thomasgriffin.io
 * ========================================================== */
jQuery(document).ready(function ($) {

    // Initialize Select2.
    omapiSelect();

    // Hide/show any state specific settings.
    omapiToggleSettings();

    // Support Toggles on content
    omapiSettingsToggle();

    // Confirm resetting settings.
    omapiResetSettings();

    // Copy to clipboard Loading
    omapiClipboard();

    // Recognize Copy to Clipboard Buttons
    omapiCopytoClipboardBtn();

    // Support PDF generation
    omapiBuildSupportPDF();

    // Run Tooltip lib on any tooltips
    omapiFindTooltips();


    /**
     * Dynamic Toggle functionality
     */
    function omapiSettingsToggle() {

        $('.omapi-ui-toggle-controller').click(function () {
            $(this).toggleClass("toggled");
            $(this).siblings(".omapi-ui-toggle-content").toggleClass("visible");
        });

    }

    /**
     * Confirms the settings reset for the active tab.
     *
     * @since 1.0.0
     */
    function omapiResetSettings() {
        $(document).on('click', 'input[name=reset]', function (e) {
            return confirm(omapi.confirm);
        });
    }

    /**
     * Toggles the shortcode list setting.
     *
     * @since 1.1.4
     */
    function omapiToggleSettings() {
        var shortcode_val = $('#omapi-field-shortcode').is(':checked');
        if (!shortcode_val) {
            $('.omapi-field-box-shortcode_output').hide();
        }
        $(document).on('change', '#omapi-field-shortcode', function (e) {
            if ($(this).is(':checked')) {
                $('.omapi-field-box-shortcode_output').show(0);
            } else {
                $('.omapi-field-box-shortcode_output').hide(0);
            }
        });

        var mailpoet_val = $('#omapi-field-mailpoet').is(':checked');
        if (!mailpoet_val) {
            $('.omapi-field-box-mailpoet_list').hide();
        }
        $(document).on('change', '#omapi-field-mailpoet', function (e) {
            if ($(this).is(':checked')) {
                $('.omapi-field-box-mailpoet_list').show(0);
            } else {
                $('.omapi-field-box-mailpoet_list').hide(0);
            }
        });

        var automatic_val = $('#omapi-field-automatic').is(':checked');
        if (automatic_val) {
            $('.omapi-field-box-automatic_shortcode').hide();
        }
        $(document).on('change', '#omapi-field-automatic', function (e) {
            if ($(this).is(':checked')) {
                $('.omapi-field-box-automatic_shortcode').hide(0);
            } else {
                $('.omapi-field-box-automatic_shortcode').show(0);
            }
        });
    }


    /**
     * Initializes the Select2 replacement for select fields.
     *
     * @since 1.0.0
     */
    function omapiSelect() {
        $('.omapi-select-ajax').each(function (i, el) {
            var ajax_action = $(this).attr('id').indexOf('taxonomies') > -1 ? 'omapi_query_taxonomies' : 'omapi_query_posts',
                init_action = 'omapi_query_taxonomies' == ajax_action ? 'omapi_query_selected_taxonomies' : 'omapi_query_selected_posts';
            $(this).select2({
                minimumInputLength: 1,
                multiple: true,
                ajax: {
                    url: omapi.ajax,
                    dataType: 'json',
                    type: 'POST',
                    quietMillis: 250,
                    data: function (term, page) {
                        return {
                            action: ajax_action,
                            q: term,
                            nonce: omapi.nonce
                        };
                    },
                    results: function (data, page) {
                        return {results: data.items};
                    }
                },
                initSelection: function (el, cb) {
                    var ids = $(el).val(),
                        data = {
                            action: init_action,
                            ids: ids,
                            nonce: omapi.nonce
                        };
                    $.post(omapi.ajax, data, function (data) {
                        cb(data.items);

                    }, 'json');
                },
                formatResult: omapiFormatResult,
                formatSelection: omapiFormatSelection,
                escapeMarkup: function (m) {
                    return m;
                }
            }).on('change select2-removed', function () {

            });
        });
    }

    /**
     * Formats the queried post selection for Select2.
     *
     * @since 1.0.0
     */
    function omapiFormatResult(item) {
        var markup = '';
        if (item.title !== undefined) {
            markup += '<div value="' + item.id + '">' + item.title + '</div>';
        }
        return markup;
    }

    /**
     * Formats the queried post selection for Select2.
     *
     * @since 1.0.0
     */
    function omapiFormatSelection(item) {
        return item.title;
    }

    /**
     * Generate support PDF from localized data
     *
     * @since 1.1.5
     */
    function omapiBuildSupportPDF() {
        var selector = $('#js--omapi-support-pdf');

        selector.click(function (e) {
            e.preventDefault();

            var doc = new jsPDF('p', 'mm', 'letter');

            var supportData = omapi.supportData;
            var serverData = supportData.server;
            var optinData = supportData.optins;

            // Doc Title
            doc.text(10, 10, 'OptinMonster Support Assistance');

            // Server Info
            i = 10;
            $.each(serverData, function (key, value) {
                i += 10;
                doc.text(10, i, key + ' : ' + value);
            });

            // Optin Info
            $.each(optinData, function (key, value) {

                //Move down 10mm
                i = 10;
                // Add a new page
                doc.addPage();
                //Title as slug
                doc.text(10, 10, key);
                $.each(value, function (key, value) {

                    // Keep from outputing ugly Object text
                    output = ( $.isPlainObject(value) ? '' : value );
                    // new line
                    i += 10;
                    doc.text(10, i, key + ' : ' + output);
                    //Output any object data from the value
                    if ($.isPlainObject(value)) {
                        $.each(value, function (key, value) {
                            i += 10;
                            doc.text(20, i, key + ' : ' + value);
                        });
                    }
                });

            });

            // Save the PDF
            doc.save('OMSupportHelp.pdf');

        });
    }

    /**
     * Clipboard Helpers
     *
     * @since 1.1.5
     */
    function omapiClipboard() {
        var ompaiClipboard = new Clipboard('.omapi-copy-button');

        ompaiClipboard.on('success', function (e) {
            setTooltip(e.trigger, 'Copied to Clipboard!');
            hideTooltip(e.trigger);
        });
        ompaiClipboard.on('error', function (e) {
            var fallbackMessage = '';

            if(/iPhone|iPad/i.test(navigator.userAgent)) {
                fallbackMessage = 'Unable to Copy on this device';
            }
            else if (/Mac/i.test(navigator.userAgent)) {
                fallbackMessage = 'Press ⌘-C to Copy';
            }
            else {
                fallbackMessage = 'Press Ctrl-C to Copy';
            }
            setTooltip(e.trigger, fallbackMessage);
            hideTooltip(e.trigger);
        });
    }

    /**
     * Standardize Copy to clipboard button
     *
     * @since 1.1.5
     */
    function omapiCopytoClipboardBtn() {
        $('omapi-copy-button').tooltip({
            trigger: 'click',
            placement: 'top',

        });
    }
    /**
     * Set BS Tooltip based on Clipboard data
     *
     * @since 1.1.5
     * @param btn
     * @param message
     */
    function setTooltip(btn, message) {
        $(btn).attr('data-original-title', message)
            .tooltip('show');
    }

    /**
     * Remove tooltip after Clipboard message shown
     *
     * @since 1.1.5
     * @param btn
     */
    function hideTooltip(btn) {
        setTimeout(function() {
            $(btn).tooltip('destroy');
        }, 2000);
    }

    function omapiFindTooltips() {
        $('[data-toggle="tooltip"]').tooltip()
    }



});