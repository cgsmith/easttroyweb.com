<?php
/**
 * Genesis Framework.
 *
 * WARNING: This file is part of the core Genesis Framework. DO NOT edit this file under any circumstances.
 * Please do all modifications in the form of a child theme.
 *
 * @package Genesis\Admin
 * @author  StudioPress
 * @license GPL-2.0+
 * @link    http://my.studiopress.com/themes/genesis/
 */

/**
 * Register a new admin page, providing content and corresponding menu item for the CPT Archive Settings page.
 *
 * @package Genesis\Admin
 */
class Genesis_Admin_CPT_Archive_Settings extends Genesis_Admin_Boxes {

	/**
	 * Post type object.
	 *
	 * @var \stdClass
	 */
	protected $post_type;

	/**
	 * Create an archive settings admin menu item and settings page for relevant custom post types.
	 *
	 * @since 2.0.0
	 *
	 * @uses GENESIS_CPT_ARCHIVE_SETTINGS_FIELD_PREFIX Settings field key prefix.
	 * @uses \Genesis_Admin::create()                  Create admin menu and settings page.
	 *
	 * @param \stdClass $post_type Post Type object.
	 */
	public function __construct( $post_type ) {
		$this->post_type = $post_type;

		$page_id = 'genesis-cpt-archive-' . $this->post_type->name;

		$menu_ops = array(
			'submenu' => array (
				'parent_slug' => 'edit.php?post_type=' . $this->post_type->name,
				'page_title'  => apply_filters( 'genesis_cpt_archive_settings_page_label', __( 'Archive Settings', 'genesis' ) ),
				'menu_title'  => apply_filters( 'genesis_cpt_archive_settings_menu_label', __( 'Archive Settings', 'genesis' ) ),
				'capability'  => 'manage_categories',
			)
		);

		//* Handle non-top-level CPT menu items
		if ( is_string( $this->post_type->show_in_menu ) ) {
			$menu_ops['submenu']['parent_slug'] = $this->post_type->show_in_menu;
			$menu_ops['submenu']['menu_title']  = apply_filters( 'genesis_cpt_archive_settings_label', $this->post_type->labels->name . ' ' . __( 'Archive', 'genesis' ) );
			$menu_ops['submenu']['menu_position']  = $this->post_type->menu_position;
		}

		$page_ops = array(); //* use defaults

		$settings_field = GENESIS_CPT_ARCHIVE_SETTINGS_FIELD_PREFIX . $this->post_type->name;

		$default_settings = apply_filters(
			'genesis_cpt_archive_settings_defaults',
			array(
				'headline'    => '',
				'intro_text'  => '',
				'doctitle'    => '',
				'description' => '',
				'keywords'    => '',
				'layout'      => '',
				'body_class'  => '',
				'noindex'     => 0,
				'nofollow'    => 0,
				'noarchive'   => 0,
			),
			$this->post_type->name
		);

		$this->create( $page_id, $menu_ops, $page_ops, $settings_field, $default_settings );

		add_action( 'genesis_settings_sanitizer_init', array( $this, 'sanitizer_filters' ) );
	}

	/**
	 * Register each of the settings with a sanitization filter type.
	 *
	 * @since 2.0.0
	 *
	 * @uses genesis_add_option_filter() Assign filter to array of settings.
	 *
	 * @see \Genesis_Settings_Sanitizer::add_filter()
	 */
	public function sanitizer_filters() {

		genesis_add_option_filter(
			'no_html',
			$this->settings_field,
			array(
				'headline',
				'doctitle',
				'description',
				'keywords',
				'body_class',
			)
		);
		genesis_add_option_filter(
			'safe_html',
			$this->settings_field,
			array(
				'intro_text',
			)
		);
		genesis_add_option_filter(
			'one_zero',
			$this->settings_field,
			array(
				'noindex',
				'nofollow',
				'noarchive',
			)
		);
	}

	/**
 	 * Register meta boxes on the CPT Archive pages.
 	 *
 	 * Some of the meta box additions are dependent on certain theme support or user capabilities.
 	 *
 	 * The 'genesis_cpt_archives_settings_metaboxes' action hook is called at the end of this function.
 	 *
 	 * @since 2.0.0
 	 *
 	 * @see \Genesis_Admin_CPT_Archives_Settings::archive_box() Callback for Archive box.
 	 * @see \Genesis_Admin_CPT_Archives_Settings::seo_box()     Callback for SEO box.
 	 * @see \Genesis_Admin_CPT_Archives_Settings::layout_box()  Callback for Layout box.
	 */
	public function metaboxes() {

		add_meta_box( 'genesis-cpt-archives-settings', __( 'Archive Settings', 'genesis' ), array( $this, 'archive_box' ), $this->pagehook, 'main' );

		if ( ! genesis_seo_disabled() ) {
			add_meta_box( 'genesis-cpt-archives-seo-settings', __( 'SEO Settings', 'genesis' ), array( $this, 'seo_box' ), $this->pagehook, 'main' );
		}

		if ( genesis_has_multiple_layouts() ) {
			add_meta_box( 'genesis-cpt-archives-layout-settings', __( 'Layout Settings', 'genesis' ), array( $this, 'layout_box' ), $this->pagehook, 'main' );
		}

		do_action( 'genesis_cpt_archives_settings_metaboxes', $this->pagehook );

	}

	/**
	 * Callback for Archive Settings meta box.
	 *
	 * @since 2.0.0
	 *
	 * @uses \Genesis_Admin::get_field_id()    Construct full field id.
	 * @uses \Genesis_Admin::get_field_name()  Construct full field name.
	 * @uses \Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field.
	 *
	 * @see \Genesis_Admin_Settings::metaboxes() Register meta boxes.
	 */
	public function archive_box() {
		?>
		<p><?php printf( __( 'View the <a href="%s">%s archive</a>.', 'genesis' ), get_post_type_archive_link( $this->post_type->name ), $this->post_type->name ); ?></p>

		<table class="form-table">
		<tbody>

			<tr valign="top">
				<th scope="row"><label for="<?php $this->field_id( 'headline' ); ?>"><b><?php _e( 'Archive Headline', 'genesis' ); ?></b></label></th>
				<td>
					<p><input class="large-text" type="text" name="<?php $this->field_name( 'headline' ); ?>" id="<?php $this->field_id( 'headline' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'headline' ) ); ?>" /></p>
					<p class="description"><?php _e( 'Leave empty if you do not want to display a headline.', 'genesis' ); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="<?php $this->field_id( 'intro_text' ); ?>"><b><?php _e( 'Archive Intro Text', 'genesis' ); ?></b></label></th>
				<td>
					<?php wp_editor( $this->get_field_value( 'intro_text' ), $this->settings_field . "-intro-text", array( 'textarea_name' => $this->get_field_name( 'intro_text' ) ) ); ?>
					<p class="description"><?php _e( 'Leave empty if you do not want to display any intro text.', 'genesis' ); ?></p>
				</td>
			</tr>

		</tbody>
		</table>

		<?php
	}

	/**
	 * Callback for SEO Settings meta box.
	 *
	 * @since 2.0.0
	 *
	 * @uses \Genesis_Admin::get_field_id()    Construct full field id.
	 * @uses \Genesis_Admin::get_field_name()  Construct full field name.
	 * @uses \Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field.
	 *
	 * @see \Genesis_Admin_Settings::metaboxes() Register meta boxes.
	 */
	public function seo_box() {
		?>

		<table class="form-table">
		<tbody>

			<tr valign="top">
				<th scope="row"><label for="<?php $this->field_id( 'doctitle' ); ?>"><b><?php _e( 'Custom Document Title', 'genesis' ); ?></th>
				<td>
					<p><input class="large-text" type="text" name="<?php $this->field_name( 'doctitle' ); ?>" id="<?php $this->field_id( 'doctitle' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'doctitle' ) ); ?>" /></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="<?php $this->field_id( 'doctitle' ); ?>"><b><?php _e( 'Meta Description', 'genesis' ); ?></th>
				<td>
					<p><input class="large-text" type="text" name="<?php $this->field_name( 'description' ); ?>" id="<?php $this->field_id( 'description' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'description' ) ); ?>" /></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="<?php $this->field_id( 'doctitle' ); ?>"><b><?php _e( 'Meta Keywords', 'genesis' ); ?></th>
				<td>
					<p><input class="large-text" type="text" name="<?php $this->field_name( 'keywords' ); ?>" id="<?php $this->field_id( 'keywords' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'keywords' ) ); ?>" /></p>
					<p class="description"><?php _e( 'Comma separated list', 'genesis' ); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e( 'Robots Meta Tags:', 'genesis' ); ?></th>
				<td>
					<p>
						<label for="<?php $this->field_id( 'noindex' ); ?>"><input type="checkbox" name="<?php $this->field_name( 'noindex' ); ?>" id="<?php $this->field_id( 'noindex' ); ?>" value="1" <?php checked( $this->get_field_value( 'noindex' ) ); ?> />
						<?php printf( __( 'Apply %s to this archive', 'genesis' ), genesis_code( 'noindex' ) ); ?> <a href="http://yoast.com/articles/robots-meta-tags/" target="_blank">[?]</a></label><br />

						<label for="<?php $this->field_id( 'nofollow' ); ?>"><input type="checkbox" name="<?php $this->field_name( 'nofollow' ); ?>" id="<?php $this->field_id( 'nofollow' ); ?>" value="1" <?php checked( $this->get_field_value( 'nofollow' ) ); ?> />
						<?php printf( __( 'Apply %s to this archive', 'genesis' ), genesis_code( 'nofollow' ) ); ?> <a href="http://yoast.com/articles/robots-meta-tags/" target="_blank">[?]</a></label><br />

						<label for="<?php $this->field_id( 'noarchive' ); ?>"><input type="checkbox" name="<?php $this->field_name( 'noarchive' ); ?>" id="<?php $this->field_id( 'noarchive' ); ?>" value="1" <?php checked( $this->get_field_value( 'noarchive' ) ); ?> />
						<?php printf( __( 'Apply %s to this archive', 'genesis' ), genesis_code( 'noarchive' ) ); ?> <a href="http://yoast.com/articles/robots-meta-tags/" target="_blank">[?]</a></label>
					</p>
				</td>
			</tr>

		</tbody>
		</table>

		<?php
	}

	/**
	 * Callback for Layout Settings meta box.
	 *
	 * @since 2.0.0
	 *
	 * @uses \Genesis_Admin::get_field_id()    Construct full field id.
	 * @uses \Genesis_Admin::get_field_name()  Construct full field name.
	 * @uses \Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field.
	 * @uses genesis_layout_selector()         Display layout selector.
	 *
	 * @see \Genesis_Admin_Settings::metaboxes() Register meta boxes.
	 */
	public function layout_box() {
		$layout = $this->get_field_value( 'layout' );

		?>

		<table class="form-table">
		<tbody>

			<tr valign="top">
				<th scope="row"><?php _e( 'Select Layout', 'genesis' ); ?></th>
				<td>
					<fieldset class="genesis-layout-selector">
						<legend class="screen-reader-text"><?php _e( 'Layout Settings', 'genesis' ); ?></legend>

						<p><input type="radio" class="default-layout" name="<?php $this->field_name( 'layout' ); ?>" id="default-layout" value="" <?php checked( $layout, '' ); ?> /> <label class="default" for="default-layout"><?php printf( __( 'Default Layout set in <a href="%s">Theme Settings</a>', 'genesis' ), menu_page_url( 'genesis', 0 ) ); ?></label></p>
						<?php genesis_layout_selector( array( 'name' => $this->get_field_name( 'layout' ), 'selected' => $layout, 'type' => 'site' ) ); ?>

					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="<?php $this->field_id( 'body_class' ); ?>"><b><?php _e( 'Custom Body Class', 'genesis' ); ?></b></label></th>
				<td>
					<p><input class="large-text" type="text" name="<?php $this->field_name( 'body_class' ); ?>" id="<?php $this->field_id( 'body_class' ); ?>" value="<?php echo esc_attr( $this->get_field_value( 'body_class' ) ); ?>" /></p>
				</td>
			</tr>

		</tbody>
		</table>

		<?php
	}

	/**
	 * Add contextual help content for the archive settings page.
	 *
	 * @since 2.0.0
	 *
	 * @todo Populate this contextual help method.
	 */
	public function help() {
		$screen = get_current_screen();
		$archive_help =
			'<h3>' . __( 'Archive Settings', 'genesis' ) . '</h3>' .
			'<p>' . __( 'The Archive Headline sets the title seen on the archive page', 'genesis' ) . '</p>' .
			'<p>' . __( 'The Archive Intro Text sets the text before the archive entries to introduce the content to the viewer.', 'genesis' ) . '</p>';

		$screen->add_help_tab(
			array(
				'id'      => $this->pagehook . '-archive',
				'title'   => __( 'Archive Settings', 'genesis' ),
				'content' => $archive_help,
			)
		);

		$seo_help =
			'<h3>' . __( 'SEO Settings', 'genesis' ) . '</h3>' .
			'<p>' . __( 'The Custom Document Title sets the page title as seen in browsers and search engines. ', 'genesis' ) . '</p>' .
			'<p>' . __( 'The Meta description and keywords fill in the meta tags for the archive page. The Meta description is the short text blurb that appear in search engine results.
 ', 'genesis' ) . '</p>' .
			'<p>' . __( 'Most search engines do not use Keywords at this time or give them very little consideration; however, it\'s worth using in case keywords are given greater consideration in the future and also to help guide your content. If the content doesn’t match with your targeted key words, then you may need to consider your content more carefully.', 'genesis' ) . '</p>' .
			'<p>' . __( 'The Robots Meta Tags tell search engines how to handle the archive page. Noindex means not to index the page at all, and it will not appear in search results. Nofollow means do not follow any links from this page and noarchive tells them not to make an archive copy of the page.', 'genesis' ) . '</p>';

		$screen->add_help_tab(
			array(
				'id'      => $this->pagehook . '-seo',
				'title'   => __( 'SEO Settings', 'genesis' ),
				'content' => $seo_help,
			)
		);

		$layout_help =
			'<h3>' . __( 'Layout Settings', 'genesis' ) . '</h3>' .
			'<p>'  . __( 'This lets you select the layout for the archive page. On most of the child themes you\'ll see these options:', 'genesis' ) . '</p>' .
			'<ul>' .
				'<li>' . __( 'Content Sidebar', 'genesis' ) . '</li>' .
				'<li>' . __( 'Sidebar Content', 'genesis' ) . '</li>' .
				'<li>' . __( 'Sidebar Content Sidebar', 'genesis' ) . '</li>' .
				'<li>' . __( 'Content Sidebar Sidebar', 'genesis' ) . '</li>' .
				'<li>' . __( 'Sidebar Sidebar Content', 'genesis' ) . '</li>' .
				'<li>' . __( 'Full Width Content', 'genesis' ) . '</li>' .
			'</ul>' .
			'<p>'  . __( 'These options can be extended or limited by the child theme.', 'genesis' ) . '</p>' .
			'<p>'  . __( 'The Custom Body Class adds a class to the body tag in the HTML to allow CSS modification exclusively for this post type\'s archive page.', 'genesis' ) . '</p>';

		$screen->add_help_tab(
			array(
				'id'      => $this->pagehook . '-layout',
				'title'   => __( 'Layout Settings', 'genesis' ),
				'content' => $layout_help,
			)
		);
	}
}
