<?php

// Maybe go away from using Settings API ?
// https://wpshout.com/wordpress-options-page/


include_once 'helpers.php';
include_once 'Paths.php';
include_once 'Config.php';

require_once "messenger.inc";

use \WebPExpress\Paths;
use \WebPExpress\Config;

/*
These lines should enable us to know whether quality can be detected.
But needs testing...
require WEBPEXPRESS_PLUGIN_DIR . '/vendor/require-webp-convert.php';
$detectedQualityOfTestJpg = \WebPConvert\Converters\ConverterHelper::detectQualityOfJpg(WEBPEXPRESS_PLUGIN_DIR . '/test/focus.jpg');
$canDetectQualityOfJpegs = ($detectedQualityOfTestJpg == 100);
*/

//webpexpress_settings_submit

add_action('admin_post_webpexpress_settings_submit', function() {
    // https://premium.wpmudev.org/blog/handling-form-submissions/
    // checkout https://codex.wordpress.org/Function_Reference/sanitize_meta

    $config = [
        'fail' => sanitize_text_field($_POST['fail']),
        'max-quality' => sanitize_text_field($_POST['max-quality']),
        'image-types' => sanitize_text_field($_POST['image-types']),
        'converters' => json_decode(wp_unslash($_POST['converters']), true) // holy moly! - https://stackoverflow.com/questions/2496455/why-are-post-variables-getting-escaped-in-php
    ];

    //wp_redirect( $_SERVER['HTTP_REFERER'] );
    //echo gettype($_POST['converters']);
    //print_r($_POST);
    // [{"converter":"cwebp","options":{"use-nice":false},"id":"cwebp"},{"converter":"wpc","id":"wpc"},{"converter":"gd","options":{"skip-pngs":true},"id":"gd"},{"converter":"ewww","id":"ewww"},{"converter":"imagick","id":"imagick"}]
//echo get_magic_quotes_gpc() ? 'yes' : 'no';

    //echo wp_unslash($_POST['converters']);
    //echo '<br><br>';
    //print_r(json_decode($_POST['converters'], true));
    //print_r($config);
    //exit;
    if (Config::saveConfiguration($config)) {
        webpexpress_add_message('success', 'Configuration saved');
    } else {
        webpexpress_add_message('error', 'Failed saving configuration file, or htaccess or something...');
    }
    wp_redirect( $_SERVER['HTTP_REFERER']);

/*
    // Save configuration file
    if (Config::saveConfigurationFile($config)) {
        printf(
          '<div class="%1$s"><p>%2$s</p></div>',
          esc_attr( 'notice notice-success is-dismissible' ),
          esc_html( __( 'WebP Express saved the configuration successfully', 'webp-express' ) )
        );
    } else {
        printf(
          '<div class="%1$s"><p>%2$s</p></div>',
          esc_attr( 'notice notice-error is-dismissible' ),
          //esc_html( __( 'WebP Express could not create a subfolder in your upload folder. Check your file permissions', 'webp-express' ) )
          'WebP Express needs to write the configuration to a file "webp-express/config/config.json" under your wp-content folder, but does not have permission to do so.</b><br>' .
          'Please change the permissions on the "wpcontent/webp-express/config" folder to allow the file to be created.'
        );
    }

    // update .htaccess
    $rules = WebPExpressHelpers::generateHTAccessRules();
    if (WebPExpressHelpers::insertHTAccessRules($rules)) {
        printf(
          '<div class="%1$s"><p>%2$s</p></div>',
          esc_attr( 'notice notice-success is-dismissible' ),
          esc_html( __( 'WebP Express updated the .htaccess successfully', 'webp-express' ) )
        );
    } else {
        printf(
          '<div class="%1$s"><p>%2$s</p></div>',
          esc_attr( 'notice notice-error is-dismissible' ),
          '<b>.htaccess is not writable</b>. To fix this, make .htaccess writable and try activating the plugin again. <a target="_blank" href="https://github.com/rosell-dk/webp-express/wiki/Error-messages-and-warnings#htaccess-is-not-writable">Click here</a> for more information.'
        );
    }
*/

    exit();
});

add_action('admin_enqueue_scripts', function () {
    // https://github.com/RubaXa/Sortable

    wp_register_script('sortable', plugins_url('../js/sortable.min.js', __FILE__), [], '1.9.0');
    wp_enqueue_script('sortable');

    wp_register_script(
        'webp-express-options-page',
        plugins_url('../js/webp-express-options-page.js', __FILE__),
        ['sortable'],
        '0.2.0'
    );
    wp_enqueue_script('webp-express-options-page');

    wp_add_inline_script('webp-express-options-page', 'window.webpExpressPaths = ' . json_encode(Paths::getUrlsAndPathsForTheJavascript()) . ';');

    //wp_add_inline_script('webp-express-options-page', 'window.webpExpressPaths = ' . json_encode(WebPExpressHelpers::calculateUrlsAndPaths()) . ';');
    //wp_add_inline_script('webp-express-options-page', 'window.converters = [{"converter":"imagick","id":"imagick"},{"converter":"cwebp","id":"cwebp"},{"converter":"gd","id":"gd"}];');

    wp_register_style(
        'webp-express-options-page-css',
        plugins_url('../css/webp-express-options-page.css', __FILE__),
        null,
        '0.2.0'
    );
    wp_enqueue_style('webp-express-options-page-css');

    add_thickbox();
});




/* Settings Page Content */
function webp_express_settings_page_content()
{
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions to access this page.');
    }
    ?>
    <div class="wrap">
        <h2>WebP Express Settings</h2>

<?php

    require "options-messages.inc";

    //echo home_url() . '<br>';
    //echo Paths::getHomeUrlPathRelDomain() . '<br>';
    //echo Paths::getWodUrlPath() . '<br>';
    echo '<pre>' . print_r(Paths::getUrlsAndPathsForTheJavascript(), true) . '</pre>';

    //echo trailingslashit(parse_url(home_url())['path']) . '<br>';
    //print_r(parse_url(home_url()));

        //echo 'WP_CONTENT_DIR:' . WP_CONTENT_DIR;
        //Config::saveConfigurationFile();
        //$rules = WebPExpressHelpers::generateHTAccessRules();
        //echo '<pre>' . print_r($rules, true) . '</pre>';
        //WebPExpressHelpers::insertHTAccessRules($rules);
        //echo getCacheDirRel

        //echo '<pre>' . print_r(Config::loadConfig(), true) . '</pre>';


        $defaultConfig = [
            'image-types' => 1,
            'fail' => 'original',
            'max-quality' => 80,
            'converters' => []
        ];

        $config = Config::loadConfig();
        if (!$config) {
            $config = [];
        }

        $config = array_merge($defaultConfig, $config);

        // Generate a custom nonce value.
        $webpexpress_settings_nonce = wp_create_nonce('webpexpress_settings_nonce');

        echo '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" method="post" id="webpexpress_settings" >';
?>
        <input type="hidden" name="action" value="webpexpress_settings_submit">
        <input type="hidden" name="webpexpress_settings_nonce" value="<?php echo $webpexpress_settings_nonce ?>" />

<?php

        echo '<table class="form-table"><tbody>';

        // Image types
        // ------------
        echo '<tr><th scope="row">Image types to convert</th><td>';

        // bitmask
        // 1: JPEGs
        // 2: PNG's
        // Converting only jpegs is thus "1"
        // Converting both jpegs and pngs is (1+2) = 3
        //$imageTypes = get_option('webp_express_image_types_to_convert');
        $imageTypes = $config['image-types'];

        echo '<select name="image-types">';
        echo '<option value="0"' . ($imageTypes == 0 ? ' selected' : '') . '>Do not convert any images!</option>';
        echo '<option value="1"' . ($imageTypes == 1 ? ' selected' : '') . '>Only convert jpegs</option>';
        echo '<option value="3"' . ($imageTypes == 3 ? ' selected' : '') . '>Convert both jpegs and pngs</option>';
        echo '</select>';

        echo '</td></tr>';

        // Response on failure
        // --------------------
        echo '<tr><th scope="row">Response on failure</th><td>';

        //$fail = get_option('webp_express_failure_response');
        $fail = $config['fail'];
        echo '<select name="fail">';
        echo '<option value="original"' . ($fail == 'original' ? ' selected' : '') . '>Original image</option>';
        echo '<option value="404"' . ($fail == '404' ? ' selected' : '') . '>404</option>';
        echo '<option value="report"' . ($fail == 'report' ? ' selected' : '') . '>Error report (in plain text)</option>';
        echo '<option value="report-as-image"' . ($fail == 'report-as-image' ? ' selected' : '') . '>Error report as image</option>';
        echo '</select>';
        echo '</td></tr>';
//        echo '<tr><td colspan=2>Determines what the converter should serve, in case the image conversion should fail. For production servers, recommended value is "Original image". For development servers, choose anything you like, but that</td></tr>';

        // Max quality
        // --------------------
        //$maxQuality = get_option('webp_express_max_quality');
        $maxQuality = $config['max-quality'];

        echo '<tr><th scope="row">Max quality (0-100)</th><td>';
        echo '<input type="text" name="max-quality" value="' . $maxQuality . '">';
        echo '</td></tr>';
//        echo '<tr><td colspan=2><p>Converted jpeg images will get same quality as original, but not more than this setting. Something between 70-85 is recommended for most websites.</p></td></tr>';

        // method
        //echo '<p>When higher values are used, the encoder will spend more time inspecting additional encoding possibilities and decide on the quality gain. Supported by cwebp, wpc and imagick</p>';

        echo '<tr></tr>';
        echo '</tbody></table>';

        // Converters
        // --------------------
        //$converters = get_option('webp_express_converters');
        $converters = $config['converters'];
        echo '<script>window.converters = ' . json_encode($converters) . '</script>';
        echo "<input type='text' name='converters' value='' style='visibility:hidden' />";

        // https://premium.wpmudev.org/blog/handling-form-submissions/


?>
        <!--<form action="options.php" method="post">-->



            <?php
            //settings_fields('webp_express_option_group');
            //do_settings_sections('webp_express_settings_page');

//print_r(get_option('plugin_error'));

            //echo '<pre>' . print_r(WebPExpressHelpers::calculateUrlsAndPaths(), true) . '</pre>';
            $localConverters = ['cwebp', 'imagick', 'gd'];

/*
            $testResult = WebPExpressHelpers::testConverters($localConverters);
            //print_r($testResult);

            if ($testResult['numOperationalConverters'] == 0) {
                echo 'Unfortunately, your server is currently not able to convert to webp files by itself. You will need to set up a cloud converter.<br><br>';
                foreach ($testResult['results'] as $result) {
                    echo $result['converter'] . ':' . $result['message'] . '<br>';
                }
            } else {
                //echo 'Your server is able to convert webp files by itself.';
            }
            if ($testResult['numOperationalConverters'] == 1) {
                //
            }
*/
            $extraConverters = [
                [
                    'converter' => 'ewww',
                    'options' => array(
                        'key' => 'your api key here',
                    ),
                ]
            ];

            $converters = [
                [
                    'converter' => 'cwebp',
                ],
                [
                    'converter' => 'imagick',
                ],
                [
                    'converter' => 'gd',
                ],
                [
                    'converter' => 'ewww',
                    'options' => [
                        'key' => 'your api key here',
                    ],
                ]
            ];

/*
http://php.net/manual/en/function.set-include-path.php


//exec('/usr/sbin/getsebool -a', $output6, $returnCode5); // ok
//echo 'All se bools: ' . print_r($output6, true) . '. Return code:' . $returnCode5;
*/

            echo '<h2>Converters</h2>';
            $dragIcon = '<svg version="1.0" xmlns="http://www.w3.org/2000/svg" width="17px" height="17px" viewBox="0 0 100.000000 100.000000" preserveAspectRatio="xMidYMid meet"><g transform="translate(0.000000,100.000000) scale(0.100000,-0.100000)" fill="#444444" stroke="none"><path d="M415 920 l-80 -80 165 0 165 0 -80 80 c-44 44 -82 80 -85 80 -3 0 -41 -36 -85 -80z"/><path d="M0 695 l0 -45 500 0 500 0 0 45 0 45 -500 0 -500 0 0 -45z"/><path d="M0 500 l0 -40 500 0 500 0 0 40 0 40 -500 0 -500 0 0 -40z"/><path d="M0 305 l0 -45 500 0 500 0 0 45 0 45 -500 0 -500 0 0 -45z"/><path d="M418 78 l82 -83 82 83 83 82 -165 0 -165 0 83 -82z"/></g></svg>';

            echo '<p><i>Drag to reorder. The converter on top will be used. Should it fail, the next will be used, etc</i></p>';
            // https://github.com/RubaXa/Sortable

            // Empty list of converters. The list will be populated by the javascript
            echo '<ul id="converters"></ul>';
            ?>
            <div id="cwebp" style="display:none;">
                <div class="cwebp converter-options">
                  <h3>cwebp</h3>
                  <div class="info">
                      cwebp works by executing the cwebp binary from Google. This should normally be your first choice.
                      Its best in terms of quality, speed and options.
                      The only catch is that it requires the exec function to be enabled, and that the webserver user is
                      allowed to execute the cwebp binary (either at known system locations, or one of the precompiled binaries,
                      that comes with this library).
                      If you are on a shared host that doesn't allow that, the second best choice would probably be the wpc cloud converter.
                  </div>
                  <h3>cweb options</h3>
                  <div>
                      <label for="cwebp_use_nice">Use nice</label>
                      <input type="checkbox" id="cwebp_use_nice">
                      <br>Enabling "use nice" saves system resources at the cost of slightly slower conversion

                  </div>
                  <br>
                  <button onclick="updateConverterOptions()" class="button button-primary" type="button">Update</button>
                  <!-- <a href="javascript: tb_remove();">close</a> -->
                </div>
            </div>
            <div id="gd" style="display:none;">
                <div class="ewww converter-options">
                  <h3>Gd</h3>
                  <p>
                    The gd converter uses the Gd extension to do the conversion. It is per default placed below the cloud converters for two reasons.
                    Firstly, it does not seem to produce quite as good quality as cwebp.
                    Secondly, it provides no conversion options, besides quality.
                    The Gd extension is pretty common, so the main feature of this converter is that it <i>may</i> work out of the box.
                    This is in contrast to the cloud converters, which requires that the user does some setup.
                  </p>
                  <h3>Gd options</h3>
                  <div class="info">
                      Gd neither supports copying metadata nor exposes any WebP options. Lacking the option to set lossless encoding results in poor encoding of PNGs - the filesize is generally much larger than the original. For this reason, the converter defaults to skip PNG's.
                  </div>
                  <div>
                      <label for="gd_skip_pngs">Skip PNGs</label>
                      <input type="checkbox" id="gd_skip_pngs">
                  </div>
                  <br>
                  <button onclick="updateConverterOptions()" class="button button-primary" type="button">Update</button>
                  <!-- <a href="javascript: tb_remove();">close</a> -->
                </div>
            </div>
            <div id="imagick" style="display:none;">
                <div class="imagick converter-options">
                  <h3>Imagick</h3>
                  <p>
                    imagick would be your last choice. For some reason it produces conversions that are only marginally better than the originals.
                    See <a href="https://github.com/rosell-dk/webp-convert/issues/43" target="_blank">this issue</a>. But it is fast.
                  </p>
                  <h3>Imagick options</h3>
                  <div class="info">
                      imagick has no extra options.
                  </div>
                  <br>
                  <!--
                  <button onclick="updateConverterOptions()" class="button button-primary" type="button">Update</button>
              -->
                  <!-- <a href="javascript: tb_remove();">close</a> -->
                </div>
            </div>
            <div id="ewww" style="display:none;">
                <div class="ewww converter-options">
                  <h3>Ewww</h3>
                  <p>
                    <a href="https://ewww.io/" target="_blank">ewww</a> is a cloud service.
                    It is a decent alternative for those who don't have the technical know-how to install wpc.
                    ewww is using cwebp to do the conversion, so quality is great.
                    ewww however only provides one conversion option (quality), and it does not support "auto"
                    quality (yet - I have requested the feature and the maintainer are considering it).
                    Also, it is not free. But very cheap. Like in almost free.
                  </p>
                  <h3>Ewww options</h3>
                  <div>
                      <label for="ewww_key">Key</label>
                      <input type="text" id="ewww_key" placeholder="Your API key here">
                  </div>
                  <br>
                  <h4>Fallback (optional)</h4>
                  <div>
                      <label for="ewww_key_2">key</label>
                      <input type="text" id="ewww_key_2" placeholder="In case the first one expires...">
                  </div>
                  <br>
                  <button onclick="updateConverterOptions()" class="button button-primary" type="button">Update</button>
                  <!-- <a href="javascript: tb_remove();">close</a> -->
                </div>
            </div>
            <div id="wpc" style="display:none;">
                <div class="wpc converter-options">
                  <h3>WebPConvert Cloud Service</h3>
                  wpc is an open source cloud converter based on <a href="https://github.com/rosell-dk/webp-convert" target="_blank">WebPConvert</a>
                  (this plugin also happens to be based on WebPConvert).
                  Conversions will of course be slower than cwebp, as images need to go back and forth to the cloud converter.
                  As images usually just needs to be converted once, the slower conversion
                  speed is probably acceptable. The conversion quality and options of wpc matches cwebp.
                  The only catch is that you will need to install the WPC library on a server (or have someone do it for you).
                  <a href="https://github.com/rosell-dk/webp-convert-cloud-service" target="blank">Visit WPC on github</a>.
                  If this is a problem, we suggest you turn to ewww.
                  (PS: A Wordpress plugin is planned, making it easier to set up a WPC instance. Or perhaps the functionality will even be part of this plugin)

                  <h3>Options for WebPConvert Cloud Service</h3>
                  <div>
                      <label for="wpc_url">URL</label>
                      <input type="text" id="wpc_url" placeholder="Url to your WPC instance">
                  </div>

                  <div>
                      <label for="wpc_secret">Secret</label>
                      <input type="text" id="wpc_secret" placeholder="Secret (must match secret on server side)">
                  </div>
                  <br>
                  <h4>Fallback (optional)</h4>
                  <p>In case the first is down, the fallback will be used.</p>
                  <div>
                      <label for="wpc_url_2">URL</label>
                      <input type="text" id="wpc_url_2" placeholder="Url to your other WPC instance">
                  </div>
                  <div>
                      <label for="wpc_secret_2">Secret</label>
                      <input type="text" id="wpc_secret_2" placeholder="Secret (must match secret on server side)">
                  </div>
                  <br>
                  <button onclick="updateConverterOptions()" class="button button-primary" type="button">Update</button>
                </div>
            </div>
            <?php
            //print_r($urls);
            //echo $urls['urls']['webpExpressRoot'];
            if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false )) {
                echo 'Your browser supports webp... So you can test if everything works (including the redirect magic) - using these links:<br>';
                //$webpExpressRoot = WebPExpressHelpers::calculateUrlsAndPaths()['urls']['webpExpressRoot'];
                //$webpExpressRoot = Paths::calculateUrlsAndPaths()['urls']['webpExpressRoot'];
                $webpExpressRoot = Paths::getPluginUrlPath();
                echo '<a href="/' . $webpExpressRoot . '/test/test.jpg" target="_blank">Convert test image</a><br>';
                echo '<a href="/' . $webpExpressRoot . '/test/test.jpg?debug" target="_blank">Convert test image (show debug)</a><br>';
            }
             ?>

            <!--
            <div id="add-cloud-converter-id" style="display:none;">
                <p>
                  Select cloud converter to add:

                  <button onclick="addConverter('ewww')" class="button button-primary" type="button">Add ewww converter</button>
                </p>
            </div>
            <button class="button button-secondary" onclick="addConverterClick()" type="button">Add cloud converter</button>
            -->
            <?php


            submit_button('Save settings');
            ?>
        </form>
    </div>

<?php
}

//End webp_express_settings_page_content
