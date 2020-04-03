<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://alessandrodecristofaro.it
 * @since             1.0.0
 * @package           Forum_Insights
 *
 * @wordpress-plugin
 * Plugin Name:       FORUM Insigths
 * Plugin URI:        https://alessandrodecristofaro.it
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Alessandro De Cristofaro
 * Author URI:        https://alessandrodecristofaro.it
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       forum-insights
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC'))
{
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('FORUM_INSIGHTS_VERSION', '1.0.0');

//AJAX Action
add_action("wp_ajax_get_interest", "get_interest");
add_action('wp_ajax_nopriv_get_interest', 'get_interest');

add_action("wp_ajax_get_suggested_int", "get_suggested_interest");
add_action('wp_ajax_nopriv_get_suggested_int', "get_suggested_interest");

add_action('wp_ajax_save_custom_audiance', 'save_custom_audiance');
add_action('wp_ajax_nopriv_save_custom_audiance', 'save_custom_audiance');

add_shortcode('get_result', 'base_template');

function save_custom_audiance()
{
    $nonce = $_REQUEST['nonce'];

    if (!$nonce)
    {
        echo 'ERROR';
        exit;
    }

    $audience_json = $_REQUEST['data'];
    //add_termmeta
    //add post ( type = 'audience')
    var_dump($audience_json);

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    {
        $result = json_encode($audience_json);
        echo $result;
    }
    else
    {
        header("Location: " . $_SERVER["HTTP_REFERER"]);
    }
    die();

}

function base_template()
{
    echo '<div id="#result"></div>';
}

function get_suggested_interest()
{
    $url_prefix = 'https://graph.facebook.com/v5.0/';

    if (isset($_REQUEST['access_token']))
    {
        $access_token = $_REQUEST['access_token'];
    }

    if (isset($_REQUEST['interest_selected']))
    {
        $interest_given = $_REQUEST['interest_selected'];
    }

    //var_dump($interest_given);
    foreach ($interest_given as $entity)
    {
        if (!next($interest_given))
        {
            $final_interest = $final_interest . '"' . $entity['name'] . '"';
        }
        else
        {
            $final_interest = $final_interest . '"' . $entity['name'] . '",';
        }
    }
    /*$final_interest = str_replace( " ", '%20', $final_interest);
    $final_interest = str_replace( '"', '%22', $final_interest);
    $final_interest = str_replace( ',', '%2C', $final_interest);*/

    //echo filter_var($final_interest, FILTER_SANITIZE_URL);
    $api_string = $url_prefix . 'search?type=adinterestsuggestion&interest_list=[' . $final_interest . ']&limit=50&locale=it_IT&access_token=' . $access_token;
    $response = wp_remote_get($api_string);
    //echo $api_string;
    if (is_array($response) && !is_wp_error($response))
    {
        $body = json_decode($response['body'], true);
        $data = $body['data'];
        //var_dump($body);
        //var_dump($body);
        include ('public/partials/result-layout.php');
        $render_data = '';
        //var_dump($data);
        foreach ($data as $interest)
        {

            if (!isset($interest['topic']))
            {
                $interest['topic'] = '';
            }

            $render_data = $render_data . render_interest_single($interest['name'], $interest['topic'], $interest['audience_size'], $interest['id']);
            /* $interest['id'] .
            $interest['name']
            $interest['audience_size']
            $interest['topic'] . '</br>';
            */
        }
        //$render_data = 'aaa';
        $result['render_data'] = $render_data;
        $result['type'] = 'success';

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            $result = json_encode($result);
            echo $result;
        }
        else
        {
            header("Location: " . $_SERVER["HTTP_REFERER"]);
        }
        die();
    }
}

function get_interest($url_prefix)
{

    $url_prefix = 'https://graph.facebook.com/v5.0/';

    if (isset($_REQUEST['access_token']))
    {
        $access_token = $_REQUEST['access_token'];
    }
    //$access_token = 'EAAjx6RT3ZAg0BALZAvkbUc9oDfewp08EPuYLDKLc0qsUmay8RaVsSt86W8fLdaVb6c14RbUklwdCiBCKmGnIEQtVAdLY5NXQ3OK3Dim5ac3YHQc9c79CeafNIK4mw2k6qqQx50riHbNOUzXJywZC0hKveVD5LWxDQ4CtE3H2RYbBG4kkS6ZCSW4Ghn4vSsA6M96HwCDi3QZDZD';
    if (!$_REQUEST['search_term'])
    {
        $search_term = 'fitness';
    }
    else
    {
        $search_term = $_REQUEST['search_term'];
    }

    $response = wp_remote_get($url_prefix . 'search?type=adinterest&q=%5B' . $search_term . '%5D&limit=50&locale=it_IT&access_token=' . $access_token);

    if (is_array($response) && !is_wp_error($response))
    {
        $body = json_decode($response['body'], true);
        $data = $body['data'];
        //var_dump($body);
        //var_dump($body);
        include ('public/partials/result-layout.php');
        $render_data = '';

        foreach ($data as $interest)
        {

            if (!isset($interest['topic']))
            {
                $interest['topic'] = '';
            }

            $render_data = $render_data . render_interest_single($interest['name'], $interest['topic'], $interest['audience_size'], $interest['id']);
            /* $interest['id'] .
              $interest['name']
             $interest['audience_size']
             $interest['topic'] . '</br>';
            */
        }
        //$render_data = 'aaa';
        $result['render_data'] = $render_data;
        $result['type'] = 'success';

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            $result = json_encode($result);
            echo $result;
        }
        else
        {
            header("Location: " . $_SERVER["HTTP_REFERER"]);
        }
        die();
    }
}

