var ViewConstants = require('../constants/view-constants.js');
var ViewDispatcher = require('../dispatcher/view-dispatcher.js');
//var ViewActions = require('../actions/view-actions.js');

/**
 * Helper function to dispatch
 * @param action Action type constant
 * @param values
 */
function updateSettings( action, values ) {
    ViewDispatcher.dispatch({
        actionType: action,
        values: values
    });
}


//todo: it should be possible to call the ViewActions
function apiOpenPanel( id, returnId, args = null ) {
    ViewDispatcher.dispatch({
        actionType: ViewConstants.PANEL_OPEN,
        panelId: id,
        returnId: returnId,
        extraArgs: args
    });
}

var ViewApi = {

    // *** View Settings *** //

    /**
     * Fetch the list of the available forms
     */
    getFormsList: function() {
        updateSettings( ViewConstants.UPDATE_FORMS_LIST, gravityview_view_settings.forms_list );
    },

    /**
     * Fetch the configured forms assigned to the view
     */
    getConfiguredActiveForms: function() {
        updateSettings( ViewConstants.UPDATE_FORMS_ACTIVE, gravityview_view_settings.forms );
    },

    /**
     * Fetch Settings Sections from server
     *
     * @param forms
     * @param templates
     */
    getSettingsSections: function( forms, templates ) {
        updateSettings( ViewConstants.UPDATE_SETTINGS_SECTIONS, gravityview_view_settings.settings_sections );
    },

    /**
     * Fetch Settings Inputs from server
     * @param forms
     * @param templates
     */
    getSettingsInputs: function( forms, templates ) {
        // todo: fetch using AJAX. When form changes or template, the settings inputs may change

        updateSettings( ViewConstants.UPDATE_SETTINGS_INPUTS, gravityview_view_settings.settings_inputs );
    },

    /**
     * Fetch saved Settings Values from server
     */
    getSettingsAllValues: function() {
        updateSettings( ViewConstants.UPDATE_SETTINGS_ALL, gravityview_view_settings.settings_values );
    },


    // *** View Fields *** //

    getSavedLayout: function() {

        var data = {
            action: 'gv_get_saved_layout',
            view: jQuery('#post_ID').val(),
            nonce: gvGlobals.nonce
        };

        jQuery.ajax( {
            type: 'POST',
            url: ajaxurl,
            data: data,
            dataType: 'json',
            async: true
        } ).done( function ( response ) {
            updateSettings( ViewConstants.UPDATE_LAYOUT_ALL, response.data );
        } ).fail( function ( jqXHR ) {
            console.log( jqXHR );
        } ).always( function () {
            //
        } );
    },

    /**
     * Fetch Fields Sections from server
     *
     * @param forms
     * @param templates
     */
    getFieldsSections: function( forms, templates ) {
        updateSettings( ViewConstants.UPDATE_FIELDS_SECTIONS, gravityview_view_settings.fields_sections );
    },

    /**
     * Fetch Fields List for the active forms
     *
     * @param forms
     */
    getFieldsList: function( forms ) {

        var data = {
            action: 'gv_get_fields_list',
            forms: forms,
            nonce: gvGlobals.nonce
        };

        jQuery.ajax( {
            type: 'POST',
            url: ajaxurl,
            data: data,
            dataType: 'json',
            async: true
        } ).done( function ( response ) {
            updateSettings( ViewConstants.UPDATE_FIELDS_LIST, response.data );
        } ).fail( function ( jqXHR ) {
            console.log( jqXHR );
        } ).always( function () {
            //
        } );

    },

    /**
     * Get the field settings array
     * @param args object Pointer containing 'context', 'row', 'col' and 'field' (field_id, form_id, field_type, ..)
     */
    getFieldSettingsValues: function( args ) {

        var data = {
            action: 'gv_get_field_settings_values',
            //template: templateId,
            context: args.context,
            field_id: args.field['field_id'],
            field_type: args.field['field_type'],
            field_label: args.field['field_label'],
            form_id: args.field['form_id'],
            nonce: gvGlobals.nonce
        };

        jQuery.ajax( {
            type: 'POST',
            url: ajaxurl,
            data: data,
            dataType: 'json',
            async: true
        } ).done( function ( response ) {

            var values = {
                pointer: args,
                settings: response.data
            };
            updateSettings( ViewConstants.UPDATE_FIELD_SETTINGS, values );
        } ).fail( function ( jqXHR ) {
            console.log( jqXHR );
        } ).always( function () {
            //
        } );

    },

    /**
     * Fetch field settings, and open the field settings panel when loaded.
     * @param args Object Field arguments ( context, row, col, field [id, field_id, form_id, field_type, gv_settings] )
     */
    getFieldSettings: function( args ) {

        var data = {
            action: 'gv_get_field_settings',
            //template: templateId,
            context: args.context,
            field_id: args.field['field_id'],
            field_type: args.field['field_type'],
            form_id: args.field['form_id'],
            nonce: gvGlobals.nonce
        };

        jQuery.ajax( {
            type: 'POST',
            url: ajaxurl,
            data: data,
            dataType: 'json',
            async: true
        } ).done( function ( response ) {

            var values = {
                pointer: args,
                settings: response.data
            };

            apiOpenPanel( ViewConstants.PANEL_FIELD_SETTINGS, false, values );
        } ).fail( function ( jqXHR ) {
            console.log( jqXHR );
        } ).always( function () {
            //
        } );
    }

};

module.exports = ViewApi;