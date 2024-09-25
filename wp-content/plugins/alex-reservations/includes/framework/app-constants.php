<?php

/**
 * CHANGE BASED ON THE PLUGIN
 */

// When resolving route skip the nonce field to allow test with direct calls
const EVAVEL_SKIP_ROUTE_NONCE_CHECK = false;

// Se usan en la framework
const EVAVEL_NONCE = 'alex-reservations';
const EVAVEL_DB_NAMESPACE = 'srr';
const EVAVEL_WPJSON_NAMESPACE = 'srr/v1';
const EVAVEL_CUSTOM_TRANSLATION_PATH = ALEXR_CUSTOM_TRANSLATION_PATH;
const EVAVEL_DIR_TRANSLATIONS = ALEXR_PLUGIN_DIR . 'includes/dashboard/translations/';

// Se usan en la aplicacion
const ALEXR_DIR_CONFIG_FILES = ALEXR_PLUGIN_DIR . 'includes/application/config/';

const ALEXR_DIR_TEMPLATES_EMAIL = ALEXR_PLUGIN_DIR . 'includes/dashboard/templates/email/';
const ALEXR_DIR_TEMPLATES_SMS = ALEXR_PLUGIN_DIR . 'includes/dashboard/templates/sms/';

const ALEXR_DIR_TEMPLATES_EMAIL_REMINDERS = ALEXR_PLUGIN_DIR . 'includes/dashboard/templates/email-reminders/';
const ALEXR_DIR_TEMPLATES_SMS_REMINDERS = ALEXR_PLUGIN_DIR . 'includes/dashboard/templates/sms-reminders/';

const ALEXR_DIR_TEMPLATES_WIDGET_MESSAGES = ALEXR_PLUGIN_DIR . 'includes/dashboard/templates/widget-messages/';


// Log files Class Log
const EVAVEL_LOG_DOWNLOAD_PARAM = 'download_ardashboard';
const EVAVEL_LOG_NONCE_TEXT = 'alexr-download-log';
const EVAVEL_LOG_WP_OPTION = '_srr_enable_log_files';

// Use vite dev
//const EVAVEL_USE_VITE_DEVTOOLS = false;
//const EVAVEL_USE_VITE_DEVTOOLS_WP_USER = 1;

