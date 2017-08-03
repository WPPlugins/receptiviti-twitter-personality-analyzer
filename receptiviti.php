<?php
/**
* Plugin Name: Receptiviti Twitter Personality Analyzer
* Plugin URI: http://www.receptiviti.ai
* Description: This plugin provides a handy widget to analyze a people's personality based on their twitter handle
* Version: 1.1.28
* Author: Receptiviti Inc.
* Author URI: http://www.receptiviti.ai
* License: GPL2
*/
/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define("RECEPTIVITI_PLUGIN_NAME__", "Receptiviti");
define("RECEPTIVITI_PLUGIN_SLUG__", "__receptivity_");
define("RECEPTIVITI_VERSION__", 1.0);
define("RECEPTIVITI_DIR__", trailingslashit(plugin_dir_path(__FILE__)));
define("RECEPTIVITI_URL__", plugin_dir_url(__FILE__));
define("RECEPTIVITI_ROOT__", trailingslashit(plugins_url("", __FILE__)));
define("RECEPTIVITI_RESOURCES__", RECEPTIVITI_ROOT__ . "resources/");
define("RECEPTIVITI_IMAGES__", RECEPTIVITI_RESOURCES__ . "images/");
define("RECEPTIVITI_API_URL_BASE__", "https://app.receptiviti.com");
define("RECEPTIVITI_PLUGIN_USER_AGENT__", "receptiviti-wp-twitter-plugin");

define("RECEPTIVITI_DEBUG__", false);
define("RECEPTIVITI_TEST__", false);
define("RECEPTIVITI_STAGING__", false);

if (RECEPTIVITI_DEBUG__) {
    @error_reporting(E_ALL);
    @ini_set("display_errors", "1");
}

/**
 * Abort loading if WordPress is upgrading
 */
if (defined("WP_INSTALLING") && WP_INSTALLING) return;

class Receptivity
{

    private $graphValues    = "";
    private $snapshot       = array();
    private $communication_recommendation  = "";

    private static $GRAPH_CATEGORIES    = array(
        "openness" => "Openness",
        "conscientious" => "Conscientious",
        "extraversion" => "Extraversion",
        "agreeable" => "Agreeable",
        "neuroticism" => "Neuroticism",
        "thinking_style" => "Thinking Style",
        "power_driven" => "Power Driven",
        "impulsive" => "Impulsive",
        "independent" => "Independent",
        "cold" => "Cold",
        "insecure" => "Insecure",
        "adjustment" => "Adjusted",
        "happiness" => "Happiness",
        "persuasive" => "Persuasive",
        "achievement_driven" => "Achievement Driven",
        "social_skills" => "Social Skills",
        "type_a" => "Type A",
        "depression" => "Depression",
        "workhorse" => "Workhorse",
        "friend_focus" => "Friendship Focus",
        "body_focus" => "Body Focus",
        "family_oriented" => "Family Oriented",
        "reward_bias" => "Reward Bias",
        "health_oriented" => "Health Oriented",
        "sexual_focus" => "Sexually Focused",
        "food_focus" => "Food Focused",
        "leisure_oriented" => "Leisure Oriented",
        "money_oriented" => "Money Oriented",
        "religion_oriented" => "Religion Oriented",
        "work_oriented" => "Work Oriented",
        "netspeak_focus" => "Netspeak"
    );

    public function __construct()
    {
        @mkdir(RECEPTIVITI_DIR__ . "tmp");

        register_activation_hook(__FILE__ , array($this, "receptivity_activate"));
        register_deactivation_hook(__FILE__ , array($this, "receptivity_deactivate"));

        $this->loadHooks();
    }

    private function loadHooks()
    {
        add_action("init", array($this, "receptivity_register"));
        add_action("wp_enqueue_scripts", array($this, "receptivity_includeResources"));
        add_action("admin_enqueue_scripts", array($this, "receptivity_includeResources"));
        add_action("plugins_loaded", array($this, "receptivity_i18n"));

        add_action("admin_menu", array($this, "receptivity_add_menu"));
        add_action("wp_ajax_" . RECEPTIVITI_PLUGIN_SLUG__, array($this, "ajax"));
        add_shortcode("receptiviti", array($this, "receptivity_shortcode"));
    }

    function receptivity_i18n()
    {
        $pluginDirName  = dirname(plugin_basename(__FILE__));
        $domain         = RECEPTIVITI_PLUGIN_SLUG__;
        $locale         = apply_filters("plugin_locale", get_locale(), $domain);
        load_textdomain($domain, WP_LANG_DIR . "/" . $pluginDirName . "/" . $domain . "-" . $locale . ".mo");
        load_plugin_textdomain($domain, "", $pluginDirName . "/resources/lang/");
    }

    function receptivity_add_menu()
    {
        add_menu_page(RECEPTIVITI_PLUGIN_NAME__, "<img src='https://staging-api.receptiviti.com/static/images/receptiviti.png' style='max-width: 50%'>", "manage_options", RECEPTIVITI_PLUGIN_SLUG__, array($this, "receptivity_settings"), "dashicons-twitter");
    }

    function receptivity_settings()
    {
        if (isset($_POST["ra-settings"]) && $_POST["tab"] == "api") {
            self::setOption("key", $_POST["key"]);
            self::setOption("secret", $_POST["secret"]);
            self::setOption("app_url", $_POST["app_url"]);
            self::setOption("plugin_version", $_POST["plugin_version"]);
        }

        include_once RECEPTIVITI_DIR__ . "resources/admin/includes/settings.php";
    }

    function receptivity_includeResources()
    {
        wp_enqueue_script("jquery");

        if (!is_admin()) {
            wp_register_script("jquery.jqplot", RECEPTIVITI_RESOURCES__ . "lib/jqplot/jquery.jqplot.min.js", array("jquery"));
            wp_enqueue_script("jquery.jqplot");
            wp_register_script("jqplot.barRenderer", RECEPTIVITI_RESOURCES__ . "lib/jqplot/jqplot.barRenderer.min.js", array("jquery.jqplot"));
            wp_enqueue_script("jqplot.barRenderer");
            wp_register_script("jqplot.categoryAxisRenderer", RECEPTIVITI_RESOURCES__ . "lib/jqplot/jqplot.categoryAxisRenderer.min.js", array("jquery.jqplot"));
            wp_enqueue_script("jqplot.categoryAxisRenderer");
            wp_register_script("jqplot.pointLabels", RECEPTIVITI_RESOURCES__ . "lib/jqplot/jqplot.pointLabels.min.js", array("jquery.jqplot"));
            wp_enqueue_script("jqplot.pointLabels");

            wp_register_style("jquery.jqplot", RECEPTIVITI_RESOURCES__ . "lib/jqplot/jquery.jqplot.min.css");
            wp_enqueue_style("jquery.jqplot");

            wp_enqueue_script("receptiviti", RECEPTIVITI_RESOURCES__ . "public/js/receptiviti.js", array("jquery"));
            wp_localize_script("receptiviti", "receptiviti", array(
                "i18n"      => array(
                    "title"     => __("Scores", RECEPTIVITI_PLUGIN_SLUG__),
                    "label-x"   => __("Percentile", RECEPTIVITI_PLUGIN_SLUG__),
                )
            ));

            wp_register_style("receptiviti", RECEPTIVITI_RESOURCES__ . "public/css/receptiviti.css");
            wp_enqueue_style("receptiviti");
        }
    }

    function receptivity_register()
    {
        // do nothing
    }

    function receptivity_activate()
    {
        @unlink(RECEPTIVITI_DIR__ . "tmp/log.log");
    }

    function receptivity_deactivate(){
        // do nothing
    }

	public function receptivity_shortcode($atts, $content="No ID")
    {
        $styleAtts  = array();
		$atts       = shortcode_atts(array_merge($styleAtts, array("handle" => "")), $atts, "receptiviti");

        $isManual   = !isset($atts["handle"]) || empty($atts["handle"]);
        $handle     = isset($atts["handle"]) ? $atts["handle"] : "";
        $error      = "";
        $output     = "";

        if (isset($_POST["handle"])) {
            $handle     = $_POST["handle"];
        }

        if (!empty($handle)) {
            $result = self::callAPI(
                self::get_receptiviti_twitter_API_url() . "user",
                array(
                    "method"    => "json",
                    "json"      => true,
                ),
                array(
                    "screen_name"   => $handle,
                ),
                array(
                    "Content-Type"      => "application/json",
                    "Accept"            => "application/json",
                    "X-API-KEY"         => self::getOption("key"),
                    "X-API-SECRET-KEY"  => self::getOption("secret"),
                    "User-Agent"  => self::get_plugin_user_agent_header(),
                )
            );

            if (intval($result["error"]) == 200) {
                $requestURL     = self::get_receptiviti_base_url() . $result["response"]["_links"]["self"]["href"];
                $status         = "";

                do {
                    sleep(5);
                    set_time_limit(0);
                    $result = self::callAPI(
                        $requestURL,
                        array(
                            "method"    => "get",
                            "json"      => true,
                        ),
                        array(),
                        array(
                            "Accept"            => "application/json",
                            "X-API-KEY"         => self::getOption("key"),
                            "X-API-SECRET-KEY"  => self::getOption("secret"),
                        )
                    );

                    $status     = $result["response"]["status"];

                } while (!in_array($status, array("Finished", "Failed", "Error")));

                if ($status == "Finished") {
                    $result = self::callAPI(
                        $requestURL . "/people",
                        array(
                            "method"    => "get",
                            "json"      => true,
                        ),
                        array(),
                        array(
                            "Accept"            => "application/json",
                            "X-API-KEY"         => self::getOption("key"),
                            "X-API-SECRET-KEY"  => self::getOption("secret"),
                        )
                    );

                    if (RECEPTIVITI_TEST__) $output         = json_encode($result["response"]);
                
                    $percentiles        = $result["response"][0]["receptiviti_scores"]["percentiles"];
                    foreach (array_reverse(self::$GRAPH_CATEGORIES) as $key=>$label) {
                        if (strlen($this->graphValues) > 0) $this->graphValues .= ",";
                        $this->graphValues  .= "[" . $percentiles[$key] . ", " . "'$label']";
                    }

                    $snapshot           = $result["response"][0]["personality_snapshot"];
                    foreach ($snapshot as $snap) {
                        $this->snapshot[$snap["summary"]]   = $snap["description"];
                    }
                    $this->communication_recommendation = $result["response"][0]["communication_recommendation"];                    
                } else {
                    $error          = $result["response"]["error_message"];
                }
            } else {
                $output     = json_encode($result["response"]["message"]);
            }

        }
        ob_start();
        include_once RECEPTIVITI_DIR__ . "resources/templates/search.php";
        return ob_get_clean();
	}

    /****************************************** Util functions ******************************************/

    private static function callAPI($url, $props=array(), $params=array(), $headers=array())
    {
        $body       = null;
        $error      = null;
        $conn       = curl_init($url);

        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($conn, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($conn, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($conn, CURLOPT_HEADER, 0);
        curl_setopt($conn, CURLOPT_NOSIGNAL, 1);

        if ($headers) {
            $header     = array();
            foreach ($headers as $key=>$val) {
                $header[]   = "$key: $val";
            }
            curl_setopt($conn, CURLOPT_HTTPHEADER, $header);
        }

        if ($props && isset($props["method"]) && $props["method"] === "post") {
            curl_setopt($conn, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
        }

        if ($props && isset($props["method"]) && $props["method"] === "json") {
            curl_setopt($conn, CURLOPT_POSTFIELDS, json_encode($params));
        }

        try {
            $body           = curl_exec($conn);
            $error          = curl_getinfo($conn, CURLINFO_HTTP_CODE);
        } catch (Exception $e) {
            self::writeDebug("Exception " . $e->getMessage());
        }

        if (curl_errno($conn)) {
            self::writeDebug("curl_errno ".curl_error($conn));
        }

        curl_close($conn);

        if ($props && isset($props["json"]) && $props["json"]) {
            $body   = json_decode($body, true);
        }

        $array          = array(
            "response"  => $body,
            "error"     => $error,
        );

        self::writeDebug("Calling ". $url. " with fields = " . print_r($params, true) . " returning raw response " . $body . " and finally returning " . print_r($array,true));

        return $array;
    }


    /**
     * Get Receptiviti Base URL
     */
    public static function get_receptiviti_base_url()
    {
        $val = self::getOption("app_url");
        return $val? $val : RECEPTIVITI_API_URL_BASE__;
    }

    /**
     * Get Receptiviti Base URL
     */
    public static function get_receptiviti_twitter_API_url()
    {
        return self::get_receptiviti_base_url() . "/v2/api/import/twitter/";
    }

    /**
     * Get Plugin User Agent Header
     */
    public static function get_plugin_user_agent_header()
    {
        return RECEPTIVITI_PLUGIN_USER_AGENT__ . "/" . self::getOption("plugin_version");
    }

    /**
     * Writes to the file /tmp/log.log if DEBUG is on
     */
    public static function writeDebug($msg)
    {
        if (RECEPTIVITI_DEBUG__) file_put_contents(RECEPTIVITI_DIR__ . "tmp/log.log", date("F j, Y H:i:s", current_time("timestamp")) . " - " . $msg."\n", FILE_APPEND);
    }

    /**
     * Custom wrapper for the get_option function
     * 
     * @return string
     */
    public static function getOption($field, $clean=false)
    {
        $val = get_option(RECEPTIVITI_PLUGIN_SLUG__ . $field);
        return $clean ? htmlspecialchars($val) : $val;
    }

    /**
     * Custom wrapper for the update_option function
     * 
     * @return mixed
     */
    public static function setOption($field, $value)
    {
        return update_option(RECEPTIVITI_PLUGIN_SLUG__ . $field, $value);
    }
}

$receptivity = new Receptivity();
