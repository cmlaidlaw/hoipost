<?php

class Dashboard {

    public static function validateNotification( $notification ) {

        $cleanNotification = null;

        switch ( $notification ) {
            case 'a_create_ok':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_ACCOUNT_CREATE_SUCCESS',
                    'status' => 'success'
                );
                break;
            case 'a_create_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_ACCOUNT_CREATE_ERROR',
                    'status' => 'error'
                );
                break;
            case 'a_enable_ok':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_ACCOUNT_ENABLE_SUCCESS',
                    'status' => 'success'
                );
                break;
            case 'a_enable_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_ACCOUNT_ENABLE_ERROR',
                    'status' => 'error'
                );
                break;
            case 'a_disable_ok':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_ACCOUNT_DISABLE_SUCCESS',
                    'status' => 'success'
                );
                break;
            case 'a_disable_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_ACCOUNT_DISABLE_ERROR',
                    'status' => 'error'
                );
                break;
            case 'a_delete_ok':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_ACCOUNT_DELETE_SUCCESS',
                    'status' => 'success'
                );
                break;
            case 'a_delete_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_ACCOUNT_DELETE_ERROR',
                    'status' => 'error'
                );
                break;
            case 'm_enable_ok':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_MESSAGE_ENABLE_SUCCESS',
                    'status' => 'success'
                );
                break;
            case 'm_enable_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_MESSAGE_ENABLE_ERROR',
                    'status' => 'error'
                );
                break;
            case 'm_disable_ok':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_MESSAGE_DISABLE_SUCCESS',
                    'status' => 'success'
                );
                break;
            case 'm_disable_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_MESSAGE_DISABLE_ERROR',
                    'status' => 'error'
                );
                break;
            case 'm_delete_ok':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_MESSAGE_DELETE_SUCCESS',
                    'status' => 'success'
                );
                break;
            case 'm_delete_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_MESSAGE_DELETE_ERROR',
                    'status' => 'error'
                );
                break;
            case 'b_create_ok':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_ESTABLISHMENT_CREATE_SUCCESS',
                    'status' => 'success'
                );
                break;
            case 'b_create_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_ESTABLISHMENT_CREATE_ERROR',
                    'status' => 'error'
                );
                break;
            case 'b_update_ok':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_ESTABLISHMENT_UPDATE_SUCCESS',
                    'status' => 'success'
                );
                break;
            case 'b_update_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_ESTABLISHMENT_UPDATE_ERROR',
                    'status' => 'error'
                );
                break;
            case 'b_enable_ok':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_ESTABLISHMENT_ENABLE_SUCCESS',
                    'status' => 'success'
                );
                break;
            case 'b_enable_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_ESTABLISHMENT_ENABLE_ERROR',
                    'status' => 'error'
                );
                break;
            case 'b_disable_ok':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_ESTABLISHMENT_DISABLE_SUCCESS',
                    'status' => 'success'
                );
                break;
            case 'b_disable_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_ESTABLISHMENT_DISABLE_ERROR',
                    'status' => 'error'
                );
                break;
            case 'b_delete_ok':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_ESTABLISHMENT_DELETE_SUCCESS',
                    'status' => 'success'
                );
                break;
            case 'b_delete_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_ESTABLISHMENT_DELETE_ERROR',
                    'status' => 'error'
                );
                break;
            case 'e_create_ok':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_EVENT_CREATE_SUCCESS',
                    'status' => 'success'
                );
                break;
            case 'e_create_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_EVENT_CREATE_ERROR',
                    'status' => 'error'
                );
                break;
            case 'e_update_ok':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_EVENT_UPDATE_SUCCESS',
                    'status' => 'success'
                );
                break;
            case 'e_update_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_EVENT_UPDATE_ERROR',
                    'status' => 'error'
                );
                break;
            case 'e_enable_ok':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_EVENT_ENABLE_SUCCESS',
                    'status' => 'success'
                );
                break;
            case 'e_enable_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_EVENT_ENABLE_ERROR',
                    'status' => 'error'
                );
                break;
            case 'e_disable_ok':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_EVENT_DISABLE_SUCCESS',
                    'status' => 'success'
                );
                break;
            case 'e_disable_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_EVENT_DISABLE_ERROR',
                    'status' => 'error'
                );
                break;
            case 'e_delete_ok':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_EVENT_DELETE_SUCCESS',
                    'status' => 'success'
                );
                break;
            case 'e_delete_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_EVENT_DELETE_ERROR',
                    'status' => 'error'
                );
                break;
            case 's_upgrade_ok':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_SERVICE_LEVEL_UPGRADE_'
                                   . 'SUCCESS',
                    'status' => 'success'
                );
                break;
            case 's_upgrade_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_SERVICE_LEVEL_UPGRADE_ERROR',
                    'status' => 'error'
                );
                break;
            case 'x_unknown_err':
                $cleanNotification = array(
                    'content' => 'NOTIFICATION_UNKNOWN_ERROR',
                    'status' => 'error'
                );
                break;
            default:
                break;
        }

        return $cleanNotification;

    }

    public static function renderCitySelectForm( &$pageData, &$lang ) {

        $html = '<div class="ui-button-group">'
              . '<form id="city-select-form" method="GET" action="'
              . DASHBOARD_BASE_PATH
              . '">'
              . '<table><tbody>'
              . '<tr><td>'
              . '<select id="city-select-control" name="c" '
              . 'placeholder="'
              . $lang->get( 'DASHBOARD_CHANGE_CITY_PLACEHOLDER' )
              . '">';

        $cities = explode( ',', ESTABLISHMENT_AVAILABLE_CITIES );

        foreach ( $cities as $city ) {

            $html .= '<option value="' . $city . '"';

            if ( $pageData['city'] === $city ) {
                $html .= ' selected="selected"';
            }

            $html .= '>' . $city . '</option>';

        }

        $html .= '</select>'
               . '</td><td>'
               . '<input class="ui-button" '
                    . 'type="submit" value="'
                    . $lang->get( 'DASHBOARD_CHANGE_CITY_LABEL' ) . '" />'
               . '</td></tr>'
               . '</tbody></table>'
               . '</form>'
               . '</div>';

        return $html;

    }

    public static function alphabetizeEstablishments( $establishments ) {

        function lexicalComparison ( $a, $b ) {
            return strtolower( $a['establishment']['name'] )
                   > strtolower( $b['establishment']['name'] );
        }

        if ( is_array( $establishments ) ) {
            usort( $establishments, 'lexicalComparison' );
        } else {
            $establishments = array();
        }

        return $establishments;

    }

    public static function renderEstablishmentSelectForm( &$pageData, &$lang ) {

        $html = '';

        if ( isset( $pageData['establishments'] )
             && is_array( $pageData['establishments'] )
             && count( $pageData['establishments'] ) > 1 ) {

            if ( isset( $pageData['currentEstablishment'] )
                 && isset( $pageData['currentEstablishment']['id'] ) ) {
                $currentEstablishmentId = $pageData['currentEstablishment']['id'];
            } else {
                $currentEstablishmentId = null;
            }

            $html = '<div class="ui-button-group">'
                  . '<form id="establishment-select-form" method="GET" action="'
                  . DASHBOARD_BASE_PATH
                  . '">'
                  . '<table><tbody>'
                  . '<tr><td>'
                  . '<select id="establishment-select-control" name="id" '
                  . 'placeholder="'
                  . $lang->get( 'DASHBOARD_CHANGE_ESTABLISHMENT_PLACEHOLDER' )
                  . '">';

            foreach ( $pageData['establishments'] as $establishment ) {

                $objectId = $establishment['id'];
                $name = $establishment['establishment']['name'];
                $status = $establishment['status'];

                $html .= '<option value="' .  $objectId . '"';
                if ( $currentEstablishmentId !== null
                     && $objectId === $currentEstablishmentId ) {
                    $html .= ' selected="selected"';
                }
                $html .= '>' . $name;
                if ( $status === 'DISABLED' ) {
                    $html .= ' ' . $lang->get( 'ESTABLISHMENT_DISABLED_LABEL' );
                }
                $html .= '</option>';
            }

            $html .= '</select>'
                   . '</td><td>'
                   . '<input class="ui-button" '
                        . 'type="submit" value="'
                        . $lang->get( 'DASHBOARD_CHANGE_ESTABLISHMENT_LABEL' ) . '" />'
                   . '</td></tr>'
                   . '</tbody></table>'
                   . '</form>'
                   . '</div>';

        }

        return $html;

    }

    public static function renderNotifications( &$pageData, &$lang ) {

        $notification = Dashboard::validateNotification( 
            Common::extractGETValue( 'n' )
        );

        if ( $notification !== null ) {

            echo '<div class="section-8-col"><h2 class="notification-'
                 . $notification['status']
                 . '">'
                 . $lang->get( $notification['content'] )
                 . '</h2></div>';

        }

        if ( isset( $pageData['currentEstablishment']['status'] )
             && $pageData['currentEstablishment']['status'] === 'DISABLED' ) {

            echo '<div class="section-8-col"><h2 class="notification-warning">'
                 . $lang->get( 'ESTABLISHMENT_DISABLED_DESCRIPTION' )
                 . '</h2></div>';

        }

        return true;

    }

    public static function renderNavSection( &$pageData, &$lang ) {

        $html = '<div class="section-8-col">'
              . '<h2 class="section-title">'
              . $lang->get( 'NAV_SECTION_TITLE' ) . '</h2>'
              . '<div class="section-content">'
              . '<div class="section-content-8-col">';
        if ( $pageData['account']['admin'] === true ) {
            $html .=  Dashboard::renderCitySelectForm( $pageData, $lang );
        }
        $html .= Dashboard::renderEstablishmentSelectForm( $pageData, $lang )
              . '<div class="ui-button-group">'
              . '<a id="dashboard-button" class="ui-button" href="'
              . DASHBOARD_BASE_PATH;
        if ( isset( $pageData['currentEstablishment'] )
             && isset( $pageData['currentEstablishment']['id'] ) ) {
            $html .= $pageData['currentEstablishment']['id'] . '/';
        }
        $html .= '" title="'
               . $lang->get( 'NAV_DASHBOARD_BUTTON_TITLE' )
               . '">'
               . $lang->get( 'NAV_DASHBOARD_BUTTON_LABEL' )
               . '</a>'
               . '<div class="clear"></div>'
               . '</div>'
               . '<div class="clear"></div>';

        if ( $pageData['account']['admin'] === true ) {

            $html .= '<div class="ui-button-group">'
                   . '<a class="ui-button" href="' . DASHBOARD_BASE_PATH
                   . 'create/voucher/" title="'
                   . $lang->get( 'NAV_CREATE_VOUCHER_LABEL' ) . '">'
                   . $lang->get( 'NAV_CREATE_VOUCHER_LABEL' ) . '</a>'
                   . '<a class="ui-button" href="' . DASHBOARD_BASE_PATH
                   . 'manage/vouchers/" title="'
                   . $lang->get( 'NAV_MANAGE_VOUCHERS_LABEL' ) . '">'
                   . $lang->get( 'NAV_MANAGE_VOUCHERS_LABEL' ) . '</a>'
                   . '<div class="clear"></div>'
                   . '</div>';

            $html .= '<div class="ui-button-group">'
                   . '<a class="ui-button" href="' . DASHBOARD_BASE_PATH
                   . 'create/account/" title="'
                   . $lang->get( 'NAV_CREATE_ACCOUNT_LABEL' ) . '">'
                   . $lang->get( 'NAV_CREATE_ACCOUNT_LABEL' ) . '</a>'
                   . '<a class="ui-button" href="' . DASHBOARD_BASE_PATH
                   . 'manage/accountss/" title="'
                   . $lang->get( 'NAV_MANAGE_ACCOUNTS_LABEL' ) . '">'
                   . $lang->get( 'NAV_MANAGE_ACCOUNTS_LABEL' ) . '</a>'
                   . '<div class="clear"></div>'
                   . '</div>';

            $html .= '<div class="ui-button-group">'
                   . '<a class="ui-button" href="' . DASHBOARD_BASE_PATH
                   . 'create/establishment/" title="'
                   . $lang->get( 'NAV_CREATE_ESTABLISHMENT_LABEL' ) . '">'
                   . $lang->get( 'NAV_CREATE_ESTABLISHMENT_LABEL' ) . '</a>';

            if ( isset( $pageData['currentEstablishment']['id'] )
                 && isset( $pageData['currentEstablishment']['status'] ) ) {

                $id = $pageData['currentEstablishment']['id'];
                $status = $pageData['currentEstablishment']['status'];

                if ( $status === 'ACTIVE' ) {
                    $html .= '<form class="ui-button-group-form" method="POST" action="'
                           . API_PATH . 'obj/' . $id . '/disable/">'
                           . '<button class="ui-button" type="submit">'
                           . $lang->get( 'NAV_DISABLE_ESTABLISHMENT_LABEL' ) . '</button>'
                           . '</form>';
                } else if ( $status === 'DISABLED' ) {
                    $html .= '<form class="ui-button-group-form" method="POST" action="'
                           . API_PATH . 'obj/' . $id . '/enable/">'
                           . '<button class="ui-button" type="submit">'
                           . $lang->get( 'NAV_ENABLE_ESTABLISHMENT_LABEL' ) . '</button>'
                           . '</form>';
                }
                $html .= '<form class="ui-button-group-form" method="POST" action="'
                       . API_PATH . 'obj/' . $id . '/delete/">'
                       . '<button class="ui-button" type="submit">'
                       . $lang->get( 'NAV_DELETE_ESTABLISHMENT_LABEL' ) . '</button>'
                       . '</form>';

            }

            $html .= '<div class="clear"></div>'
                   . '</div>';

            $html .= '<div class="ui-button-group">'
                   . '<a class="ui-button disabled" href="' . DASHBOARD_BASE_PATH
                   . 'moderation/" title="'
                   . $lang->get( 'NAV_MODERATE_MESSAGES_LABEL' )
                   . '" onclick="javascript:return false;">'
                   . $lang->get( 'NAV_MODERATE_MESSAGES_LABEL' )
                   . '</a>'
                   . '<div class="clear"></div>'
                   . '</div>';
        }

        $html .= '<div class="ui-button-group">'
               . '<a class="ui-button" href="'
               . API_AUTH_SIGN_OUT_GATEWAY
               . '" title="'
               . $lang->get( 'NAV_SIGN_OUT_BUTTON_TITLE' )
               . '">'
               . $lang->get( 'NAV_SIGN_OUT_BUTTON_LABEL' )
               . '</a>'
               . '<div class="clear"></div>'
               . '</div>'
               . '</div>'
               . '<div class="clear"></div>'
               . '</div>'
               . '<div class="clear"></div>'
               . '</div>';

            echo $html;

        return true;

    }

    public static function renderEstablishmentSection( &$pageData, &$lang ) {

        if ( isset( $pageData['currentEstablishment'] ) ) {

            $id = $pageData['currentEstablishment']['id'];
            $establishment = $pageData['currentEstablishment']['establishment'];
            $status = $pageData['currentEstablishment']['status'];

            $html = '<div class="section-5-col';
            if ( $status === 'DISABLED' ) {
                $html .= ' disabled';
            }
            $html .= '">';
            
            $html .= '<h2 class="section-title">'
                   . $lang->get( 'ESTABLISHMENT_SECTION_TITLE' )
                   . '</h2>'
                   . '<div class="section-content">';

            $html .= '<div class="section-content-5-col">';

            //summary of venue info
            $html .= '<div id="establishment-info"><table><tbody>';

            if ( $establishment['name'] !== null ) {
                $html .= '<tr><td class="form-label-4-col">'
                       . $lang->get( 'ESTABLISHMENT_NAME_LABEL' ) . '</td>'
                       . '<td class="form-content-4-col"><h3 class="section-em">'
                       . $establishment['name']
                       . '</h3></td></tr>';
            }

            if ( $establishment['logo'] !== null
                 && $establishment['logo']['fullAspectRatio'] !== null ) {
                $html .= '<tr><td class="form-label-4-col">'
                       . $lang->get( 'ESTABLISHMENT_LOGO_LABEL' ) . '</td>'
                       . '<td class="form-content-4-col">'
                       . '<img id="dashboard-establishment-logo" src="'
                       . BASE_PATH . 'media/thumb/'
                       . $establishment['logo']['name']
                       . '" height="'
                       . floor( 150 / $establishment['logo']['fullAspectRatio'] )
                       . '" alt="Logo" />'
                       . '</td></tr>';
            } else {
                $html .= '<tr><td class="form-label-4-col">'
                       . $lang->get( 'ESTABLISHMENT_LOGO_LABEL' ) . '</td>'
                       . '<td class="form-content-4-col">'
                       . '<img id="dashboard-establishment-logo" src="' . BASE_PATH
                       . 'img/logo-placeholder.jpg" alt="'
                       . $lang->get( 'ESTABLISHMENT_LOGO_PLACEHOLDER' )
                       . '" />'
                       . '</td></tr>';
            }

            if ( $establishment['description'] !== null ) {
                $html .= '<tr><td class="form-label-4-col">'
                       . $lang->get( 'ESTABLISHMENT_DESCRIPTION_LABEL' ) . '</td>'
                       . '<td class="form-content-4-col">'
                       . $establishment['description']
                       . '</td></tr>';
            } else {
                $html .= '<tr><td class="form-label-4-col">'
                       . $lang->get( 'ESTABLISHMENT_DESCRIPTION_LABEL' ) . '</td>'
                       . '<td class="form-content-4-col"><em class="form-content-placeholder">'
                       . $lang->get( 'ESTABLISHMENT_DESCRIPTION_PLACEHOLDER' )
                       . '</em></td></tr>';
            }

            if ( $establishment['address'] !== null ) {
                $html .= '<tr><td class="form-label-4-col">'
                       . $lang->get( 'ESTABLISHMENT_ADDRESS_LABEL' ) . '</td>'
                       . '<td class="form-content-4-col">'
                       . $establishment['address']
                       . '</td></tr>';
            } else {
                $html .= '<tr><td class="form-label-4-col">'
                       . $lang->get( 'ESTABLISHMENT_ADDRESS_LABEL' ) . '</td>'
                       . '<td class="form-content-4-col"><em class="form-content-placeholder">'
                       . $lang->get( 'ESTABLISHMENT_ADDRESS_PLACEHOLDER' )
                       . '</em></td></tr>';
            }

            if ( $establishment['tel'] !== null ) {
                $html .= '<tr><td class="form-label-4-col">'
                       . $lang->get( 'ESTABLISHMENT_TEL_LABEL' ) . '</td>'
                       . '<td class="form-content-4-col">'
                       . $establishment['tel']
                       . '</td></tr>';
            } else {
                $html .= '<tr><td class="form-label-4-col">'
                       . $lang->get( 'ESTABLISHMENT_TEL_LABEL' ) . '</td>'
                       . '<td class="form-content-4-col"><em class="form-content-placeholder">'
                       . $lang->get( 'ESTABLISHMENT_TEL_PLACEHOLDER' )
                       . '</em></td></tr>';
            }

            if ( $establishment['email'] !== null ) {
                $html .= '<tr><td class="form-label-4-col">'
                       . $lang->get( 'ESTABLISHMENT_EMAIL_LABEL' ) . '</td>'
                       . '<td class="form-content-4-col">'
                       . $establishment['email']
                       . '</td></tr>';
            } else {
                $html .= '<tr><td class="form-label-4-col">'
                       . $lang->get( 'ESTABLISHMENT_EMAIL_LABEL' ) . '</td>'
                       . '<td class="form-content-4-col"><em class="form-content-placeholder">'
                       . $lang->get( 'ESTABLISHMENT_EMAIL_PLACEHOLDER' )
                       . '</em></td></tr>';
            }

            if ( $establishment['url'] !== null ) {
                $html .= '<tr><td class="form-label-4-col">'
                       . $lang->get( 'ESTABLISHMENT_URL_LABEL' ) . '</td>'
                       . '<td class="form-content-4-col">'
                       . $establishment['url']
                       . '</td></tr>';
            } else {
                $html .= '<tr><td class="form-label-4-col">'
                       . $lang->get( 'ESTABLISHMENT_URL_LABEL' ) . '</td>'
                       . '<td class="form-content-4-col"><em class="form-content-placeholder">'
                       . $lang->get( 'ESTABLISHMENT_URL_PLACEHOLDER' )
                       . '</em></td></tr>';
            }

            $html .= '<tr><td></td><td>';

            if ( $status === 'ACTIVE' ) {

                $html .= '<button id="update-establishment-button" '
                       . 'class="ui-button">'
                       . $lang->get( 'ESTABLISHMENT_UPDATE_LABEL' )
                       . '</button>';

            }

            $html .= '</td></tr></tbody></table></div></div>';

                //form to update venue info
                $html .= '<div id="establishment-form">'
                       . '</div>';

            $html .= '<div class="clear"></div>'
                   . '</div>'
                   . '</div>';

            echo $html;

        } else {

            $html = '<div class="section-8-col disabled">';

            $html .= '<h2 class="section-title">'
                   . $lang->get( 'ESTABLISHMENT_SECTION_TITLE' )
                   . '</h2>'
                   . '<div class="section-content">'
                   . $lang->get( 'ACCOUNT_NO_ESTABLISHMENT_NOTICE' )
                   . '</div></div>';

            echo $html;

        }

        return true;

    }

    public static function renderServiceSection( &$pageData, &$lang) {

        if ( isset( $pageData['currentEstablishment'] )
             && isset( $pageData['currentEstablishment']['serviceLevel'] ) ) {

            $id = $pageData['currentEstablishment']['id'];
            $serviceLevel =
                $pageData['currentEstablishment']['serviceLevel'];
            $serviceDaysRemaining = 999;
//                $pageData['currentEstablishment']['serviceStatus']['daysRemaining'];

            $serviceTitle = $lang->get(
                'SERVICE_LEVEL_' . $serviceLevel . '_TITLE' );
            $serviceDescription = $lang->get(
                'SERVICE_LEVEL_' . $serviceLevel . '_DESCRIPTION'
            );

            if ( $serviceLevel < SERVICE_NUMBER_OF_LEVELS ) {
                $upgradeTitle = $lang->get(
                    'SERVICE_LEVEL_' . ( $serviceLevel + 1 )
                    . '_TITLE'
                );
                $upgradeDescription = $lang->get(
                    'SERVICE_LEVEL_' . ( $serviceLevel + 1 )
                    . '_DESCRIPTION'
                );
            } else {
                $upgradeDescription = false;
            }

            $html = '<div class="section-3-col">'
                  . '<h2 class="section-title">'
                  . $lang->get( 'SERVICE_SECTION_TITLE' )
                  . '</h2>'
                  . '<div class="section-content">'
                  . '<div class="section-content-3-col">'
                  . '<h3 class="section-em">'
                  . $lang->get( 'SERVICE_CURRENT_LEVEL_LABEL' )
                  . $serviceTitle
                  . '</h3>';

            if ( $pageData['account']['admin'] === true ) {

                $html .= '<br /><br />';

                if ( $serviceLevel === 0 ) {

                    $html .= '<form method="POST" action="'
                           . API_PATH
                           . 'service/upgrade/" autocomplete="off">'
                           . '<input type="hidden" name="id" value="'
                           . $pageData['currentEstablishment']['id']
                           . '" />'
                           . '<button class="ui-button" type="submit">'
                           . $lang->get( 'SERVICE_LEVEL_UPGRADE_LABEL' )
                           . '</button>'
                           . '</form>';

                } else {

                    $html .= '<form method="POST" action="'
                           . API_PATH
                           . 'service/downgrade/" autocomplete="off">'
                           . '<input type="hidden" name="id" value="'
                           . $pageData['currentEstablishment']['id']
                           . '" />'
                           . '<button class="ui-button" type="submit">'
                           . $lang->get( 'SERVICE_LEVEL_DOWNGRADE_LABEL' )
                           . '</button>'
                           . '</form>';

                }

                $html .= '<div class="clear"></div>'
                       . '<br />'
                       . '<a class="ui-button" href="'
                       . DASHBOARD_BASE_PATH
                       . 'analytics/'
                       . $pageData['currentEstablishment']['id']
                       . '/">'
                       . $lang->get( 'DASHBOARD_ANALYTICS_LABEL' )
                       . '</a>';

            }

            $html .= '</div>'
                   . '<div class="clear"></div>'
                   . '</div>'
                   . '</div>';

            echo $html;

        }

        return true;

    }

    public static function renderEventsSection( &$pageData, &$lang ) {

        if ( isset( $pageData['currentEstablishment'] ) ) {

            $status = $pageData['currentEstablishment']['status'];
            $eventCount = count( $pageData['currentEstablishment']['events'] );
            $eventMax = $pageData['currentEstablishment']['maxEvents'];

            $html = '<div class="section-8-col">'
                   . '<h2 class="section-title">'
                   . $lang->get( 'EVENT_DASHBOARD_SECTION_TITLE' )
                   . '</h2>'
                   . '<div class="section-content">'
                   . '<div class="section-content-8-col">'

                   . '<h3 class="section-em">'
                   . $lang->get( 'SERVICE_LEVEL_EVENTS_AVAILABLE_PREFIX' )
                   . '<span class="inline-large-digit">'
                   . $eventCount
                   . '</span>'
                   . $lang->get( 'SERVICE_LEVEL_EVENTS_AVAILABLE_OF' )
                   . '<span class="inline-large-digit">'
                   . $eventMax
                   . '</span>'
                   . $lang->get( 'SERVICE_LEVEL_EVENTS_AVAILABLE_SUFFIX' )
                   . '</h3>';

            if ( $eventCount > $eventMax ) {
                $html .= '<h4 class="event-notification">'
                       . $lang->get( 'SERVICE_LEVEL_INSUFFICIENT_DESCRIPTION' )
                       . '</h4>';
            }

            $html .= '<h3 class="section-em">'
                   . $lang->get( 'EVENT_LOCAL_DATE_TIME_NOTICE' )
                   . '</h3>'
                   . '</div>';

            if ( count( $pageData['currentEstablishment']['events'] ) > 0 ) {

                foreach ( $pageData['currentEstablishment']['events'] as $eventObject ) {

                    $html .= '<div class="section-content-4-col dashboard-event">';

                    $html .= '<div id="obj-' . $eventObject['id'] . '" class="obj dashboard-event-obj layout';
                    if ( $eventObject['status'] === 'DISABLED' ) {
                        $html .= ' disabled';
                    }
                    $html .= '">'
                           . '</div>';

                    $html .= '<div class="dashboard-event-meta">';
                    if ( $status === 'DISABLED' ) {
                        $html .= '<h3 class="dashboard-event-meta-title">'
                              . $lang->get( 'EVENT_ESTABLISHMENT_DISABLED_TITLE' )
                              . '</h3>';
                    } else if ( $eventObject['status'] === 'DISABLED' ) {
                        $html .= '<h3 class="dashboard-event-meta-title">'
                              . $lang->get( 'EVENT_DISABLED_TITLE' )
                              . '</h3>'
                              . '<p class="dashboard-event-meta-content">'
                              . $lang->get( 'EVENT_DISABLED_UPSELL_DESCRIPTION' )
                              . '</p>';
                    } else {
                        $html .= '<h3 class="dashboard-event-meta-title">'
                               . $lang->get( 'EVENT_META_TITLE' )
                               . '</h3>'
                               . '<ul class="dashboard-event-meta-content">';

                        if ( $eventObject['event']['repeatsWeekly'] ) {
                            $html .= '<li>'
                                  . $lang->get(
                                      'EVENT_REPEATS_WEEKLY_TRUE_DESCRIPTION'
                                  )
                                  . '</li>'
                                  . '<li>';
                            $nextDateTime = Common::getNextOccurrence(
                                $eventObject['event']['startDateTime'],
                                true,
                                false //override default to return object not timestamp
                            );

                            $nextDateTime->setTimeZone(
                                new DateTimeZone(
                                    Common::getTimeZone( $eventObject['establishment']['city'] )
                                )
                            );

                            $html .= $lang->get( 'EVENT_NEXT_OCCURRENCE_LABEL' )
                                   . $nextDateTime->format( 'D M d Y H:i:s' )
                                   . '</li>';
                        } else {
                            $html .= '<li>'
                                  . $lang->get(
                                      'HAPPENING_REPEATS_WEEKLY_FALSE_DESCRIPTION'
                                  )
                                  . '</li>';
                        }
                        $html .= '</ul>';
                    }
                    $html .= '<div class="dashboard-event-meta-column">';
                    if ( $status === 'ACTIVE'
                         && $eventObject['status'] === 'ACTIVE' ) {
                        $html .= '<a class="ui-button" href="' . DASHBOARD_BASE_PATH
                              . 'update/event/' . $eventObject['id'] . '/" title="'
                              . $lang->get( 'EVENT_UPDATE_LABEL' )
                              . '">'
                              . $lang->get( 'EVENT_UPDATE_LABEL' )
                              . '</a>';
                    } else if ( $eventObject['status'] === 'DISABLED' ) {
                        $html .= '<a class="ui-button" href="' . DASHBOARD_BASE_PATH
                              . 'upgrade/" title="'
                              . $lang->get( 'SERVICE_UPGRADE_TITLE' )
                              . '" >'
                              . $lang->get( 'SERVICE_UPGRADE_TITLE' )
                              . '</a>';
                    }
                    $html .= '</div>'
                           . '<div class="dashboard-event-meta-column">'
                           . '<form id="event-' . $eventObject['id']
                           . '-delete" method="POST" '
                           . 'action="' . API_PATH . 'obj/'
                           . $eventObject['id'] . '/delete/">'
                           . '<input class="ui-button" type="submit" value="' . $lang->get( 'EVENT_DELETE_SUBMIT_LABEL' ) . '" />'
                           . '</form>'
                           . '</div>'
                           . '<div class="clear"></div>'
                           . '</div>'
                           . '<div class="clear"></div>'
                           . '</div>';

                }

            }

            //only show the 'create happening' button if the venue is permitted at
            //least one more happening
            if ( $eventCount < $eventMax ) {
                $html .= '<div class="section-content-4-col dashboard-event">'
                       . '<a id="dashboard-event-create-icon" href="'
                       . DASHBOARD_BASE_PATH . 'create/event/'
                       . $pageData['currentEstablishment']['id']
                       . '" title="'
                       . $lang->get( 'EVENT_CREATE_LABEL' )
                       . '">'
                       . '<img src="'
                       . BASE_PATH . 'img/create-event-icon.png" alt="'
                       . $lang->get( 'EVENT_CREATE_LABEL' )
                       . '" />'
                       . '</a>'
                       . '<div class="dashboard-event-meta">'
                       . '<a id="dashboard-event-create-button" class="ui-button" '
                       . 'href="' . DASHBOARD_BASE_PATH . 'create/event/'
                       . $pageData['currentEstablishment']['id']
                       . '" title="'
                       . $lang->get( 'EVENT_CREATE_LABEL' )
                       . '">'
                       . $lang->get( 'EVENT_CREATE_LABEL' )
                       . '</a>'
                       . '</div>'
                       . '<div class="clear"></div>'
                       . '</div>';
            }

            $html .= '<div class="section-content-8-col"></div>'
                   . '<div class="clear"></div>'
                   . '</div>'
                   . '</div>';

            echo $html;

        }

        return true;

    }

    public static function exportEventToJS( &$pageData ) {
		return;
    }

    public static function exportLocalizedEvent( &$pageData ) {
        return json_encode( $pageData['currentEvent'] );
    }

    public static function formatMessageText( $text ) {

        $formatted = '';

        if ( $text ) {

            $paragraphs = explode( "\n", $text );

            if ( mb_strlen( $paragraphs[0] ) > 0 ) {
                $formatted .= '<em class="headline">';
                $formatted .= Dashboard::expandMessageTags( $paragraphs[0] );
                $formatted .= '</em><br />';
            }

            if ( count( $paragraphs ) > 1 ) {
                $length = count( $paragraphs ) - 1;
                $i = 1;
                while ( $i < $length ) {
                    if ( mb_strlen( $paragraphs[$i] ) > 0 ) {
                        $formatted .= Dashboard::expandMessageTags(
                            $paragraphs[$i]
                        );
                        $formatted .= '<br />';
                    } else {
                        $formatted .= '<br />';
                    }
                    $i++;
                }
                $formatted .= Dashboard::expandMessageTags(
                    $paragraphs[$length]
                );
            }

        }

        return $formatted;

    }

    public static function expandMessageTags( $text ) {

        return $text;

    }

    public static function getServiceLevelMaxEvents( &$pageData ) {

        switch ( $pageData['serviceStatus']['level'] ) {

            case 1:
                $max = SERVICE_LEVEL_1_MAX_EVENTS;
                break;

            case 2:
                $max = SERVICE_LEVEL_2_MAX_EVENTS;
                break;

            default:
                $max = SERVICE_LEVEL_0_MAX_EVENTS;
                break;

        }

        return $max;

    }

    public static function getServiceLevelMaxEventsPerResultSet( &$pageData ) {

        switch ( $pageData['serviceStatus']['level'] ) {

            case 1:
                $max = SERVICE_LEVEL_1_MAX_EVENTS_PER_RESULT_SET;
                break;

            case 2:
                $max = SERVICE_LEVEL_2_MAX_EVENTS_PER_RESULT_SET;
                break;

            default:
                $max = SERVICE_LEVEL_0_MAX_EVENTS_PER_RESULT_SET;
                break;

        }

        return $max;

    }

}
