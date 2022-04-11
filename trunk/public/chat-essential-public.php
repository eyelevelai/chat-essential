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
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/chat-essential.css', array(), $this->version, 'all' );
	}

	/**
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/chat-essential.js', array( 'jquery' ), $this->version, false );
	}

    private function filter_rules($rules) {
        global $post;

        $output = [];
        foreach ($rules as $rule) {
            switch ($rule->display_on) {
                case 'all':
                    $output[] = $rule;
                    break;
                case 'posts':
                    $posts = explode(',', $rule->in_posts);
                    if (in_array($post->ID, $posts)) {
                        $output[] = $rule;
                    }
                    break;
                case 'pages':
                    $pages = explode(',', $rule->in_pages);
                    if (in_array($post->ID, $pages)) {
                        $output[] = $rule;
                    }
                    break;
                case 'categories':
                    $categories = get_the_category($post->ID);
                    $categoryIds = [];
                    foreach ($categories as $category) {
                        $categoryIds[] = $category->term_id;
                    }
                    if (!empty(array_intersect($categoryIds, explode(',', $rule->in_categories)))) {
                        $output[] = $rule;
                    }
                    break;
                case 'tags':
                    $tags = get_the_tags($post->ID);
                    $tagIds = [];
                    foreach ($tags as $tag) {
                        $tagIds[] = $tag->term_id;
                    }
                    if (!empty(array_intersect($tagIds, explode(',', $rule->in_tags)))) {
                        $output[] = $rule;
                    }
                    break;
                case 'postTypes':
                    $post_type = get_post_type($post->ID);
                    if (in_array($post_type, explode(',', $rule->in_postTypes))) {
                        $output[] = $rule;
                    }
                    break;
            }
        }

        return $output[0];
    }

	public function manage_footer() {
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
        $rules = $wpdb->get_results( "SELECT * FROM $table_name WHERE status='active' AND api_key='$apiKey' ORDER BY rules_id DESC" );
        $rule = $this->filter_rules($rules);
        $out = '';
        if ( ! empty( $rule ) ) {
            $chat = ['origin' => 'web'];
            if (CHAT_ESSENTIAL_ENV !== 'prod') {
                $chat['env'] = CHAT_ESSENTIAL_ENV;
            }
            $out .= Chat_Essential_Pixel::generatePixel($apiKey, $rule->flow_name, $chat);
        }
        echo $out;
	}

}
