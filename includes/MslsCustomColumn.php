<?php

/**
 * Custom Column
 *
 * @package Msls
 */

/**
 * MslsCustomColumn extends MslsMain
 */
require_once dirname( __FILE__ ) . '/MslsMain.php';

/**
 * MslsCustomColumn::init() uses MslsOptions for a check
 */ 
require_once dirname( __FILE__ ) . '/MslsOptions.php';

/**
 * MslsAdminIcon is used
 */
require_once dirname( __FILE__ ) . '/MslsLink.php';

/**
 * MslsCustomColumn
 * 
 * @package Msls
 */
class MslsCustomColumn extends MslsMain {

    /**
     * Init
     */
    public static function init() {
        $options = MslsOptions::instance();
        if ( !$options->is_excluded() ) {
            $post_type = MslsPostType::instance()->get_request();
            if ( !empty( $post_type ) ) {
                $obj = new self();
                add_filter( "manage_{$post_type}_posts_columns", array( $obj, 'th' ) );
                add_action( "manage_{$post_type}_posts_custom_column", array( $obj, 'td' ), 10, 2 );
            }
        }
    }

    /**
     * Table header
     * 
     * @param array $columns
     * @return array
     */
    public function th( $columns ) {
        $blogs = $this->blogs->get();
        if ( $blogs ) {
            $arr = array();
            foreach ( $blogs as $blog ) {
                $language = $blog->get_language();
                $icon     = new MslsAdminIcon( null );
                $icon->set_language( $language );
                $icon->set_src( $this->options->get_flag_url( $language, true ) );
                $arr[] = $icon->get_img();
            }
            $columns['mslscol'] = implode( '&nbsp;', $arr );
        }
        return $columns;
    }

    /**
     * Table body
     * 
     * @param string $column_name
     * @param int $item_id
     */
    public function td( $column_name, $item_id ) {
        if ( 'mslscol' == $column_name ) {
            $blogs = $this->blogs->get();
            if ( $blogs ) {
                $mydata = MslsOptions::create( $item_id );
                foreach ( $blogs as $blog ) {
                    switch_to_blog( $blog->userblog_id );
                    $language = $blog->get_language();
                    $href     = ( $mydata->has_value( $language ) ? $mydata->$language : 0 );
                    $src      = $this->options->get_url( $href > 0 ? 'images/link_edit.png' : 'images/link_add.png' ); 
                    echo $this->get_icon( $language, $href, $src );
                    restore_current_blog();
                }
            }
        }
    }

}

/**
 * MslsCustomColumnTaxonomy
 * 
 * @package Msls
 */
class MslsCustomColumnTaxonomy extends MslsCustomColumn {

    /**
     * Init
     */
    static function init() {
        $options = MslsOptions::instance();
        if ( !$options->is_excluded() ) {
            $taxonomy = MslsTaxonomy::instance()->get_request();
            if ( !empty( $taxonomy ) ) {
                $obj = new self();
                add_filter( "manage_edit-{$taxonomy}_columns" , array( $obj, 'th' ) );
                add_action( "manage_{$taxonomy}_custom_column" , array( $obj, 'td' ), 10, 3 );
            }
        }
    }

    /**
     * Table body
     * 
     * @param string $deprecated
     * @param string $column_name
     * @param int $item_id
     */
    public function td( $deprecated, $column_name, $item_id ) {
        parent::td( $column_name, $item_id );
    }

}

?>
