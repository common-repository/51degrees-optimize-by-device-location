<?php
/*
    This Original Work is copyright of 51 Degrees Mobile Experts Limited.
    Copyright 2019 51 Degrees Mobile Experts Limited, 5 Charlotte Close,
    Caversham, Reading, Berkshire, United Kingdom RG4 7BY.

    This Original Work is licensed under the European Union Public Licence (EUPL) 
    v.1.2 and is subject to its terms as set out below.

    If a copy of the EUPL was not distributed with this file, You can obtain
    one at https://opensource.org/licenses/EUPL-1.2.

    The 'Compatible Licences' set out in the Appendix to the EUPL (as may be
    amended by the European Commission) shall be deemed incompatible for
    the purposes of the Work and the provisions of the compatibility
    clause in Article 5 of the EUPL shall not apply.
*/

/**
 * Option keys used by the plugin when storing options using Wordpress'
 * get_option/update_option methods.
 */
class Options
{
 
    /**
     * Options group key for this plugin's options.
     */
    const GROUP_KEY = "fiftyonedegrees_options";
    
    /**
     * Key for storing the constructed pipeline.
     */
    const PIPELINE = "fiftyonedegrees_resource_key_pipeline";

    /**
     * Key for storing the Resource Key used by the pipeline.
     */
    const RESOURCE_KEY = "fiftyonedegrees_resource_key";

    /**
     * Key for storing a flag indicating whether the Resource Key
     * option has been updated.
     */
    const RESOURCE_KEY_UPDATED = "fiftyonedegrees_resource_key_updated";

    /**
     * Key for storing the time at which the processing result
     * cached in the session was invalidated by updating the pipeline.
     * Value is stored as an int as returned by time().
     */
    const SESSION_INVALIDATED = "fiftyonedegrees_session_invalidated";
    

    /**
     * Key for storing whether or not Google Analytics tracking is
     * enabled in the plugin.
     */
    const ENABLE_GA = "fiftyonedegrees_ga_enable_tracking";

    /**
     * Key for storing the Google Analytics access token.
     */
    const GA_TOKEN = "fiftyonedegrees_ga_access_token";

    /**
     * Key for storing the Google Analytics authorization code.
     */
    const GA_AUTH_CODE = "fiftyonedegrees_ga_auth_code";

    /**
     * Key for storing the list of Google Analytics properties.
     */
    const GA_PROPERTIES = "fiftyonedegrees_ga_properties_list";

    /**
     * Key for storing the Google Analytics tracking id.
     */
    const GA_TRACKING_ID = "fiftyonedegrees_ga_tracking_id";

    /**
     * Key for storing the Google Analytics account id.
     */
    const GA_ACCOUNT_ID = "fiftyonedegrees_ga_account_id";

    /**
     * Key for storing the maximum number of custom dimensions
     * that can be set for Google Analytics.
     */
    const GA_MAX_DIMENSIONS = "fiftyonedegrees_ga_max_cust_dim_index";

    /**
     * Key for storing an error message from Google Analytics if one
     * occurred during configuration.
     */
    const GA_ERROR = "fiftyonedegrees_ga_error";

    /**
     * Key for storing whether or not page views should be sent to
     * Google Analytics. This takes the value of 'true' or 'false'.
     */
    const GA_SEND_PAGE_VIEW = "fiftyonedegrees_ga_send_page_view";

    /**
     * Key for storing whether or no page views should be sent to
     * Google Analytics. This takes the value of 'On' or 'Off'.
     */
    const GA_SEND_PAGE_VIEW_VAL = "fiftyonedegrees_ga_send_page_view_val";

    /**
     * Key to store the list of custom dimensions sent to Google
     * Analytics.
     */
    const GA_DIMENSIONS = "fiftyonedegrees_passed_dimensions";

    /**
     * Key to store whether or not the GA_DIMENSIONS option has been
     * updated.
     */
    const GA_DIMENSIONS_UPDATED = "fiftyonedegrees_passed_dimensions_updated";

    /**
     * Key to store Google Analytics JavaScript code.
     */
    const GA_JS = "fiftyonedegrees_ga_tracking_javascript";

    /**
     * Key to store whether or not Google Analyics tracking id has been
     * updated.
     */
    const GA_ID_UPDATED = "fiftyonedegrees_ga_tracking_id_update_flag";

    /**
     * Key to store whether or not the Google Analytics send page view option
     * has been updated.
     */
    const GA_SEND_PAGE_VIEW_UPDATED = "fiftyonedegrees_ga_send_page_view_update_flag";

    /**
     * Key used to store a boolean flag if there was an error using the Google
     * Analytics tracking id.
     */
    const GA_TRACKING_ID_ERROR = "fiftyonedegrees_ga_tracking_id_error";

    /**
     * Key used to store whether or not the Google Analytics custom dimensions
     * admin screen is available. If Google Analytics has been set up correctly,
     * then this will have the value 'enabled'.
     */
    const GA_CUSTOM_DIMENSIONS_SCREEN = "fiftyonedegrees_ga_custom_dimension_screen";

    /**
     * Key used to store the map of custom dimensions to be sent to Google
     * Analytics.
     */
    const GA_CUSTOM_DIMENSIONS_MAP = "fiftyonedegrees_ga_cust_dims_map";

    /**
     * Key used to store the data at which the access token was aquired for
     * Google Analytics.
     */
    const GA_AUTH_DATE = "fiftyonedegrees_ga_auth_date";
}
?>
