<?php declare( strict_types=1 );

namespace lloc\MslsTests;

use lloc\Msls\MslsBlogCollection;
use lloc\Msls\MslsCustomFilter;
use lloc\Msls\MslsOptions;
use Brain\Monkey\Functions;

class TestMslsCustomFilter extends MslsUnitTestCase {

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $options = \Mockery::mock( MslsOptions::class );

        $collection = \Mockery::mock( MslsBlogCollection::class );
        $collection->shouldReceive( 'get' )->once()->andReturn( [] );

        $this->test = new MslsCustomFilter( $options, $collection );

    }

    public function test_execute_filter(): void {
        $query = \Mockery::mock( 'WP_Query' );

        $this->assertFalse( $this->test->execute_filter( $query ) );
	}

    public function test_execute_filter_with_filter_input(): void {
        Functions\expect('filter_has_var')->once()->with( INPUT_GET, 'msls_filter' )->andReturn( true );

        $query = \Mockery::mock( 'WP_Query' );

        $this->assertFalse( $this->test->execute_filter( $query ) );
    }

    public function test_add_filter(): void {
        $this->expectOutputString('' );

        $this->test->add_filter();
    }
}
