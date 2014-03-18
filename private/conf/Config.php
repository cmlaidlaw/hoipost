<?php

define( 'INSTALLED', true );

define( 'HOSTNAME', '(e.x. `www.example.com`)' );

define( 'DEBUG', false );

define( 'ALLOW_USER_MESSAGES', false );

/*******************************************************************************
* Back-end system constants                                                    *
*******************************************************************************/

//internal directories
define( 'BASE_DIR', 'FILESYSTEM PATH TO THE DIRECTORY CONTAINING `public/` and `private/`' );
define( 'PUBLIC_DIR', BASE_DIR . 'public/' );
define( 'PRIVATE_DIR', BASE_DIR . 'private/' );
define( 'LIB_DIR', PRIVATE_DIR . 'lib/' );
define( 'LOG_DIR', PRIVATE_DIR . 'log/' );
define( 'API_DIR', PRIVATE_DIR . 'api/1.1/' );
define( 'THUMB_DIR', PUBLIC_DIR . 'media/thumb/' );
define( 'PIC_DIR', PUBLIC_DIR . 'media/full/' );

//external paths
define( 'BASE_PATH', 'https://' . HOSTNAME . '/' );
define( 'API_PATH', BASE_PATH . 'api/1.1/' );
define( 'THUMB_PATH', BASE_PATH . 'media/thumb/' );
define( 'PIC_PATH', BASE_PATH . 'media/full/' );

//runtime directives
ini_set( 'error_reporting', E_ALL ); //report errors
ini_set( 'log_errors', '1' ); //and do log them...
ini_set( 'error_log', LOG_DIR . 'rawerror.log' ); //...to this file
ini_set('display_errors', '0'); //but don't show them
ini_set( 'ignore_repeated_errors', '1' ); //ignore repeated errors
mb_internal_encoding( 'UTF-8' ); //set the internal string encoding

//database info
define( 'DB_HOST', '' );
define( 'DB_USER', '' );
define( 'DB_PASS', '' );
define( 'DB_OPEN', '' );

//global program constants
define( 'UPLOAD_MAX_SIZE', 2097152 );
define( 'CUSTOM_EPOCH_OFFSET', 1366502400 );
define( 'TEL_MAX_LENGTH', 16 );
define( 'EMAIL_MAX_LENGTH', 256 );
define( 'URL_MAX_LENGTH', 256 );

//api constants
define( 'API_RESULT_COUNT_DEFAULT', 20 );
define( 'API_RESULT_COUNT_MAX', 50 );
define( 'API_AUTH_SIGN_IN_GATEWAY', API_PATH . 'auth/in/' );
define( 'API_AUTH_SIGN_OUT_GATEWAY', API_PATH . 'auth/out/' );
define( 'API_AUTH_PASSWORD_SALT', '' );

//cookie handling
define( 'COOKIE_DOMAIN', HOSTNAME );
define( 'COOKIE_TOKEN_SALT', '' );
define( 'COOKIE_EXPIRATION_DELAY', 86400 ); //24 hours
define( 'COOKIE_RECYCLE_WINDOW', 3600 ); //1 hour

/*******************************************************************************
* Back-end object constants                                                    *
*******************************************************************************/

/*
 *
 * Object types:
 * 1: Message  (+1)
 * 2: Business (+2)
 * 6: Event    (+3)
 * Each type is the sum of the component parts... i.e. an Event has a Message,
 * Business and Event component so (1 + 2 + 3) = 6
 *
 */

//position
define( 'GEOHASH_MAX_LENGTH', 16 );
define( 'DEFAULT_LATITUDE', 25.00 );
define( 'DEFAULT_LONGITUDE', -71.00 );

//image
define( 'IMAGE_THUMB_MAX_WIDTH', 180 );
define( 'IMAGE_THUMB_MAX_HEIGHT', 270 );
define( 'IMAGE_THUMB_MAX_RATIO', 0.75 );
define( 'IMAGE_FULL_MAX_WIDTH', 800 );
define( 'IMAGE_FILENAME_SALT', '' );

//message
define( 'MESSAGE_MAX_LENGTH', 1024 );
define( 'MESSAGE_NUMBER_OF_THEMES', 6 );
define( 'MESSAGE_DEFAULT_THEME', 1 );
define( 'TAG_MIN_LENGTH', 3 );
define( 'TAG_MAX_LENGTH', 16 );

//business
define(
    'ESTABLISHMENT_AVAILABLE_CITIES',
    'hong kong,guangzhou'
);
define(
    'ESTABLISHMENT_AVAILABLE_CATEGORIES',
    'unassigned,retail,f&b,nightlife,transport,convenience,parks&rec,landmarks&culture'
);
define( 'ESTABLISHMENT_NAME_MAX_LENGTH', 256 );
define( 'ESTABLISHMENT_DESCRIPTION_MAX_LENGTH', 512 );
define( 'ESTABLISHMENT_ADDRESS_MAX_LENGTH', 512 );

/*******************************************************************************
* Back-end service constants                                                   *
*******************************************************************************/

//service
define( 'SERVICE_NUMBER_OF_LEVELS', 1 );
define( 'SERVICE_LEVEL_0_MAX_EVENTS', 10 );
define( 'SERVICE_LEVEL_0_MAX_EVENTS_PER_RESULT_SET', 1 );
define( 'SERVICE_LEVEL_1_MAX_EVENTS', 20 );
define( 'SERVICE_LEVEL_1_MAX_EVENTS_PER_RESULT_SET', 3 );
define( 'SERVICE_LEVEL_2_MAX_EVENTS', 20 ); //admin, not included in SERVICE_NUMBER_OF_LEVELS
define( 'SERVICE_LEVEL_2_MAX_EVENTS_PER_RESULT_SET', 3 );

//vouchers
define( 'VOUCHER_CODE_CHARACTER_SET', 'ABCDEFGHKLMNOPQRSTUWXYZ123456789' );
define( 'VOUCHER_CODE_LENGTH', 10 );

/*******************************************************************************
* Front-end constants                                                          *
*******************************************************************************/

//page errors
define( 'ERROR_BASE_PATH', BASE_PATH . 'error/' );
define( 'ERROR_REDIRECT_PATH', 'error/' );

//auth locations
define( 'AUTH_SIGN_IN_FORM', 'sign-in/' );
define( 'AUTH_SIGN_IN_LANDING', 'dashboard/' );
define( 'AUTH_SIGN_OUT_FORM', 'sign-out/' );
define( 'AUTH_SIGN_OUT_LANDING', 'signed-out/' );

//dashboard locations
define( 'DASHBOARD_BASE_PATH', BASE_PATH . 'dashboard/' );
define( 'DASHBOARD_REDIRECT_PATH', 'dashboard/' );

//file versioning
define( 'MAIN_CSS_LATEST_REVISION', '31072013' );
define( 'IE8_CSS_LATEST_REVISION', '29052013' );
define( 'CJK_CSS_LATEST_REVISION', '29052013' );
define( 'JS_LATEST_REVISION', '05022013' );
define( 'UTIL_JS_LATEST_REVISION', '29052013' );
