<?php

use WP_CLI\CommandWithDBObject;
use WP_CLI\Iterators\Table as TableIterator;
use WP_CLI\Utils;
use WP_CLI\Formatter;

/**
 * WP-CLI Command to easily manage quota in WordPress Multisite environments
 *
 * @package wp-cli
 */
class Quota_Command {

	/**
	 * Lists all sites in a multisite installation with quota.
	 *
	 * ## OPTIONS
	 *
	 * [--fields=<fields>]
	 * : Comma-separated list of fields to show.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - count
	 *   - ids
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each site:
	 *
	 * * blog_id
	 * * url
	 * * quota
	 * * quota_used
	 * * quota_used_percent
	 *
	 *
	 * ## EXAMPLES
	 *
	 *     # Output a simple list of site URLs
	 *     $ wp quota list --field=url
	 *     http://www.example.com/
	 *     http://www.example.com/subdir/
	 *
	 * @subcommand list
	 */
	public function list_( $args, $assoc_args ) {
		if ( ! is_multisite() ) {
			WP_CLI::error( 'This is not a multisite installation.' );
		}

		$this->display_formatted_items( $assoc_args );

	}

	/**
	 * Display formatted items
	 *
	 * @param $assoc_args
	 * @param int $blog_id
	 */
	private function display_formatted_items( $assoc_args, $blog_id = 0 ) {
		global $wpdb;

		if ( isset( $assoc_args['fields'] ) ) {
			$assoc_args['fields'] = preg_split( '/,[ \t]*/', $assoc_args['fields'] );
		}

		$defaults   = [
			'format' => 'table',
			'fields' => [ 'blog_id', 'url', 'quota', 'quota_used', 'quota_used_percent' ],
		];
		$assoc_args = array_merge( $defaults, $assoc_args );


		$where = [];

		if ( $blog_id ) {
			$where = [ 'blog_id' => $blog_id ];
		}

		$append = '';

		$site_cols = [
			'blog_id',
			'quota',
			'quota_used',
			'quota_used_percent',
		];
		foreach ( $site_cols as $col ) {
			if ( isset( $assoc_args[ $col ] ) ) {
				$where[ $col ] = $assoc_args[ $col ];
			}
		}

		$iterator_args = [
			'table'  => $wpdb->blogs,
			'where'  => $where,
			'append' => $append,
		];

		$iterator = new TableIterator( $iterator_args );

		$iterator = Utils\iterator_map(
			$iterator,
			function ( $blog ) {
				return $this->determine_quotas( $blog );
			}
		);

		if ( ! empty( $assoc_args['format'] ) && 'ids' === $assoc_args['format'] ) {
			$sites     = iterator_to_array( $iterator );
			$ids       = wp_list_pluck( $sites, 'blog_id' );
			$formatter = new Formatter( $assoc_args, null, 'site' );
			$formatter->display_items( $ids );
		} else {
			$formatter = new Formatter( $assoc_args, null, 'site' );
			$formatter->display_items( $iterator );
		}
	}

	/**
	 * Determine quotas
	 *
	 * @param $blog
	 *
	 * @return mixed
	 */
	private function determine_quotas( $blog ) {
		$blog->url = trailingslashit( get_site_url( $blog->blog_id ) );

		switch_to_blog( $blog->blog_id );

		// Get quota
		$quota       = get_space_allowed();
		$blog->quota = $quota;

		// Get quota used & quota used in percent
		$used = get_space_used();

		if ( $used >= $quota ) {
			$percentused = '100';
		} else {
			$percentused = ( $used / $quota ) * 100;
		}
		$blog->quota_used         = round( $used, 2 );
		$blog->quota_used_percent = round( $percentused, 2 );

		restore_current_blog();

		return $blog;
	}


	/**
	 * Returns the quota information for a single site
	 *
	 * ## OPTIONS
	 *
	 * [<id>]
	 * : The id of the site to get.
	 *
	 * [--fields=<fields>]
	 * : Comma-separated list of fields to show.
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - csv
	 *   - count
	 *   - ids
	 *   - json
	 *   - yaml
	 * ---
	 *
	 * ## AVAILABLE FIELDS
	 *
	 * These fields will be displayed by default for each site:
	 *
	 * * blog_id
	 * * url
	 * * quota
	 * * quota_used
	 * * quota_used_percent
	 *
	 *
	 * ## EXAMPLES
	 *
	 *     # Output the quota of blog 3
	 *     $ wp quota get 3
	 *     +---------+--------------------------------------------------------+--------------+----------------+---------------------+
	 *     | blog_id | url                                                    | quota        | quota_used     | quota_used_percent  |
	 *     +---------+--------------------------------------------------------+--------------+----------------+---------------------+
	 *     | 3       | https://dev-site.local/subsite3                        | 10000        | 9850           | 98.50               |
	 *     +---------+--------------------------------------------------------+--------------+----------------+---------------------+
	 *
	 * @subcommand get
	 */
	public function get( $args, $assoc_args ) {
		if ( ! is_multisite() ) {
			WP_CLI::error( 'This is not a multisite installation.' );
		}

		if ( empty( $args ) ) {
			WP_CLI::error( 'Need to specify a blog id.' );
		}

		global $wpdb;
		$blog_id = $args[0];
		$blog    = get_blog_details( $blog_id );
		if ( ! $blog ) {
			WP_CLI::error( 'Site not found.' );
		}

		$this->display_formatted_items( $assoc_args, $blog_id );

	}
}