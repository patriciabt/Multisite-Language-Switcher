<?php

namespace lloc\MslsTests;

use lloc\Msls\MslsBlog;
use lloc\Msls\MslsPostTag;
use lloc\Msls\MslsOptions;
use lloc\Msls\MslsBlogCollection;
use Brain\Monkey\Functions;

/**
 * WP_Test_MslsPostTag
 */
class WP_Test_MslsPostTag extends Msls_UnitTestCase {

	public function setUp(): void {
		parent::setUp();

		Functions\when( 'get_option' )->justReturn( [] );
		Functions\expect( 'is_admin' )->andReturn( true );
		Functions\expect( 'get_post_types' )->andReturn( [ 'post', 'page' ] );

		foreach (  [ 'de_DE', 'en_US' ] as $locale ) {
			$blog = \Mockery::mock( MslsBlog::class );
			$blog->shouldReceive( [
				'get_language' => $locale,
			] );

			$blogs[] = $blog;
		}

		$options = \Mockery::mock( MslsOptions::class );
		$options->shouldReceive( 'get_icon_type' )->andReturn( 'label' );

		$collection = \Mockery::mock( MslsBlogCollection::class );
		$collection->shouldReceive( 'get' )->andReturn( $blogs );

		Functions\expect( 'msls_blog_collection' )->once()->andReturn( $collection );

		$this->test = MslsPostTag::init();
	}

	/**
	 * Verify the static suggest-method
	 */
	public function test_suggest(): void {
		Functions\expect( 'wp_die' );

		self::expectOutputString( '' );

		MslsPostTag::suggest();
	}

	public function test_edit_input() {
		Functions\expect( 'did_action' )->andReturn( 1 );
		Functions\expect( 'get_queried_object_id' )->andReturn( 42 );
		Functions\expect( 'get_current_blog_id' )->andReturn( 23 );
		Functions\expect( 'get_admin_url' )->andReturn( '/wp-admin/edit-tags.php' );
		Functions\expect( 'switch_to_blog' )->atLeast();
		Functions\expect( 'restore_current_blog' )->atLeast();
		Functions\expect( 'get_terms' )->andReturn( [] );
		Functions\expect( 'plugin_dir_path' )->atLeast( 1 )->andReturn( dirname( __DIR__, 1 ) . '/' );

		$output = '<tr>
			<th colspan="2">
			<strong>Multisite Language Switcher</strong>
			</th>
			</tr><tr class="form-field">
			<th scope="row">
			<label for="msls_input_de_DE"><a title="Create a new translation in the de_DE-blog" href="/wp-admin/edit-tags.php"><span class="flag-icon flag-icon-de">de_DE</span></a>&nbsp;</label></th>
			<td>
			<select class="msls-translations" name="msls_input_de_DE">
			<option value=""></option>
			
			</select></td>
			</tr><tr class="form-field">
			<th scope="row">
			<label for="msls_input_en_US"><a title="Create a new translation in the en_US-blog" href="/wp-admin/edit-tags.php"><span class="flag-icon flag-icon-us">en_US</span></a>&nbsp;</label></th>
			<td>
			<select class="msls-translations" name="msls_input_en_US">
			<option value=""></option>
			
			</select></td>
			</tr>';

		self::expectOutputString( $output );

		$this->test->edit_input( 'test' );
	}


	public function test_add_input_second_call() {
		Functions\expect( 'did_action' )->andReturn( 2 );

		self::expectOutputString( '' );

		$this->test->add_input( 'test' );
	}

	public function test_edit_input_second_call() {
		Functions\expect( 'did_action' )->andReturn( 2 );

		self::expectOutputString( '' );

		$this->test->edit_input( 'test' );
	}

}
