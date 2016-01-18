<?php

namespace Duncanjbrown\PlainRoute;
/**
 * A skinny version of HM-Rewrite, where I got all the ideas.
 * All credit due: https://github.com/humanmade/hm-rewrite
 *
 * This class calls its callbacks dynamically, so you can hook anything you like by
 * passing eg 'pre_get_posts' into the $args.
 *
 * Do bear in mind that this can break things.
 */

/**
 * Plain_Route. Add arbitrary routing points. Instantiate on init.
 *
 *
 *
 * Example
 *
 * add_action( 'init', function() {
 * 	new Plain_Route( 'stripe(/)?', [
 * 		'rewrite' => 'p=123',
 * 		'pre_get_posts' => function( $query ) {
 * 			if( $query->is_main_query() ) {
 * 				$query->set('stripe', true);
 * 			}
 * 		}
 * 	]);
 * });
 */
class PlainRoute {

	// The rule against which we match a request
	private $regex;

	// Callbacks for the matched rule
	private $args;

	// Names for query vars required
	private $names;

	/**
	* Send in a regex, eg 'myroute/?$', plus args for callbacks, and an array of query vars used
	*
	* @param string $regex the rewrite regex
	* @param array $args see below
	* @param array $names query vars to add
	*/
	function __construct( $regex, $args, $names  = array() ) {
		$this->names = $names;
		$this->args = $args;
		$this->regex = $regex;

		add_action( 'rewrite_rules_array', function( $rules ) use ( $regex, $args ) {
			$new_rules = array();
			$new_rules[$regex] = 'index.php?' . ( empty( $args['rewrite'] ) ? '' : $args['rewrite'] );
			return $new_rules + $rules;
		});

		add_action( 'query_vars', function( $vars ) use ( $names ) {
			foreach( $names as $name )  {
				$vars[] = $name;
			}
			return $vars;
		});

		add_filter( 'parse_request', array( &$this, 'try_match' ) );
	}

	public function get_regex() {
		return $this->regex;
	}

	public function try_match( $request ) {
		if ( $this->regex == $request->matched_rule ) {
			$this->engage_callbacks();
		}
	}

	private function engage_callbacks() {
		$hooks = [
			'pre_get_posts',
			'wp_title',
			'wp',
			'template'
		];

		foreach( $hooks as $hook ) {
			if ( isset( $this->args[$hook] ) )
			add_filter( $hook, array( &$this, $hook ) );
		}

		/**
		* Template is a little different
		*/
		if ( isset( $this->args['template'] ) ) {
			add_filter( 'template', array( &$this, 'template' ), 1, 0 );
		}

		do_action( 'plain_routes', $this->args );
	}

	public function template() {
		include locate_template( $this->args['template'] );
		exit;
	}

	public function __call( $method, $args ) {
		call_user_func_array( $this->args[$method], $args );
	}

}
