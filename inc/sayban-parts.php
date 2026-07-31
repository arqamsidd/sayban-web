<?php
/**
 * Sayban.pk — shared parts: listing data, design card, and the
 * [sayban_finder] + [sayban_featured] shortcodes (used to replace REM's
 * Elementor widgets on the homepage). Included from functions.php.
 */
defined( 'ABSPATH' ) || exit;

/* Parse REM's text price ("2.35 Crore") into a number. */
if ( ! function_exists( 'sayban_price_to_num' ) ) {
	function sayban_price_to_num( $txt ) {
		$txt = strtolower( trim( (string) $txt ) );
		if ( $txt === '' || ! preg_match( '/([0-9]+(?:\.[0-9]+)?)/', $txt, $m ) ) { return 0; }
		$n = (float) $m[1];
		if ( strpos( $txt, 'crore' ) !== false || strpos( $txt, 'cr' ) !== false )   { return (int) round( $n * 10000000 ); }
		if ( strpos( $txt, 'lakh' ) !== false || strpos( $txt, 'lac' ) !== false )    { return (int) round( $n * 100000 ); }
		if ( strpos( $txt, 'thousand' ) !== false )                                    { return (int) round( $n * 1000 ); }
		return (int) round( $n );
	}
}

function sayban_currency() {
	return function_exists( 'rem_get_currency_symbol' ) ? rem_get_currency_symbol() : 'PKR ';
}

/**
 * Resolve a Sayban page URL by the REM shortcode(s) it contains, then return
 * get_permalink() for it — so internal links keep working even if the page slug
 * (or ID) changes. Cached per request. Keys: login, register, create, dashboard, listings.
 */
function sayban_page_url( $key ) {
	static $cache = array();
	if ( array_key_exists( $key, $cache ) ) { return $cache[ $key ]; }
	global $wpdb;
	$find = function ( $includes, $exclude = '' ) use ( $wpdb ) {
		$includes = (array) $includes;
		$where = '';
		$args  = array();
		foreach ( $includes as $inc ) { $where .= ' AND post_content LIKE %s'; $args[] = '%' . $wpdb->esc_like( $inc ) . '%'; }
		if ( $exclude ) { $where .= ' AND post_content NOT LIKE %s'; $args[] = '%' . $wpdb->esc_like( $exclude ) . '%'; }
		$sql = "SELECT ID FROM {$wpdb->posts} WHERE post_type='page' AND post_status='publish'" . $where . ' ORDER BY ID ASC LIMIT 1';
		return (int) $wpdb->get_var( $wpdb->prepare( $sql, $args ) );
	};
	$id = 0;
	switch ( $key ) {
		case 'login':     $id = $find( '[rem_agent_login', '[rem_create_property' ); break; // standalone login (not the create wrapper)
		case 'register':  $id = $find( '[rem_register_agent' ); break;
		case 'create':    $id = $find( '[rem_create_property' ); break;
		case 'dashboard': $id = $find( '[rem_my_properties' ); break;
		case 'profile':   $id = $find( '[rem_agent_edit' ); break;   // edit-profile page
		case 'editprop':  $id = $find( '[rem_edit_property' ); break; // edit-property page
		case 'listings':  $id = $find( array( '[rem_list_properties', 'top_bar' ) ); break; // our /properties/ page (id 604)
	}
	$url = $id ? get_permalink( $id ) : home_url( '/' );
	$cache[ $key ] = $url;
	return $url;
}

/* Collect the fields a card needs for one listing. */
function sayban_listing_data( $pid ) {
	$cats = wp_get_post_terms( $pid, 'rem_property_cat', array( 'fields' => 'slugs' ) );
	return array(
		'id'    => $pid,
		'link'  => get_permalink( $pid ),
		'title' => get_the_title( $pid ),
		'price' => trim( (string) get_post_meta( $pid, 'rem_property_price', true ) ),
		'cat'   => is_array( $cats ) && $cats ? $cats[0] : '',
		'feat'  => get_post_meta( $pid, 'rem_property_featured', true ) === 'Yes',
		'beds'  => get_post_meta( $pid, 'rem_property_bedrooms', true ),
		'baths' => get_post_meta( $pid, 'rem_property_bathrooms', true ),
		'area'  => get_post_meta( $pid, 'rem_property_area', true ),
		'city'  => get_post_meta( $pid, 'rem_property_city', true ),
		'addr'  => get_post_meta( $pid, 'rem_property_address', true ),
		'img'   => get_the_post_thumbnail_url( $pid, 'medium_large' ),
	);
}

/* Render one Sayban design card (shared by listings + featured). */
function sayban_card_html( $it ) {
	$curr    = sayban_currency();
	$is_rent = $it['cat'] === 'rent';
	$loc     = trim( implode( ' · ', array_filter( array( $it['addr'] ? ucwords( $it['addr'] ) : '', $it['city'] ? ucwords( $it['city'] ) : '' ) ) ) );
	ob_start(); ?>
	<article class="sb-card">
	  <a class="sb-card-img" href="<?php echo esc_url( $it['link'] ); ?>" <?php if ( $it['img'] ) echo 'style="background-image:url(\'' . esc_url( $it['img'] ) . '\')"'; ?>>
		<span class="sb-card-badges">
		  <span class="sb-b <?php echo $is_rent ? 'sb-b-rent' : 'sb-b-sale'; ?>"><?php echo $is_rent ? 'For Rent' : 'For Sale'; ?></span>
		  <?php if ( $it['feat'] ) : ?><span class="sb-b sb-b-feat">Featured</span><?php endif; ?>
		</span>
		<span class="sb-card-fav" aria-hidden="true">&#9825;</span>
	  </a>
	  <a class="sb-card-body" href="<?php echo esc_url( $it['link'] ); ?>">
		<div class="sb-card-price"><span class="sb-cur"><?php echo esc_html( $curr ); ?></span><?php echo esc_html( $it['price'] ); ?><?php echo $is_rent ? '<span class="sb-per">/mo</span>' : ''; ?></div>
		<div class="sb-card-title"><?php echo esc_html( $it['title'] ); ?></div>
		<?php if ( $loc ) : ?><div class="sb-card-loc"><?php echo esc_html( $loc ); ?></div><?php endif; ?>
		<div class="sb-card-specs">
		  <?php if ( '' !== (string) $it['beds'] ) : ?><span><?php echo esc_html( $it['beds'] ); ?> Beds</span><?php endif; ?>
		  <?php if ( '' !== (string) $it['baths'] ) : ?><span><?php echo esc_html( $it['baths'] ); ?> Baths</span><?php endif; ?>
		  <?php if ( $it['area'] ) : ?><span><?php echo esc_html( $it['area'] ); ?></span><?php endif; ?>
		</div>
	  </a>
	</article>
	<?php return ob_get_clean();
}

/* =========================================================================
   [sayban_finder]  — hero property finder (tabs + fields → /properties/)
   ========================================================================= */
function sayban_finder_shortcode( $atts ) {
	$target = sayban_page_url( 'listings' );
	$counts = wp_count_posts( 'rem_property' );
	$active = $counts && isset( $counts->publish ) ? (int) $counts->publish : 0;
	$types  = array( 'House', 'Flat / Apartment', 'Upper / Lower Portion', 'Plot / Land', 'Office / Shop' );
	$prices = array( 10000000 => 'Under 1 Crore', 20000000 => 'Under 2 Crore', 30000000 => 'Under 3 Crore', 50000000 => 'Under 5 Crore' );
	ob_start(); ?>
	<div class="sayban-finder">
	  <div class="sf-panel">
	  <div class="sf-tabs" role="tablist">
		<button type="button" class="sf-tab on" data-purpose="buy">Buy</button>
		<button type="button" class="sf-tab" data-purpose="rent">Rent</button>
		<button type="button" class="sf-tab" data-purpose="plot">Plots</button>
		<button type="button" class="sf-tab" data-purpose="">Requirements</button>
	  </div>
	  <form class="sf-form" method="get" action="<?php echo esc_url( $target ); ?>">
		<input type="hidden" name="purpose" value="buy" class="sf-purpose">
		<div class="sf-fields">
		  <div class="sf-field">
			<label>City</label>
			<div class="sf-select"><select name="city"><option value="">All Cities</option><option value="lahore">Lahore</option></select></div>
		  </div>
		  <div class="sf-field">
			<label>Area / Society</label>
			<input class="sf-input" type="text" name="area" placeholder="e.g. DHA Phase 6, Bahria Town…">
		  </div>
		  <div class="sf-field">
			<label>Property Type</label>
			<div class="sf-select"><select name="type[]" class="sf-type"><option value="">-- Property Type --</option><?php foreach ( $types as $t ) echo '<option value="' . esc_attr( $t ) . '">' . esc_html( $t ) . '</option>'; ?></select></div>
		  </div>
		  <div class="sf-field">
			<label>Price (PKR)</label>
			<div class="sf-select"><select name="max"><option value="">Any</option><?php foreach ( $prices as $v => $l ) echo '<option value="' . $v . '">' . esc_html( $l ) . '</option>'; ?></select></div>
		  </div>
		  <button type="submit" class="sf-search">Search</button>
		</div>
	  </form>
	  </div><!-- .sf-panel -->
	  <div class="sf-trust">
		<span>&#10003; <?php echo (int) $active; ?> active listing<?php echo $active === 1 ? '' : 's'; ?></span>
		<span>&#10003; Direct owner contact</span>
		<span>&#10003; Zero commission</span>
	  </div>
	</div>
	<script>
	(function () {
	  var root = document.currentScript.previousElementSibling;
	  if ( !root || !root.classList.contains('sayban-finder') ) { root = document.querySelector('.sayban-finder'); }
	  if ( !root ) return;
	  var tabs = root.querySelectorAll('.sf-tab');
	  var purposeEl = root.querySelector('.sf-purpose');
	  var typeEl = root.querySelector('.sf-type');
	  tabs.forEach(function (tab) {
		tab.addEventListener('click', function () {
		  tabs.forEach(function (t) { t.classList.remove('on'); });
		  tab.classList.add('on');
		  if ( tab.hasAttribute('data-purpose') ) { purposeEl.value = tab.getAttribute('data-purpose'); }
		  if ( tab.hasAttribute('data-type') && typeEl ) { typeEl.value = tab.getAttribute('data-type'); purposeEl.value = ''; }
		  else if ( typeEl && tab.hasAttribute('data-purpose') ) { typeEl.value = ''; }
		});
	  });
	})();
	</script>
	<?php return ob_get_clean();
}
add_shortcode( 'sayban_finder', 'sayban_finder_shortcode' );

/* =========================================================================
   [sayban_featured count="4"]  — featured properties grid (design cards)
   ========================================================================= */
function sayban_featured_shortcode( $atts ) {
	$a = shortcode_atts( array( 'count' => 4, 'featured' => 'yes' ), $atts, 'sayban_featured' );
	$args = array( 'post_type' => 'rem_property', 'posts_per_page' => (int) $a['count'], 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true );
	if ( $a['featured'] === 'yes' ) {
		$args['meta_query'] = array( array( 'key' => 'rem_property_featured', 'value' => 'Yes' ) );
	}
	$q = new WP_Query( $args );
	if ( ! $q->have_posts() ) { return ''; }
	ob_start();
	echo '<div class="sb-featured"><div class="sb-featured-grid">';
	while ( $q->have_posts() ) { $q->the_post(); echo sayban_card_html( sayban_listing_data( get_the_ID() ) ); }
	wp_reset_postdata();
	echo '</div></div>';
	return ob_get_clean();
}
add_shortcode( 'sayban_featured', 'sayban_featured_shortcode' );

/* =========================================================================
   [sayban_cities]  — "Browse by City" cards (image + bottom gradient + count)
   Images come from the option `sayban_city_images` (slug => URL).
   ========================================================================= */
function sayban_cities_shortcode( $atts ) {
	$imgs   = get_option( 'sayban_city_images', array() );
	$cities = array(
		'karachi'    => 'Karachi',
		'lahore'     => 'Lahore',
		'islamabad'  => 'Islamabad',
		'rawalpindi' => 'Rawalpindi',
		'faisalabad' => 'Faisalabad',
	);
	/* real published listing counts per city (case-insensitive) */
	global $wpdb;
	$rows = $wpdb->get_results(
		"SELECT LOWER(pm.meta_value) city, COUNT(*) c
		 FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE pm.meta_key = 'rem_property_city' AND p.post_type = 'rem_property' AND p.post_status = 'publish'
		 GROUP BY LOWER(pm.meta_value)", ARRAY_A
	);
	$counts = array();
	foreach ( (array) $rows as $r ) { $counts[ $r['city'] ] = (int) $r['c']; }

	$listings = sayban_page_url( 'listings' );
	ob_start();
	echo '<div class="sb-cities">';
	foreach ( $cities as $slug => $name ) {
		$img = isset( $imgs[ $slug ] ) ? $imgs[ $slug ] : '';
		$n   = isset( $counts[ $slug ] ) ? $counts[ $slug ] : 0;
		$sub = $n > 0 ? ( number_format_i18n( $n ) . ' listing' . ( $n === 1 ? '' : 's' ) ) : 'Browse listings';
		$url = add_query_arg( 'city', $slug, $listings );
		echo '<a class="sb-city" href="' . esc_url( $url ) . '"' . ( $img ? ' style="background-image:url(\'' . esc_url( $img ) . '\')"' : '' ) . '>'
			. '<span class="sb-city-grad" aria-hidden="true"></span>'
			. '<span class="sb-city-meta"><b>' . esc_html( $name ) . '</b><span>' . esc_html( $sub ) . '</span></span>'
			. '</a>';
	}
	echo '</div>';
	return ob_get_clean();
}
add_shortcode( 'sayban_cities', 'sayban_cities_shortcode' );
