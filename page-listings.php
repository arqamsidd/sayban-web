<?php
/**
 * Sayban.pk — listings / search page (matches site/listings.html).
 * Custom design grid: own WP_Query + GET filters (Buy/Rent via rem_property_cat,
 * type, beds, price), design cards, sort + grid/list, and a REM property map.
 * Assigned to the "Properties" page via functions.php template_include.
 */
defined( 'ABSPATH' ) || exit;

get_header();

/* ---------- price text <-> number helpers (REM stores price as text) ---------- */
function sb_price_to_num( $txt ) {
	$txt = strtolower( trim( (string) $txt ) );
	if ( $txt === '' ) { return 0; }
	if ( ! preg_match( '/([0-9]+(?:\.[0-9]+)?)/', $txt, $m ) ) { return 0; }
	$n = (float) $m[1];
	if ( strpos( $txt, 'crore' ) !== false || strpos( $txt, 'cr' ) !== false ) { return (int) round( $n * 10000000 ); }
	if ( strpos( $txt, 'lakh' ) !== false || strpos( $txt, 'lac' ) !== false ) { return (int) round( $n * 100000 ); }
	if ( strpos( $txt, 'thousand' ) !== false || strpos( $txt, 'k' ) !== false ) { return (int) round( $n * 1000 ); }
	return (int) round( $n );
}

/* ---------- read filters from the query string ---------- */
$f_purpose = isset( $_GET['purpose'] ) ? sanitize_key( $_GET['purpose'] ) : '';   // '', buy, rent
$f_types   = isset( $_GET['type'] ) ? array_map( 'sanitize_text_field', (array) $_GET['type'] ) : array();
$f_beds    = isset( $_GET['beds'] ) ? sanitize_text_field( $_GET['beds'] ) : '';
$f_min     = isset( $_GET['min'] ) ? (int) $_GET['min'] : 0;
$f_max     = isset( $_GET['max'] ) ? (int) $_GET['max'] : 0;
$f_city    = isset( $_GET['city'] ) ? sanitize_text_field( $_GET['city'] ) : '';
$f_sort    = isset( $_GET['sort'] ) ? sanitize_key( $_GET['sort'] ) : 'date';
$view      = ( isset( $_GET['view'] ) && $_GET['view'] === 'list' ) ? 'list' : 'grid';

/* ---------- query ---------- */
$args = array( 'post_type' => 'rem_property', 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' );
if ( $f_purpose ) {
	$args['tax_query'] = array( array( 'taxonomy' => 'rem_property_cat', 'field' => 'slug', 'terms' => $f_purpose ) );
}
$meta = array( 'relation' => 'AND' );
if ( $f_types )  { $meta[] = array( 'key' => 'rem_property_type', 'value' => $f_types, 'compare' => 'IN' ); }
if ( $f_city )   { $meta[] = array( 'key' => 'rem_property_city', 'value' => $f_city, 'compare' => '=' ); }
if ( $f_beds && $f_beds !== 'any' ) {
	if ( $f_beds === '5plus' ) { $meta[] = array( 'key' => 'rem_property_bedrooms', 'value' => 5, 'compare' => '>=', 'type' => 'NUMERIC' ); }
	else { $meta[] = array( 'key' => 'rem_property_bedrooms', 'value' => $f_beds, 'compare' => '=' ); }
}
if ( count( $meta ) > 1 ) { $args['meta_query'] = $meta; }

$q = new WP_Query( $args );
$items = array();
while ( $q->have_posts() ) {
	$q->the_post();
	$pid  = get_the_ID();
	$pnum = sb_price_to_num( get_post_meta( $pid, 'rem_property_price', true ) );
	if ( $f_min && $pnum && $pnum < $f_min ) { continue; }
	if ( $f_max && $pnum && $pnum > $f_max ) { continue; }
	$cats = wp_get_post_terms( $pid, 'rem_property_cat', array( 'fields' => 'slugs' ) );
	$items[] = array(
		'id'    => $pid,
		'pnum'  => $pnum,
		'link'  => get_permalink( $pid ),
		'title' => get_the_title(),
		'price' => trim( (string) get_post_meta( $pid, 'rem_property_price', true ) ),
		'cat'   => is_array( $cats ) && $cats ? $cats[0] : '',
		'feat'  => get_post_meta( $pid, 'rem_property_featured', true ) === 'Yes',
		'type'  => get_post_meta( $pid, 'rem_property_type', true ),
		'beds'  => get_post_meta( $pid, 'rem_property_bedrooms', true ),
		'baths' => get_post_meta( $pid, 'rem_property_bathrooms', true ),
		'area'  => get_post_meta( $pid, 'rem_property_area', true ),
		'city'  => get_post_meta( $pid, 'rem_property_city', true ),
		'addr'  => get_post_meta( $pid, 'rem_property_address', true ),
		'img'   => get_the_post_thumbnail_url( $pid, 'medium_large' ),
	);
}
wp_reset_postdata();
if ( $f_sort === 'price_desc' ) { usort( $items, function ( $a, $b ) { return $b['pnum'] - $a['pnum']; } ); }
elseif ( $f_sort === 'price_asc' ) { usort( $items, function ( $a, $b ) { return $a['pnum'] - $b['pnum']; } ); }
$count = count( $items );

$curr    = function_exists( 'rem_get_currency_symbol' ) ? rem_get_currency_symbol() : 'PKR ';
$purpose_word = $f_purpose === 'rent' ? 'for Rent' : ( $f_purpose === 'buy' ? 'for Sale' : '' );
$heading = trim( 'Houses ' . $purpose_word . ( $f_city ? ' in ' . ucwords( $f_city ) : ' in Lahore' ) );

/* helper to build a filter URL preserving other params */
function sb_url( $overrides = array() ) {
	$base = get_permalink();
	$q = array_merge( $_GET, $overrides );
	foreach ( $q as $k => $v ) { if ( $v === '' || $v === null ) { unset( $q[$k] ); } }
	return $q ? $base . '?' . http_build_query( $q ) : $base;
}

$types_list = array( 'House', 'Flat / Apartment', 'Upper / Lower Portion', 'Plot / Land', 'Office / Shop' );
$price_opts = array( 5000000 => '50 Lakh', 10000000 => '1 Crore', 15000000 => '1.5 Crore', 20000000 => '2 Crore', 30000000 => '3 Crore', 50000000 => '5 Crore' );
?>

<div class="sb-listings-page">
  <div class="sb-listings-wrap">

	<nav class="sb-breadcrumb">
	  <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span>
	  <span>Lahore</span><span>/</span><b><?php echo esc_html( $heading ); ?></b>
	</nav>

	<div class="sb-map-strip">
	  <span class="sb-map-strip-txt">◈&nbsp;&nbsp;Map view — see these <?php echo (int) $count; ?> result<?php echo $count === 1 ? '' : 's'; ?> on a map of Lahore</span>
	  <button type="button" class="sb-btn sb-btn-teal" id="sb-map-toggle">Show Map</button>
	</div>
	<div class="sb-map-panel" id="sb-map-panel" hidden>
	  <?php echo do_shortcode( '[rem_maps]' ); ?>
	</div>

	<div class="sb-listings-grid">

	  <!-- ================= FILTER SIDEBAR ================= -->
	  <form class="sb-filters" method="get" action="<?php echo esc_url( get_permalink() ); ?>">
		<div class="sb-fgroup">
		  <h6>Purpose</h6>
		  <div class="sb-seg">
			<label class="<?php echo $f_purpose === '' ? 'on' : ''; ?>"><input type="radio" name="purpose" value="" <?php checked( $f_purpose, '' ); ?> onchange="this.form.submit()">All</label>
			<label class="<?php echo $f_purpose === 'buy' ? 'on' : ''; ?>"><input type="radio" name="purpose" value="buy" <?php checked( $f_purpose, 'buy' ); ?> onchange="this.form.submit()">Buy</label>
			<label class="<?php echo $f_purpose === 'rent' ? 'on' : ''; ?>"><input type="radio" name="purpose" value="rent" <?php checked( $f_purpose, 'rent' ); ?> onchange="this.form.submit()">Rent</label>
		  </div>
		</div>

		<div class="sb-fgroup">
		  <h6>City &amp; Area</h6>
		  <div class="sb-select-wrap">
			<select name="city" onchange="this.form.submit()">
			  <option value="">All Cities</option>
			  <option value="lahore" <?php selected( $f_city, 'lahore' ); ?>>Lahore</option>
			</select>
		  </div>
		  <input class="sb-input" type="text" name="area" placeholder="Search area / society…" value="<?php echo esc_attr( $_GET['area'] ?? '' ); ?>">
		</div>

		<div class="sb-fgroup">
		  <h6>Property Type</h6>
		  <?php foreach ( $types_list as $t ) : ?>
			<label class="sb-check">
			  <input type="checkbox" name="type[]" value="<?php echo esc_attr( $t ); ?>" <?php checked( in_array( $t, $f_types, true ) ); ?> onchange="this.form.submit()">
			  <span class="sb-check-box"></span><?php echo esc_html( $t ); ?>
			</label>
		  <?php endforeach; ?>
		</div>

		<div class="sb-fgroup">
		  <h6>Price (PKR)</h6>
		  <div class="sb-range-track"><span class="sb-range-fill"></span></div>
		  <div class="sb-price-row">
			<select name="min" onchange="this.form.submit()">
			  <option value="">Min</option>
			  <?php foreach ( $price_opts as $v => $l ) : ?><option value="<?php echo $v; ?>" <?php selected( $f_min, $v ); ?>><?php echo esc_html( $l ); ?></option><?php endforeach; ?>
			</select>
			<select name="max" onchange="this.form.submit()">
			  <option value="">Max</option>
			  <?php foreach ( $price_opts as $v => $l ) : ?><option value="<?php echo $v; ?>" <?php selected( $f_max, $v ); ?>><?php echo esc_html( $l ); ?></option><?php endforeach; ?>
			</select>
		  </div>
		</div>

		<div class="sb-fgroup">
		  <h6>Bedrooms</h6>
		  <div class="sb-pills">
			<?php foreach ( array( 'any' => 'Any', '3' => '3', '4' => '4', '5plus' => '5+' ) as $val => $lab ) :
				$on = ( $f_beds === $val || ( $val === 'any' && $f_beds === '' ) ); ?>
			  <label class="sb-pill <?php echo $on ? 'on' : ''; ?>"><input type="radio" name="beds" value="<?php echo esc_attr( $val ); ?>" <?php checked( $on ); ?> onchange="this.form.submit()"><?php echo esc_html( $lab ); ?></label>
			<?php endforeach; ?>
		  </div>
		</div>

		<div class="sb-fgroup sb-fbuttons">
		  <button type="submit" class="sb-btn sb-btn-gold">Apply Filters</button>
		  <a class="sb-btn sb-btn-ghost" href="<?php echo esc_url( get_permalink() ); ?>">Reset</a>
		</div>
	  </form>

	  <!-- ================= RESULTS ================= -->
	  <div class="sb-results">
		<div class="sb-sortbar">
		  <div>
			<h1 class="sb-results-title"><?php echo esc_html( $heading ); ?></h1>
			<div class="sb-results-count"><?php echo (int) $count; ?> result<?php echo $count === 1 ? '' : 's'; ?></div>
		  </div>
		  <div class="sb-sort-tools">
			<div class="sb-select-wrap">
			  <select onchange="location.href=this.value">
				<option value="<?php echo esc_url( sb_url( array( 'sort' => 'date' ) ) ); ?>" <?php selected( $f_sort, 'date' ); ?>>Newest first</option>
				<option value="<?php echo esc_url( sb_url( array( 'sort' => 'price_desc' ) ) ); ?>" <?php selected( $f_sort, 'price_desc' ); ?>>Price: High to Low</option>
				<option value="<?php echo esc_url( sb_url( array( 'sort' => 'price_asc' ) ) ); ?>" <?php selected( $f_sort, 'price_asc' ); ?>>Price: Low to High</option>
			  </select>
			</div>
			<div class="sb-view-toggle">
			  <a class="<?php echo $view === 'grid' ? 'on' : ''; ?>" href="<?php echo esc_url( sb_url( array( 'view' => 'grid' ) ) ); ?>">Grid</a>
			  <a class="<?php echo $view === 'list' ? 'on' : ''; ?>" href="<?php echo esc_url( sb_url( array( 'view' => 'list' ) ) ); ?>">List</a>
			</div>
		  </div>
		</div>

		<?php if ( ! $count ) : ?>
		  <div class="sb-noresults">No properties match these filters. <a href="<?php echo esc_url( get_permalink() ); ?>">Reset filters</a>.</div>
		<?php else : ?>
		<div class="sb-cards sb-cards-<?php echo esc_attr( $view ); ?>">
		  <?php foreach ( $items as $it ) :
			  $is_rent = $it['cat'] === 'rent';
			  $loc = trim( implode( ' · ', array_filter( array( $it['addr'] ? ucwords( $it['addr'] ) : '', $it['city'] ? ucwords( $it['city'] ) : '' ) ) ) ); ?>
			<article class="sb-card">
			  <a class="sb-card-img" href="<?php echo esc_url( $it['link'] ); ?>" <?php if ( $it['img'] ) echo 'style="background-image:url(\'' . esc_url( $it['img'] ) . '\')"'; ?>>
				<span class="sb-card-badges">
				  <span class="sb-b <?php echo $is_rent ? 'sb-b-rent' : 'sb-b-sale'; ?>"><?php echo $is_rent ? 'For Rent' : 'For Sale'; ?></span>
				  <?php if ( $it['feat'] ) : ?><span class="sb-b sb-b-feat">Featured</span><?php endif; ?>
				</span>
				<button type="button" class="sb-card-fav" aria-label="Save">&#9825;</button>
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
		  <?php endforeach; ?>
		</div>
		<?php endif; ?>
	  </div>
	</div>
  </div>
</div>

<script>
(function () {
  var btn = document.getElementById('sb-map-toggle');
  var panel = document.getElementById('sb-map-panel');
  if (btn && panel) {
	btn.addEventListener('click', function () {
	  var show = panel.hasAttribute('hidden');
	  if (show) { panel.removeAttribute('hidden'); btn.textContent = 'Hide Map';
		window.dispatchEvent(new Event('resize')); // nudge Leaflet to render tiles
	  } else { panel.setAttribute('hidden', ''); btn.textContent = 'Show Map'; }
	});
  }
})();
</script>

<?php
get_footer();
