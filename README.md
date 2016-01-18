Purpose
===

This library provides a simple interface for WordPress's fiddly rewrite rules API.

It's useful for creating arbitrary endpoints with callbacks. For example, a webhook receiver for a payment gateway.

Usage
===

Creating an endpoint is accomplished by `new`ing one up. You should do this on `init`.

```
add_action( 'init', function() {
  new Plain_Route( 'stripe(/)?', [
    'rewrite' => 'p=123',
    'pre_get_posts' => function( $query ) {
    if( $query->is_main_query() ) {
      $query->set('stripe', true);
    }
  }]);
});
```

In the above example, `pre_get_posts` could be replaced with `wp_title` or `wp`. Or you could add them alongside.

You can also render any template with the special `template` callback.

Creating an endpoint that renders a specific template:

```
add_action( 'init', function() {
 	new Plain_Route( 'my-special-template(/)?', [
 		'template' => 'my-special-template.php'
 	]);
});
```


Credit
===

This class is a much-simplified variation of HM_Rewrite by humanmade.
