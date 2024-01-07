<?php
/**
 * MslsPostTagClassic
 * @author Dennis Ploetner <re@lloc.de>
 * @since 0.9.9
 */

namespace lloc\Msls;

/**
 * Post Tag Classic
 *
 * @package Msls
 */
class MslsPostTagClassic extends MslsPostTag {

	/**
	 * Add the input fields to the add-screen of the taxonomies
	 *
	 * @param string $taxonomy
	 */
	public function add_input( string $taxonomy ): void {
		if ( did_action( "{$taxonomy}_add_form_fields" ) !== 1 ) {
			return;
		}

		$title_format = '<h3>%s</h3>';

		$item_format = '<label for="msls_input_%1$s">%2$s</label>
			<select class="msls-translations" name="msls_input_%1$s">
			<option value=""></option>
			%3$s
			</select>';

		echo '<div class="form-field">';
		$this->the_input( null, $title_format, $item_format );
		echo '</div>';
	}

	/**
	 * Add the input fields to the edit-screen of the taxonomies
	 *
	 * @param \WP_Term $tag
	 * @param string $taxonomy
	 */
	public function edit_input( \WP_Term $tag, string $taxonomy ): void {
		if ( did_action( "{$taxonomy}_edit_form_fields" ) !== 1 ) {
			return;
		}

		$title_format = '<tr>
			<th colspan="2">
			<strong>%s</strong>
			</th>
			</tr>';

		$item_format = '<tr class="form-field">
			<th scope="row">
			<label for="msls_input_%1$s">%2$s</label></th>
			<td>
			<select class="msls-translations" name="msls_input_%1$s">
			<option value=""></option>
			%3$s
			</select></td>
			</tr>';

		$this->the_input( $taxonomy, $title_format, $item_format );
	}

	/**
	 * Prints options inputs
	 *
	 * @uses selected
	 * @param MslsBlog $blog
	 * @param string $type
	 * @param MslsOptionsTax $mydata
	 * @param string $item_format
	 */
	public function print_option( MslsBlog $blog, string $type, MslsOptionsTax $mydata, string $item_format ): void {
		switch_to_blog( $blog->userblog_id );

		$language  = $blog->get_language();
		$icon_type = $this->options->get_icon_type();
		$icon      = MslsAdminIcon::create()->set_language( $language )->set_icon_type( $icon_type );

		if ( $mydata->has_value( $language ) ) {
			$icon->set_href( $mydata->$language );
		}

		$options  = '';
		$terms    = get_terms( [ 'taxonomy' => $type, 'hide_empty' => false ] );
		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$options .= sprintf(
					'<option value="%s" %s>%s</option>',
					$term->term_id,
					selected( $term->term_id, $mydata->$language, false ),
					$term->name
				);
			}
		}

		printf( $item_format, $language, $icon, $options );

		restore_current_blog();
	}

	/**
	 * Print the input fields
	 * Returns true if the blogcollection is not empty
	 *
	 * @param mixed $tag
	 * @param string $title_format
	 * @param string $item_format
	 * @return boolean
	 */
	public function the_input( $tag, $title_format, $item_format ) {
		$blogs = $this->collection->get();

		if ( ! empty( $blogs ) ) {
			$term_id = is_object( $tag ) ? $tag->term_id : 0;
			$mydata  = MslsOptionsTax::create( $term_id );
			$type    = MslsContentTypes::create()->get_request();

			$this->maybe_set_linked_term( $mydata );

			printf(
				$title_format,
				apply_filters(
					'msls_term_select_title',
					__( 'Multisite Language Switcher', 'multisite-language-switcher' )
				)
			);

			foreach ( $blogs as $blog ) {
				$this->print_option( $blog, $type, $mydata, $item_format );
			}

			return true;
		}

		return false;
	}
}
