<?php

/**
 * Post Tag
 *
 * @package Msls
 */

/**
 * MslsPostTag extends MslsMain
 */
require_once dirname( __FILE__ ) . '/MslsMain.php';

/**
 * MslsAdminIcon is used
 */
require_once dirname( __FILE__ ) . '/MslsLink.php';

/**
 * MslsPostTag
 * 
 * @package Msls
 */
class MslsPostTag extends MslsMain {

    /**
     * Init
     */
    public static function init() {
        $options = MslsOptions::instance();
        if ( !$options->is_excluded() && isset( $_REQUEST['taxonomy'] ) ) {
            $taxonomy = MslsContentTypes::create()->get_request();
            if ( !empty( $taxonomy ) ) {
                $obj = new self();
                add_action( "{$taxonomy}_edit_form_fields", array( $obj, 'add' ) );
                add_action( "{$taxonomy}_add_form_fields", array( $obj, 'add' ) );
                add_action( "edited_{$taxonomy}", array( $obj, 'set' ), 10, 2 );
                add_action( "create_{$taxonomy}", array( $obj, 'set' ), 10, 2 );
            }
        }
    }

    /**
     * Add
     * 
     * @param StdClass
     */
    public function add( $tag ) {
        $term_id = ( is_object( $tag ) ? $tag->term_id : 0 );
        $blogs   = $this->blogs->get();
        if ( $blogs ) {
            printf(
                '<tr><th colspan="2"><strong>%s</strong></th></tr>',
                __( 'Multisite Language Switcher', 'msls' )
            );
            $mydata = MslsTaxOptions::create( $term_id );
            $type   = MslsContentTypes::create()->get_request();
            foreach ( $blogs as $blog ) {
                switch_to_blog( $blog->userblog_id );
                $language = $blog->get_language();
                $icon     = $this->get_icon( $language, $mydata->$language );
                $options  = '';
                $terms    = get_terms( $type, array( 'hide_empty' => 0 ) );
                if ( !empty( $terms ) ) {
                    foreach ( $terms as $term ) {
                        $options .= sprintf(
                            '<option value="%s"%s>%s</option>',
                            $term->term_id,
                            ( $term->term_id == $mydata->$language ? ' selected="selected"' : '' ),
                            $term->name
                        );
                    }
                }
                printf(
                    '<tr class="form-field"><th scope="row" valign="top"><label for="msls[%s]">%s </label></th><td><select class="msls-translations" name="msls[%s]"><option value=""></option>%s</select></td>',
                    $language,
                    $icon,
                    $language,
                    $options
                );
                restore_current_blog();
            }
        }
    }

    /**
     * Set
     * 
     * @param int $term_id
     * @param int $tt_id
     */
    public function set( $term_id, $tt_id ) {
        $tax = get_taxonomy( $_REQUEST['taxonomy'] );
        if ( $tax && current_user_can( $tax->cap->manage_terms ) )
            $this->save( $term_id, 'MslsTaxOptions' );
    }

}

?>
