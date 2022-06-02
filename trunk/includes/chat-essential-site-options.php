<?php

/**
 * @link       https://www.chatessential.com
 * @since      0.0.1
 *
 * @package    Chat_Essential
 * @subpackage Chat_Essential/includes
 * @author     Chat Essential <support@eyelevel.ai>
 */
class Site_Options {

	public static function optionNames() {
		return array(
			'all' => chat_essential_localize('Site Wide'),
			'pages' => chat_essential_localize('Specific Pages'),
			'posts' => chat_essential_localize('Specific Posts'),
			'categories' => chat_essential_localize('Specific Categories'),
			'tags' => chat_essential_localize('Specific Tags'),
			'postTypes' => chat_essential_localize('Specific Post Types'),
			'none' => chat_essential_localize('None'),
		);
	}

	public static function cleanContent($content) {
		$pg = $content->to_array();
		$raw_txt = html_entity_decode(strip_shortcodes(wp_strip_all_tags(apply_filters('the_content', $content->post_content))));
		$c_txt = preg_replace( "/(\r\n|\r|\n)+/", "\n", $raw_txt );
		$c_txt = preg_replace( "/[ \t]+/", "\n", $c_txt );
		$c_txt = preg_replace( "/(\r\n|\r|\n)+/", " ", $c_txt );
		if (strlen($c_txt) > CHAT_ESSENTIAL_MIN_TRAINING_PAGE_CONTENT) {
			$pg['c_len'] = strlen($c_txt);
			$pg['content'] = $raw_txt;
			$pg['url'] = get_permalink($content);
			return $pg;
		}
		return;
	}

	/**
	 * @since    0.0.1
	 */
	public static function processOptions($options) {
		$content = array();

		if (empty($options) || empty($options['siteType'])) {
			return $content;
		}

		if ($options['siteType'] !== 'posts' &&
			$options['siteType'] !== 'postTypes' &&
			$options['siteType'] !== 'none') {
			$q = array(
				'hierarchical' => true,
				'post_type' => 'page',
				'sort_order' => 'desc',
				'sort_column' => 'post_modified',
			);
			if (!empty($options['ex_pages'])) {
				$ex = array();
				foreach ($options['ex_pages'] as $exp) {
					$ex[] = intval($exp);
				}
				$q['exclude'] = $ex;
			}
			if (!empty($options['in_pages'])) {
				$in = array();
				foreach ($options['in_pages'] as $inp) {
					$in[] = intval($inp);
				}
				$q['include'] = $in;
			}
			$pages = get_pages($q);
			foreach($pages as $page) {
				if (Site_Options::shouldInclude($options, $page)) {
					$pg = Site_Options::cleanContent($page);
					if ($pg) {
						$content[] = $pg;
					}
				}
			}
		}

		if ($options['siteType'] !== 'pages' &&
			$options['siteType'] !== 'none') {
			$q = array(
				'numberposts' => -1,
			);
			if (!empty($options['ex_posts'])) {
				$ex = array();
				foreach ($options['ex_posts'] as $exp) {
					$ex[] = intval($exp);
				}
				$q['exclude'] = $ex;
			}
			if (!empty($options['in_posts'])) {
				$in = array();
				foreach ($options['in_posts'] as $inp) {
					$in[] = intval($inp);
				}
				$q['include'] = $in;
			}
			if (!empty($options['in_postTypes'])) {
				$q['post_type'] = $options['in_postTypes'];
			}
			$posts = get_posts($q);
			foreach($posts as $post) {
				if (Site_Options::shouldInclude($options, $post)) {
					$pg = Site_Options::cleanContent($post);
					if ($pg) {
						$content[] = $pg;
					}
				}
			}
		}

		return $content;
	}

	/**
	 * @since    0.0.1
	 */
	public static function shouldInclude($options, $page) {
		switch ($options['siteType']) {
			case 'all':
				return true;
			case 'posts':
				return true;
			case 'pages':
				return true;
			case 'categories':
				if (empty($options['in_categories'])) {
					return false;
				}
				foreach ($options['in_categories'] as $val) {
					if (has_category($val, $page)) {
						return true;
					}
				}
				return false;
			case 'tags':
				if (empty($options['in_tags'])) {
					return false;
				}
				foreach ($options['in_tags'] as $val) {
					if (has_tag($val, $page)) {
						return true;
					}
				}
				return false;
			case 'postTypes':
				return true;
		}

		return false;
	}

	public static function mapDBRecord($dbr) {
		if (!empty($dbr->display_on)) {
			$dbr->siteType = $dbr->display_on;
			if (!empty($dbr->in_pages)) {
				$dbr->in_pages = explode(",", $dbr->in_pages);
			}
			if (!empty($dbr->ex_pages)) {
				$dbr->ex_pages = explode(",", $dbr->ex_pages);
			}
			if (!empty($dbr->in_posts)) {
				$dbr->in_posts = explode(",", $dbr->in_posts);
			}
			if (!empty($dbr->ex_posts)) {
				$dbr->ex_posts = explode(",", $dbr->ex_posts);
			}
			if (!empty($dbr->in_postTypes)) {
				$dbr->in_postTypes = explode(",", $dbr->in_postTypes);
			}
			if (!empty($dbr->in_categories)) {
				$dbr->in_categories = explode(",", $dbr->in_categories);
			}
			if (!empty($dbr->in_tags)) {
				$dbr->in_tags = explode(",", $dbr->in_tags);
			}
			return (array) $dbr;
		}

		return [];
	}

	/**
	 * @since    0.0.1
	 */
	public static function typeSelector($training) {
		$pages = get_pages(array(
			'hierarchical' => true,
			'post_type' => 'page',
			'sort_order' => 'desc',
			'sort_column' => 'post_modified',
		));
		$posts = get_posts(array(
			'numberposts' => -1,
		));

		$args = array( 'hide_empty' => 0 );
		$categories = get_categories( $args );
		$tags = get_tags( $args );

		$args = array( 'public' => true );
		$output = 'names';
		$operator = 'and';
		$cpostTypes = get_post_types( $args, $output, $operator );
		$postTypes = array( 'post' );
		foreach ( $cpostTypes as $cpdata ) {
			$postTypes[] = $cpdata;
		}

		if (empty($training) || count($training) < 1) {
			$training = array(
				'siteType' => 'all',
			);
		}

		$expages = ( 'pages' === $training['siteType'] || 'none' === $training['siteType'] ) ? 'display:none;' : '';
		$exposts = ( 'posts' === $training['siteType'] || 'none' === $training['siteType'] ) ? 'display:none;' : '';
		$excategories = 'categories' === $training['siteType'] ? 'display:none;' : '';
		$extags = 'tags' === $training['siteType'] ? 'display:none;' : '';
		$expostTypes = 'postTypes' === $training['siteType'] ? 'display:none;' : '';
		$spages = ( 'pages' === $training['siteType'] ) ? '' : 'display:none;';
		$sposts = ( 'posts' === $training['siteType'] ) ? '' : 'display:none;';
		$scategories = 'categories' === $training['siteType'] ? '' : 'display:none;';
		$stags = 'tags' === $training['siteType'] ? '' : 'display:none;';
		$cposts = 'postTypes' === $training['siteType'] ? '' : 'display:none;';

		$l1 = chat_essential_localize('Site Display');
		$l2 = chat_essential_localize('Exclude Pages');
		$l3 = chat_essential_localize('Exclude Posts');
		$l4 = chat_essential_localize('Pages');
		$l5 = chat_essential_localize('Posts');
		$l6 = chat_essential_localize('Categories');
		$l7 = chat_essential_localize('Tags');
		$l8 = chat_essential_localize('Post Types');

		$html = '<tr><th><label for="data[current_type]">' . $l1 . '</label></th>';
		$html .= '<td><select name="data[current_type]" id="siteTypeSelect" onchange="showTypeOptions(this.value);">';
		foreach (Site_Options::optionNames() as $key => $val) {
			if ($training['siteType'] === $key) {
				$html .= '<option selected="selected" value="' . $key . '">' . $val . '</option>';
			} else {
				$html .= '<option value="' . $key . '">' . $val . '</option>';
			}
		}
		$html .= '</select></td></tr>';

		$html .= '<tr id="excludePages" style="' . $expages . $exposts . $extags . $expostTypes . $excategories . '">';
		$html .= '<th><label for="data[ex_pages][]">' . $l2 . '</label></th>';
		$html .= '<td><select id="exPages" name="data[ex_pages][]" multiple>';
		foreach ( $pages as $pdata ) {
			if ( !empty($training['ex_pages']) && in_array( $pdata->ID, $training['ex_pages'] ) ) {
				$html .= sprintf( '<option value="%1$s" selected="selected">%2$s</option>', $pdata->ID, $pdata->post_title );
			} else {
				$html .= sprintf( '<option value="%1$s">%2$s</option>', $pdata->ID, $pdata->post_title );
			}
		}
		$html .= '</select></td></tr>';

		$html .= '<tr id="excludePosts" style="' . $expages . $exposts . $extags . $expostTypes . $excategories . '">';
		$html .= '<th><label for="data[ex_posts][]">' . $l3 . '</label></th>';
		$html .= '<td><select id="exPosts" name="data[ex_posts][]" multiple>';
		foreach ( $posts as $pdata ) {
			if ( !empty($training['ex_posts']) && in_array( $pdata->ID, $training['ex_posts'] ) ) {
				$html .= sprintf( '<option value="%1$s" selected="selected">%2$s</option>', $pdata->ID, $pdata->post_title );
			} else {
				$html .= sprintf( '<option value="%1$s">%2$s</option>', $pdata->ID, $pdata->post_title );
			}
		}
		$html .= '</select></td></tr>';

		$html .= '<tr id="pages" style="' . $spages . '">';
		$html .= '<th><label for="data[in_pages][]">' . $l4 . '</label></th>';
		$html .= '<td><select id="inPages" name="data[in_pages][]" multiple>';
		foreach ( $pages as $pdata ) {
			if ( !empty($training['in_pages']) && in_array( $pdata->ID, $training['in_pages'] ) ) {
				$html .= sprintf( '<option value="%1$s" selected="selected">%2$s</option>', $pdata->ID, $pdata->post_title );
			} else {
				$html .= sprintf( '<option value="%1$s">%2$s</option>', $pdata->ID, $pdata->post_title );
			}
		}
		$html .= '</select></td></tr>';

		$html .= '<tr id="posts" style="' . $sposts . '">';
		$html .= '<th><label for="data[in_posts][]">' . $l5 . '</label></th>';
		$html .= '<td><select id="inPosts" name="data[in_posts][]" multiple>';
		foreach ( $posts as $pdata ) {
			if ( !empty($training['in_posts']) && in_array( $pdata->ID, $training['in_posts'] ) ) {
				$html .= sprintf( '<option value="%1$s" selected="selected">%2$s</option>', $pdata->ID, $pdata->post_title );
			} else {
				$html .= sprintf( '<option value="%1$s">%2$s</option>', $pdata->ID, $pdata->post_title );
			}
		}
		$html .= '</select></td></tr>';

		$html .= '<tr id="categories" style="' . $scategories . '">';
		$html .= '<th><label for="data[in_categories][]">' . $l6 . '</label></th>';
		$html .= '<td><select id="inCategories" name="data[in_categories][]" multiple>';
		foreach ( $categories as $cdata ) {
			if ( !empty($training['in_categories']) && in_array( $cdata->term_id, $training['in_categories'] ) ) {
				$html .= '<option value="' . $cdata->term_id . '" selected>' . $cdata->name . '</option>';
			} else {
				$html .= '<option value="' . $cdata->term_id . '">' . $cdata->name . '</option>';
			}
		}
		$html .= '</select></td></tr>';

		$html .= '<tr id="tags" style="' . $stags . '">';
		$html .= '<th><label for="data[in_tags][]">' . $l7 . '</label></th>';
		$html .= '<td><select id="inTags" name="data[in_tags][]" multiple>';
		foreach ( $tags as $tdata ) {
			if ( !empty($training['in_tags']) && in_array( $tdata->term_id, $training['in_tags'] ) ) {
				$html .= '<option value="' . $tdata->term_id . '" selected>' . $tdata->name . '</option>';
			} else {
				$html .= '<option value="' . $tdata->term_id . '">' . $tdata->name . '</option>';
			}
		}
		$html .= '</select></td></tr>';

		$html .= '<tr id="postTypes" style="' . $cposts . '">';
		$html .= '<th><label for="data[in_postTypes][]">' . $l8 . '</label></th>';
		$html .= '<td><select id="inPostTypes" name="data[in_postTypes][]" multiple>';
		foreach ( $cpostTypes as $cpkey => $cpdata ) {
			if ( !empty($training['in_postTypes']) && in_array( $cpkey, $training['in_postTypes'] ) ) {
				$html .= '<option value="' . $cpkey . '" selected>' . $cpdata . '</option>';
			} else {
				$html .= '<option value="' . $cpkey . '">' . $cpdata . '</option>';
			}
		}
		$html .= '</select></td></tr>';

		return $html;
	}

}
