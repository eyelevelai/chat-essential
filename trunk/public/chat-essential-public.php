<?php

/**
 * @link       http://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/public
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Public {

	/**
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * @since    0.0.1
	 */
	public function ey_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/chat-essential.css', array(), $this->version, 'all' );
	}

	/**
	 * @since    0.0.1
	 */
	public function ey_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/chat-essential.js', array( 'jquery' ), $this->version, false );
	}

    private function filter_rules($rules) {
        global $post;

        $output = [];
        foreach ($rules as $rule) {
            if (!empty($rule->device_display) && $rule->device_display != 'both') {
                if ($rule->device_display == 'mobile' && !wp_is_mobile()) {
                    $output = [];
                    break;
                }
                if ($rule->device_display == 'desktop' && wp_is_mobile()) {
                    $output = [];
                    break;
                }
            }

            switch ($rule->display_on) {
                case 'all':
                    $output[] = $rule;
                    if (isset($rule->ex_pages) && !empty($rule->ex_pages)) {
                        $pages = explode(',', $rule->ex_pages);
                        if (!empty($post) && isset($post) && !empty($post->ID) && isset($post->ID) && in_array($post->ID, $pages)) {
                            $output = [];
                        }
                    }
                    if (isset($rule->ex_posts) && !empty($rule->ex_posts)) {
                        $posts = explode(',', $rule->ex_posts);
                        if (!empty($post) && isset($post) && !empty($post->ID) && isset($post->ID) && in_array($post->ID, $posts)) {
                            $output = [];
                        }
                    }
                    break;
                case 'posts':
                    $posts = explode(',', $rule->in_posts);
                    if (!empty($post) && isset($post) && !empty($post->ID) && isset($post->ID) && in_array($post->ID, $posts)) {
                        $output[] = $rule;
                    }
                    break;
                case 'pages':
                    $pages = explode(',', $rule->in_pages);
                    if (!empty($post) && isset($post) && !empty($post->ID) && isset($post->ID) && in_array($post->ID, $pages)) {
                        $output[] = $rule;
                    }
                    break;
                case 'categories':
                    if (!empty($post) && isset($post) && !empty($post->ID) && isset($post->ID)) {
                        $categories = get_the_category($post->ID);
                        $categoryIds = [];
                        foreach ($categories as $category) {
                            $categoryIds[] = $category->term_id;
                        }
                        if (!empty(array_intersect($categoryIds, explode(',', $rule->in_categories)))) {
                            $output[] = $rule;
                        }
                    }
                    break;
                case 'tags':
                    if (!empty($post) && isset($post) && !empty($post->ID) && isset($post->ID)) {
                        $tags = get_the_tags($post->ID);
                        $tagIds = [];
                        foreach ($tags as $tag) {
                            $tagIds[] = $tag->term_id;
                        }
                        if (!empty(array_intersect($tagIds, explode(',', $rule->in_tags)))) {
                            $output[] = $rule;
                        }
                    }
                    break;
                case 'postTypes':
                    if (!empty($post) && isset($post) && !empty($post->ID) && isset($post->ID)) {
                        $post_type = get_post_type($post->ID);
                        if (in_array($post_type, explode(',', $rule->in_postTypes))) {
                            $output[] = $rule;
                        }
                    }
                    break;
            }
        }

        if (count($output) > 0) {
            return $output[0];
        }
    }

	public function ey_load_chat() {
		if (is_admin()) {
			return;
		}

		$settings = get_option(CHAT_ESSENTIAL_OPTION);
		if (empty($settings) ||
			empty($settings['apiKey'])) {
			return;
		}
		$apiKey = $settings['apiKey'];

		global $wpdb;

        $table_name = $wpdb->prefix . 'chat_essential';
        $rules = $wpdb->get_results( "SELECT * FROM $table_name WHERE status='active' AND api_key='$apiKey' ORDER BY `order` ASC" );
        $rule = $this->filter_rules($rules);
        $out = '';
        if ( ! empty( $rule ) ) {
            $chat = ['origin' => 'web'];
            if (CHAT_ESSENTIAL_ENV !== 'prod') {
                $chat['env'] = CHAT_ESSENTIAL_ENV;
            }
            if (!empty($rule->bubble_placement) && $rule->bubble_placement == 'left') {
                $chat['invert'] = true;
            }
            $out .= Chat_Essential_Pixel::generatePixel($apiKey, $rule->flow_name, $chat);
        }
        echo $out;
	}

}
