<?php

defined( 'DOING_GRAVITYVIEW_TESTS' ) || exit;

/**
 * @group frontend
 */
class GravityView_frontend_Test extends GV_UnitTestCase {


	/**
	 * @covers GravityView_frontend::process_search_dates()
	 */
	public function test_process_search_dates() {

		$date_range_2014 = array(
			'start_date' => '2014-01-01',
			'end_date' => '2014-12-31',
		);

		$date_range_june_2015 = array(
			'start_date' => '2015-06-01',
			'end_date' => '2015-06-30',
		);

		$date_range_2015 = array(
			'start_date' => '2015-01-01',
			'end_date' => '2015-12-31',
		);


		$search_dates = GravityView_frontend::process_search_dates( array(), $date_range_2015 );
		$this->assertEquals( $date_range_2015, $search_dates, 'No View settings to override; use the passed array' );


		$search_dates = GravityView_frontend::process_search_dates( $date_range_2014, $date_range_2015 );
		$this->assertEquals( array(
			'start_date' => $date_range_2015['start_date'],
			'end_date' => $date_range_2014['end_date'],
		), $search_dates, 'The start date is after the end date, which logs a GravityView error but doesn\'t throw any exceptions. This is expected behavior.' );


		$search_dates = GravityView_frontend::process_search_dates( $date_range_2015, $date_range_june_2015 );
		$this->assertEquals( $date_range_june_2015, $search_dates, 'The 2015 June passed values are all inside 2015 View settings. Use the passed values.' );

		$now = time();

		$yesterday = date( 'Y-m-d H:i:s', strtotime( 'yesterday', $now ) );
		$three_days_ago_ymd = date( 'Y-m-d', strtotime( '3 days ago', $now ) );
		$one_month_ago = date( 'Y-m-d H:i:s', strtotime( '-1 month', $now ) );

		$relative_dates = array(
			'start_date' => date( 'Y-m-d H:i:s', strtotime( '-1 month', $now ) ),
			'end_date' => date( 'Y-m-d H:i:s', strtotime( 'yesterday', $now ) )
		);

		$search_dates = GravityView_frontend::process_search_dates( $relative_dates );
		$this->assertEquals( array( 'start_date' => $one_month_ago, 'end_date' => $yesterday ), $search_dates, 'Make sure the relative dates are formatted in Y-m-d H:i:s format' );

		$search_dates = GravityView_frontend::process_search_dates( $relative_dates, array( 'end_date' => $three_days_ago_ymd ) );
		$this->assertEquals( array( 'start_date' => $one_month_ago, 'end_date' => $three_days_ago_ymd ), $search_dates, 'end_date overridden' );

	}

	/**
	 * @covers GravityView_frontend::get_search_criteria()
	 */
	public function test_get_search_criteria() {

		/** Just an empty test. */
		$this->assertEquals( array(
			'field_filters' => array(), 'status' => 'active'
		), GravityView_frontend::get_search_criteria( array(), 1 ) );

		/** Make sure searching is locked if implicit search_value is given. */
		$criteria = GravityView_frontend::get_search_criteria( array( 'search_value' => 'hello', 'search_field' => '1' ), 1 );

		$this->assertEquals( 'all', $criteria['field_filters']['mode'] );
	}

	/**
	 * @covers GravityView_frontend::single_entry_title()
	 */
	public function test_single_entry_title() {

		// We test check_entry_display elsewhere
		add_filter( 'gravityview/single/title/check_entry_display', '__return_false' );

		$form = $this->factory->form->create_and_get();
		$_entry = $this->factory->entry->create_and_get( array( 'form_id' => $form['id'] ) );
		$_view = $this->factory->view->create_and_get( array( 'form_id' => $form['id'] ) );

		$view = \GV\View::from_post( $_view );
		$entry = \GV\GF_Entry::by_id( $_entry['id'] );

		global $post;

		$post = $_view;

		gravityview()->request = new \GV\Mock_Request();
		gravityview()->request->returns['is_view'] = $view;
		gravityview()->request->returns['is_entry'] = $entry;

		$view->settings->set( 'single_title', '{:1} is the title' );

		$outside_loop = GravityView_frontend::getInstance()->single_entry_title( 'Original Title' );
		$this->assertEquals( 'Original Title', $outside_loop, 'we are outside the loop; this should return the original' );

		add_filter( 'gravityview/single/title/out_loop', '__return_true' );

		$no_post_id = GravityView_frontend::getInstance()->single_entry_title( 'Original Title' );
		$this->assertEquals( 'Original Title', $no_post_id, 'We did not pass a $post ID; this should return the original' );

		$different_ids = GravityView_frontend::getInstance()->single_entry_title( 'Original Title', ( $_view->ID + 1 ) );
		$this->assertEquals( 'Original Title', $different_ids, 'The global $post ID and the passed post id are different; this should return the original' );

		$should_work = GravityView_frontend::getInstance()->single_entry_title( 'Original Title', $_view->ID );
		$this->assertEquals( sprintf( '%s is the title', $_entry['1'] ), $should_work );

		$single_entry_title = GravityView_frontend::getInstance()->single_entry_title( 'Original Title', $_view->ID );
		$this->assertEquals( sprintf( '%s is the title', $_entry['1'] ), $single_entry_title );

		$form2 = $this->factory->form->create_and_get();
		$_entry2 = $this->factory->entry->create_and_get( array( 'form_id' => $form2['id'] ) );
		$_view2 = $this->factory->view->create_and_get( array( 'form_id' => $form2['id'] ) );
		$view2 = \GV\View::from_post( $_view2 );
		$view2->settings->set( 'single_title', '{:1} is the title for two' );

		gravityview()->request = new \GV\Mock_Request();
		gravityview()->request->returns['is_view'] = false;
		gravityview()->request->returns['is_entry'] = $entry;

		global $post;
		$post = $this->factory->post->create_and_get( array(
			'post_content' => '[gravityview id="' . $view->ID . '"][gravityview id="' . $view2->ID . '"]'
		) );

		$single_entry_title = GravityView_frontend::getInstance()->single_entry_title( 'Original Title No GVID', $post->ID );
		$this->assertEquals( 'Original Title No GVID', $single_entry_title, 'The post has two Views but no GVID; should return original' );

		$_GET = array(
			'gvid' => $view->ID
		);
		$entry_1_should_win = GravityView_frontend::getInstance()->single_entry_title( 'Original Title Entry 1', $post->ID );
		$this->assertEquals( sprintf( '%s is the title', $_entry['1'] ), $entry_1_should_win );

		$_GET = array(
			'gvid' => $view2->ID
		);
		$entry_2_should_win = GravityView_frontend::getInstance()->single_entry_title( 'Original Title Entry 2', $post->ID );
		$this->assertEquals( sprintf( '%s is the title for two', $_entry2['1'] ), $entry_2_should_win );

		$post_id = $post->ID;
		unset( $post );
		$_GET = array();
		$single_entry_title = GravityView_frontend::getInstance()->single_entry_title( 'Original Title', $post_id );
		$this->assertEquals( 'Original Title', $single_entry_title, 'There is no global $post and no GVID; should return original' );

		remove_filter( 'gravityview/single/title/out_loop', '__return_true' );
		remove_filter( 'gravityview/single/title/check_entry_display', '__return_false' );
		$_GET = array();
	}

}
