<?php

/**
 * @link       https://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/includes
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Chat_Essential_Utility {

	public static function sanitize_json_array( $arr ) {
		$narr = array();
		foreach ($arr as $k => $v) {
			$ty = gettype($v);
			switch ($ty) {
				case 'boolean':
				case 'integer':
				case 'double':
					$narr[$k] = $v;
					break;
				case 'string':
					$narr[$k] = sanitize_text_field($v);
					break;
				case 'array':
					$narr[$k] = Chat_Essential_Utility::sanitize_json_array($v);
					break;
				case 'object':
					$narr[$k] = Chat_Essential_Utility::sanitize_json_object($v);
					break;
				case 'NULL':
					$narr[$k] = null;
					break;
			}
		}

		return $narr;
	}

	public static function sanitize_json_object( $obj ) {
		$nobj = new stdClass();
		foreach ($obj as $k => $v) {
			$ty = gettype($v);
			switch ($ty) {
				case 'boolean':
				case 'integer':
				case 'double':
					$nobj->$k = $v;
					break;
				case 'string':
					$nobj->$k = sanitize_text_field($v);
					break;
				case 'array':
					$nobj->$k = Chat_Essential_Utility::sanitize_json_array($v);
					break;
				case 'object':
					$nobj->$k = Chat_Essential_Utility::sanitize_json_object($v);
					break;
				case 'NULL':
					$nobj->$k = null;
					break;
			}
		}

		return $nobj;
	}

	public static function sanitize_text( $key ) {
		if ( ! empty( $_POST ) &&
			! empty( $_POST['data'] ) &&
			! empty( $_POST['data'][ $key ] ) ) {
			$data = $_POST['data'][ $key ];
			$out = stripslashes_deep( $data );
			$out = sanitize_text_field( $out );
			return $out;
		}
		return '';
	}

	public static function sanitize_array( $key, $type = 'integer' ) {
		if ( ! empty( $_POST ) &&
			! empty( $_POST['data'] ) && 
			! empty( $_POST['data'][ $key ] ) ) {
			$arr = $_POST['data'][ $key ];
			if ( ! is_array( $arr ) ) {
				return array();
			}
			if ( 'integer' === $type ) {
				return array_map( 'absint', $arr );
			} else { // strings
				$new_array = array();
				foreach ( $arr as $val ) {
					$new_array[] = sanitize_text_field( $val );
				}
			}
			return $new_array;
		}
		return array();
	}

	public static function sanitize_post() {
		return array(
			'flow_name' => Chat_Essential_Utility::sanitize_text( 'flow_name' ),
			'options' => Chat_Essential_Utility::sanitize_text( 'options' ),
			'display_on' => Chat_Essential_Utility::sanitize_text( 'display_on' ),
			'status' => Chat_Essential_Utility::sanitize_text( 'status' ),
			'in_pages' => Chat_Essential_Utility::sanitize_array( 'in_pages' ),
			'ex_pages' => Chat_Essential_Utility::sanitize_array( 'ex_pages' ),
			'in_posts' => Chat_Essential_Utility::sanitize_array( 'in_posts' ),
			'ex_posts' => Chat_Essential_Utility::sanitize_array( 'ex_posts' ),
			'in_postTypes' => Chat_Essential_Utility::sanitize_array( 'in_postTypes', 'string' ),
			'in_categories' => Chat_Essential_Utility::sanitize_array( 'in_categories' ),
			'in_tags' => Chat_Essential_Utility::sanitize_array( 'in_tags' ),
		);
	}

	public static function hosted_check() {
		$settings = get_option(CHAT_ESSENTIAL_OPTION);
		if (empty($settings) ||
			empty($settings['dedicated_url']) ||
			empty($settings['dedicated_post_id'])) {
			Chat_Essential_Utility::delete_hosted(true);
			Chat_Essential_Utility::create_hosted(true);
		} else {
			$q = array(
				'numberposts' => 100,
				'post_type' => CHAT_ESSENTIAL_POST_TYPE,
			);
			$posts = get_posts($q);
			if (count($posts) !== 1) {
				Chat_Essential_Utility::delete_hosted(true);
				Chat_Essential_Utility::create_hosted(true);
			}
			if (empty($settings)) {
				$settings = array();
			}
			$settings['dedicated_post_id'] = $posts[0]->ID;
			$settings['dedicated_url'] = get_page_link($posts[0]->ID);
		}
	}

	public static function delete_hosted($deregister) {
		$q = array(
			'numberposts' => 100,
			'post_type' => CHAT_ESSENTIAL_POST_TYPE,
		);

		$posts = get_posts($q);
		foreach($posts as $post) {
			wp_delete_post($post->ID, true);
		}

		if ($deregister) {
			unregister_post_type(CHAT_ESSENTIAL_POST_TYPE);
		}
	}

	public static function create_hosted($register) {
		$web_name = get_option('blogname');

		if ($register) {
			register_post_type(CHAT_ESSENTIAL_POST_TYPE, array(
				'label' => 'Hosted Chat',
				'singular_label' => 'Hosted Chat',
				'show_ui' => false,
				'hierarchical' => true,
				'rewrite' => array("slug" => "hosted"),
				'supports' => array('title', 'editor'),
				'capability_type' => 'page',
				'public' => true,
			));
		}

		$settings = get_option(CHAT_ESSENTIAL_OPTION);
		if (!isset($settings)) {
			$settings = array();
		}

		$pid = wp_insert_post(array(
			'post_title' => 'Chat with Us | ' . $web_name,
			'post_status' => 'publish',
			'post_content' => '<br />Test<br />',
			'post_author' => get_current_user_id(),
			'post_type' => CHAT_ESSENTIAL_POST_TYPE,
		));

		$settings['dedicated_url'] = get_page_link($pid);
		$settings['dedicated_post_id'] = $pid;
		update_option(CHAT_ESSENTIAL_OPTION, $settings);
	}

	public static function db_uninstall() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'chat_essential';

		$sql = "DROP TABLE $table_name;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $sql );

		delete_option('chat_essential_activation_date');
		delete_option('chat_essential_db_version');
	}

	public static function db_install() {
		global $chat_essential_db_version;
		global $wpdb;

		$now = strtotime( "now" );
		add_option( 'chat_essential_activation_date', $now );
		update_option( 'chat_essential_activation_date', $now );

		$table_name = $wpdb->prefix . 'chat_essential';
		$charset_collate = $wpdb->get_charset_collate();
		$sql =
			"CREATE TABLE IF NOT EXISTS $table_name(
				`rules_id` int(10) NOT NULL AUTO_INCREMENT,
				`platform_id` varchar(32) NOT NULL, 
				`api_key` varchar(255) NOT NULL,
				`flow_name` varchar(255) DEFAULT NULL,
				`options` text,
                `order` int DEFAULT NULL,
				`display_on` enum('all','pages', 'posts','categories','postTypes','tags') NOT NULL DEFAULT 'all',
				`in_pages` varchar(300) DEFAULT NULL,
				`ex_pages` varchar(300) DEFAULT NULL,
				`in_posts` varchar(1000) DEFAULT NULL,
				`ex_posts` varchar(300) DEFAULT NULL,
				`in_postTypes` varchar(300) DEFAULT NULL,
				`in_categories` varchar(300) DEFAULT NULL,
				`in_tags` varchar(300) DEFAULT NULL,
				`status` enum('active','inactive') NOT NULL DEFAULT 'inactive',
				`created_by` varchar(300) DEFAULT NULL,
				`last_modified_by` varchar(300) DEFAULT NULL,
				`created` datetime DEFAULT CURRENT_TIMESTAMP,
				`last_revision_date` datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`rules_id`),
				INDEX (`platform_id`)
			)	$charset_collate; ";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		add_option( 'chat_essential_db_version', $chat_essential_db_version );
	}

    /**
     * @since    0.2
     */
    public static function db_migrate($version_from, $version_to) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'chat_essential';

        switch ("$version_from-$version_to") {
            case '0.1-0.2':
                update_option( 'chat_essential_db_version', $version_to );
                $query = "ALTER TABLE `{$table_name}` ADD `order` int DEFAULT NULL AFTER `options`;";
                $wpdb->query( $query );
                break;
        }
    }

	/**
	 * @since    0.0.1
	 */
	public static function db_check() {
		global $chat_essential_db_version;
        $current_version = get_site_option( 'chat_essential_db_version' );
        if ( $current_version != $chat_essential_db_version ) {
            Chat_Essential_Utility::db_migrate($current_version, $chat_essential_db_version);
        }
	}

	public static function logout($apiKey) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'chat_essential';

		$wpdb->delete( $table_name,
			array(
				'api_key' => $apiKey,
			), array(
				'%s',
			)
		);
	}

    public static function update_web_status($rules_id, $status) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'chat_essential';

        $wpdb->update( $table_name,
            array(
                'status' => $status,
            ), array(
                'rules_id' => $rules_id,
            ), array(
                '%s',
            )
        );
    }

    public static function create_web_rules($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'chat_essential';
        $max_order = $wpdb->get_results( "SELECT `order` FROM $table_name ORDER BY `order` DESC LIMIT 1;" );
        $data['order'] = $max_order[0]->order + 1;

        return $wpdb->insert( $table_name, $data );
    }

	public static function get_rules($platformId) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'chat_essential';

		$rule = $wpdb->get_results( "SELECT * FROM $table_name WHERE platform_id='$platformId'" );

		foreach ( $rule as $data ) {
			return $data;
		}

		return array();
	}

    public static function get_all_rules() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'chat_essential';
        $rule = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY `order` ASC" );

        return $rule ?: [];
    }

    public static function reorder_rules($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'chat_essential';

        foreach ($data as $order => $rules_id) {
            $wpdb->update( $table_name,
                array(
                    'order' => $order + 1,
                ), array(
                    'rules_id' => $rules_id,
                ), array(
                    '%s',
                )
            );
        }
    }

	public static function init_user($apiKey, $webs) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'chat_essential';

		$wpdb->delete( $table_name,
			array(
				'api_key' => $apiKey
			), array (
				'%s'
			)
		);

        $order = 1;
		foreach ($webs as $web) {
			$wpdb->insert( $table_name,
				array(
					'flow_name' => $web['id'],
					'platform_id' => $web['platformId'],
					'api_key' => $apiKey,
					'display_on' => 'all',
                    'order' => $order,
				), array(
					'%s',
					'%s',
					'%s',
					'%s',
                    '%d',
				)
			);
            $order++;
		}
	}

	/**
	 * @since    0.0.1
	 * @param    string               $color            Background color.
	 * @param    string               $dark             Darkest reference color.
	 * @param    string               $light            Lightest reference color.
	 * @return   string                                 The color that goes best with the given background.
	 */
	public static function light_or_dark($color, $dark = '#000000', $light = '#FFFFFF') {
		return Chat_Essential_Utility::is_light( $color ) ? $dark : $light;
	}

	/**
	 * @since    0.0.1
	 * @param    string               $color            Hex color.
	 * @return   boolean                                Whether the color is light or dark.
	 */
	public static function is_light($hex) {
		$hex = str_replace('#', '', $hex);
		$c_r = hexdec( substr( $hex, 0, 2 ) );
		$c_g = hexdec( substr( $hex, 2, 2 ) );
		$c_b = hexdec( substr( $hex, 4, 2 ) );

		$brightness = ( ( $c_r * 299 ) + ( $c_g * 587 ) + ( $c_b * 114 ) ) / 1000;

		return array(
			'is_light' => $brightness > 155,
			'brightness' => $brightness,
		);
	}

	/**
	 * @since    0.0.1
	 * @param    string               $color            Hex color.
	 * @return   string                                 Color with # removed and set to 6 characters.
	 */
	public static function clean_color($color) {
		$hex = str_replace( '#', '', $color );
		$len = strlen($hex);
		if ($len != 3 && $len != 6 && $len != 9) {
			return;
		}
		if ($len == 3) {
			$hex = $hex . $hex;
		} else if ($len == 9) {
			$hex = substr($hex, 0, 6);
		}

		return $hex;
	}

	/**
	 * @since    0.0.1
	 * @param    string               $email            The email address for signup.
	 * @return   array                                  The collection of actions and filters registered with WordPress.
	 */
	public static function signup_data($email) {
		$web_name = get_option('blogname');
		$home_url = get_option('home');

		$options = array(
			'customer' => array(
				'email' => $email,
			),
			'integration' => array(
				'installations' => array()
			),
		);
		$tz = wp_timezone();
/*
		'offhoursSetting' => array(
			'timezone' => $tz.getName(),
		)
*/
		$options['integration']['installations'][] = array(
			'url' => $home_url,
			'name' => $web_name,
			'platform' => 'web'
		);

		$qrTheme = array();
		$theme = array();

		global $_wp_admin_css_colors;
		$opt = get_user_option( 'admin_color' );
		if ($opt) {
			$curr = $_wp_admin_css_colors[$opt];
			if (isset($curr)) {
				$primary = null;
				$secondary = null;
				$background = null;
				if (!empty($curr->colors)) {
					$c = Chat_Essential_Utility::clean_color($curr->colors[0]);
					if (!empty($c)) {
						$cc = Chat_Essential_Utility::is_light($c);
						$cc['color'] = '#'.$c;
						$primary = $cc;
					}
				}
				if (!empty($curr->icon_colors)) {
					$choices = array();
					if (!empty($curr->icon_colors['base'])) {
						$c = Chat_Essential_Utility::clean_color($curr->icon_colors['base']);
						if (!empty($c)) {
							$cc = Chat_Essential_Utility::is_light($c);
							if ($cc['is_light'] != $primary['is_light']) {
								$cc['color'] = '#'.$c;
								$choices[] = $cc;
							}
						}
					}
					if (!empty($curr->icon_colors['current'])) {
						$c = Chat_Essential_Utility::clean_color($curr->icon_colors['current']);
						if (!empty($c)) {
							$cc = Chat_Essential_Utility::is_light($c);
							if ($cc['is_light'] != $primary['is_light']) {
								$cc['color'] = '#'.$c;
								$choices[] = $cc;
							}
						}
					}
					if (count($choices) == 2) {
						$light = $choices[0];
						$dark = $choices[1];
						if ($light['brightness'] < $dark['brightness']) {
							$light = $choices[1];
							$dark = $choices[0];
						}
						$secondary = Chat_Essential_Utility::light_or_dark($primary['color'], $light['color'], $dark['color']);
					} else if (count($choices) == 1) {
						$secondary = $choices[0]['color'];
					}

				}
				if ($primary && $secondary) {
					$pc = $primary['color'];
					$theme['header'] = $pc;
					$theme['bubble'] = $pc;
					$theme['bubbleBackground'] = $secondary;
					if ($primary['is_light']) {
						$theme['button'] = $secondary;
						$theme['responseBackground'] = $secondary;
						$qrTheme['background'] = $pc;
						$qrTheme['foreground'] = $secondary;
					} else {
						$theme['button'] = $pc;
						$theme['responseBackground'] = $pc;
						$qrTheme['foreground'] = $pc;
						$qrTheme['background'] = $secondary;
					}
				}
			}
		}

		$icon = get_site_icon_url(200);
		$logo = get_theme_mod( 'custom_logo' );
		if ($icon || $logo) {
			if ($icon) {
				$theme['iconUrl'] = $icon;
				$qrTheme['imageUrl'] = $icon;
			}
			if ($logo) {
				$logo = wp_get_attachment_url($logo);
				if ($logo) {
					$logo = esc_url($logo);
					$theme['logoUrl'] = $logo;
				}
			}
		}

		if (!empty($theme)) {
			$theme['name'] = 'WordPress Plugin';
			$options['theme'] = $theme;
		}
		if (!empty($qrTheme)) {
			$qrTheme['name'] = 'WordPress Plugin';
			$options['qrTheme'] = $qrTheme;
		}

		return $options;
	}

    public static function is_premium() {
        return CHAT_ESSENTIAL_SUBSCRIPTION == 'pro';
    }

    public static function premium_banner() {
        return !Chat_Essential_Utility::is_premium()
            ? '
                <div class="chat-essential-upgrade-banner">
                    <h2>Upgrade to Premium</h2>
                    <ul>
                      <li><strong>+ Automatic re-training:</strong> Your AI assistant will automatically update and re-train when you make content updates</li>
                      <li><strong>+ Unlimited website roles:</strong> Control where your chat appears on your website and which chat flows load</li>
                      <li><strong>+ Unlimited chat themes and flows:</strong> Create customized chat flows for each of section of your website</li>
                    </ul>
                    <a class="button button-primary ey-button top-margin" href="https://www.chatessential.com/wp-premium" target="_blank">Get Chat Essential Premium</a>
                </div>
            '
            : '';
    }

    public static function train_ai_hook($api) {
        $options = get_option(CHAT_ESSENTIAL_OPTION);

        $res = $api->request($options['apiKey'], 'GET', 'nlp/model/' . $options['apiKey'], null, null);
        if ($res['code'] != 200) {
            wp_die('There was an issue loading your settings.', $res['code']);
        }

        $training = [];
        if (!empty($res['data'])) {
            $data = json_decode($res['data']);
            if (!empty($data->nlp) &&
                !empty($data->nlp->model)) {
                if (!empty($data->nlp->model->training) &&
                    !empty($data->nlp->model->training->metadata)
                ) {
                    $training = json_decode($data->nlp->model->training->metadata, true);
                }
            }
        }

        $content = Site_Options::processOptions($training);

        $fname = uniqid(random_int(0, 10), true);
        $res = $api->upload($fname, $content);
        if ($res['code'] != 200) {
            return;
        }
        $reqData = array(
            'fileUrl' => CHAT_ESSENTIAL_UPLOAD_BASE_URL . '/' . $fname . '.json',
            'metadata' => json_encode($training),
            'modelId' => $options['modelId'],
            'engines' => array(
                'gpt3',
            ),
        );

        $res = $api->request($options['apiKey'], 'POST', 'nlp/train/' . $options['apiKey'], array(
            'nlp' => $reqData,
        ), null);

//        echo '<pre>';
//        print_r($res['data']);
//        echo '</pre>';
    }
}
