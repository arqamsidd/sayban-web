<?php
/**
 * Sayban.pk — custom single property template.
 * Reproduces the Claude Design mockup (site/property.html) with real REM data.
 * Loaded via the template_include filter in functions.php (beats REM's prio 99).
 */
defined( 'ABSPATH' ) || exit;

get_header();

global $post;
while ( have_posts() ) :
	the_post();

	$pid = get_the_ID();
	$mv  = function ( $k ) use ( $pid ) { return get_post_meta( $pid, $k, true ); };

	$price    = trim( (string) $mv( 'rem_property_price' ) );
	$before   = trim( (string) $mv( 'rem_before_price_text' ) );
	$after    = trim( (string) $mv( 'rem_after_price_text' ) );
	$type     = $mv( 'rem_property_type' );
	$purpose  = $mv( 'rem_property_purpose' );
	$status   = $mv( 'rem_property_status' );
	$area     = $mv( 'rem_property_area' );
	$city     = $mv( 'rem_property_city' );
	$address  = $mv( 'rem_property_address' );
	$featured = $mv( 'rem_property_featured' );
	$beds     = $mv( 'rem_property_bedrooms' );
	$baths    = $mv( 'rem_property_bathrooms' );
	$lat      = $mv( 'rem_property_latitude' );
	$lng      = $mv( 'rem_property_longitude' );
	$cbs      = $mv( 'rem_property_detail_cbs' );
	$imgs     = $mv( 'rem_property_images' );
	$thumb    = get_post_thumbnail_id( $pid );

	$curr = function_exists( 'rem_get_currency_symbol' ) ? rem_get_currency_symbol() : 'PKR ';

	// Purpose → readable badge.
	$p_low = strtolower( (string) $purpose );
	$purpose_label = $purpose ? ( ( 'buy' === $p_low || 'sale' === $p_low ) ? 'For Sale' : 'For ' . ucfirst( $p_low ) ) : '';

	$city_label = $city ? ucwords( str_replace( array( '-', '_' ), ' ', $city ) ) : '';
	$loc_line   = trim( implode( ' · ', array_filter( array( $address ? ucwords( $address ) : '', $city_label ) ) ) );

	// Gallery ids: main first, then the rest.
	$ids = array();
	if ( is_array( $imgs ) ) {
		foreach ( $imgs as $iid ) {
			$iid = (int) $iid;
			if ( $iid ) { $ids[] = $iid; }
		}
	}
	if ( $thumb ) {
		$ids = array_merge( array( (int) $thumb ), array_values( array_diff( $ids, array( (int) $thumb ) ) ) );
	}
	$ids     = array_values( array_unique( $ids ) );
	$main_id = isset( $ids[0] ) ? $ids[0] : 0;
	$thumbs  = array_slice( $ids, 1, 4 );
	$extra   = max( 0, count( $ids ) - 5 );

	// Amenities (keys toggled "on").
	$amenities = array();
	if ( is_array( $cbs ) ) {
		foreach ( $cbs as $name => $on ) {
			if ( 'on' === $on || true === $on || '1' === (string) $on ) { $amenities[] = $name; }
		}
	}

	$img_url = function ( $id, $size ) { return $id ? wp_get_attachment_image_url( $id, $size ) : ''; };
	?>

	<div class="sb-single">
	  <div class="sb-single-wrap">

		<nav class="sb-breadcrumb">
		  <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
		  <?php if ( $city_label ) : ?><span>/</span><span><?php echo esc_html( $city_label ); ?></span><?php endif; ?>
		  <span>/</span><b><?php the_title(); ?></b>
		</nav>

		<?php if ( $main_id ) : ?>
		<div class="sb-gallery">
		  <a class="sb-g-cell sb-g-main sb-lightbox" href="<?php echo esc_url( $img_url( $main_id, 'full' ) ); ?>" data-sbgal="1"
			 style="background-image:url('<?php echo esc_url( $img_url( $main_id, 'large' ) ); ?>')"></a>
		  <?php foreach ( $thumbs as $i => $tid ) :
			  $is_last = ( count( $thumbs ) - 1 === $i && $extra > 0 ); ?>
			  <a class="sb-g-cell sb-lightbox" href="<?php echo esc_url( $img_url( $tid, 'full' ) ); ?>" data-sbgal="1"
				 style="background-image:url('<?php echo esc_url( $img_url( $tid, 'medium_large' ) ); ?>')">
				 <?php if ( $is_last ) : ?><span class="sb-g-more">+ <?php echo (int) $extra; ?> photos</span><?php endif; ?>
			  </a>
		  <?php endforeach; ?>
		</div>
		<?php endif; ?>

		<div class="sb-detail-grid">
		  <main class="sb-detail-main">

			<div class="sb-badges">
			  <?php if ( $purpose_label ) : ?><span class="sb-badge sb-badge-sale"><?php echo esc_html( $purpose_label ); ?></span><?php endif; ?>
			  <?php if ( 'Yes' === $featured ) : ?><span class="sb-badge sb-badge-feat">Featured</span><?php endif; ?>
			  <?php if ( $status ) : ?><span class="sb-badge sb-badge-ver"><?php echo esc_html( $status ); ?></span><?php endif; ?>
			</div>

			<div class="sb-title-row">
			  <div>
				<h1 class="sb-title"><?php the_title(); ?></h1>
				<?php if ( $loc_line ) : ?><div class="sb-loc"><?php echo esc_html( $loc_line ); ?></div><?php endif; ?>
			  </div>
			  <div class="sb-price-block">
				<div class="sb-price"><?php echo esc_html( $before ); ?><span class="sb-cur"><?php echo esc_html( $curr ); ?></span><?php echo esc_html( $price . ( $after ? ' ' . $after : '' ) ); ?></div>
			  </div>
			</div>

			<div class="sb-actions">
			  <button type="button" class="sb-btn sb-btn-ghost" onclick="window.print()">Print PDF</button>
			  <a class="sb-btn sb-btn-ghost" href="#sb-inquiry">Enquire</a>
			  <?php if ( $lat && $lng ) : ?><a class="sb-btn sb-btn-ghost" href="#sb-location">View on map</a><?php endif; ?>
			</div>

			<div class="sb-facts">
			  <?php
				$facts = array(
					array( $type ? $type : '—', 'Type' ),
					array( '' !== (string) $beds ? $beds : '—', 'Bedrooms' ),
					array( '' !== (string) $baths ? $baths : '—', 'Bathrooms' ),
					array( $area ? $area : '—', 'Area' ),
					array( $purpose ? ucfirst( $p_low ) : '—', 'Purpose' ),
					array( 'SB-' . $pid, 'Property ID' ),
				);
				foreach ( $facts as $f ) : ?>
				<div class="sb-fact"><b><?php echo esc_html( $f[0] ); ?></b><span><?php echo esc_html( $f[1] ); ?></span></div>
			  <?php endforeach; ?>
			</div>

			<?php if ( trim( get_the_content() ) ) : ?>
			<h2 class="sb-h">Description</h2>
			<div class="sb-desc"><?php the_content(); ?></div>
			<?php endif; ?>

			<?php if ( $amenities ) : ?>
			<h2 class="sb-h">Amenities</h2>
			<div class="sb-amenities">
			  <?php foreach ( $amenities as $a ) : ?>
				<div class="sb-amen"><i>✓</i><?php echo esc_html( $a ); ?></div>
			  <?php endforeach; ?>
			</div>
			<?php endif; ?>

			<?php if ( $lat && $lng ) :
				$bb = sprintf( '%f,%f,%f,%f', $lng - 0.008, $lat - 0.006, $lng + 0.008, $lat + 0.006 ); ?>
			<h2 class="sb-h" id="sb-location">Location</h2>
			<div class="sb-map">
			  <iframe title="Map" loading="lazy" src="https://www.openstreetmap.org/export/embed.html?bbox=<?php echo esc_attr( $bb ); ?>&amp;layer=mapnik&amp;marker=<?php echo esc_attr( $lat . ',' . $lng ); ?>"></iframe>
			</div>
			<?php endif; ?>

		  </main>

		  <aside class="sb-rail" id="sb-inquiry">
			<div class="sb-agent-card">
			  <?php do_action( 'rem_single_property_agent', (int) $post->post_author ); ?>
			</div>
			<div class="sb-safety">
			  <b>Safety tip:</b> never pay token money before verifying documents and visiting the property.
			</div>
		  </aside>
		</div>

		<?php
		// Similar properties — prefer same category, then fill with recent listings.
		$terms   = wp_get_post_terms( $pid, 'rem_property_cat', array( 'fields' => 'ids' ) );
		$sim_ids = array();
		if ( ! is_wp_error( $terms ) && $terms ) {
			$sim_ids = get_posts( array(
				'post_type' => 'rem_property', 'posts_per_page' => 3, 'fields' => 'ids',
				'post__not_in' => array( $pid ), 'ignore_sticky_posts' => true,
				'tax_query' => array( array( 'taxonomy' => 'rem_property_cat', 'field' => 'term_id', 'terms' => $terms ) ),
			) );
		}
		if ( count( $sim_ids ) < 3 ) {
			$fill = get_posts( array(
				'post_type' => 'rem_property', 'posts_per_page' => 3, 'fields' => 'ids',
				'post__not_in' => array_merge( array( $pid ), $sim_ids ), 'ignore_sticky_posts' => true,
			) );
			$sim_ids = array_slice( array_merge( $sim_ids, $fill ), 0, 3 );
		}
		$sim = new WP_Query( array(
			'post_type' => 'rem_property', 'post__in' => $sim_ids ? $sim_ids : array( 0 ),
			'orderby' => 'post__in', 'posts_per_page' => 3, 'ignore_sticky_posts' => true, 'no_found_rows' => true,
		) );
		if ( $sim->have_posts() ) : ?>
		<section class="sb-similar">
		  <div class="sb-sec-head">
			<span class="sb-kicker">More like this</span>
			<h2 class="sb-h sb-h-serif">Similar Properties<?php echo $city_label ? ' in ' . esc_html( $city_label ) : ''; ?></h2>
		  </div>
		  <div class="sb-sim-grid">
			<?php while ( $sim->have_posts() ) : $sim->the_post();
				$sid  = get_the_ID();
				$sp   = trim( (string) get_post_meta( $sid, 'rem_property_price', true ) );
				$sbd  = get_post_meta( $sid, 'rem_property_bedrooms', true );
				$sba  = get_post_meta( $sid, 'rem_property_bathrooms', true );
				$sar  = get_post_meta( $sid, 'rem_property_area', true );
				$sct  = get_post_meta( $sid, 'rem_property_city', true );
				$simg = get_the_post_thumbnail_url( $sid, 'medium_large' );
				?>
				<a class="sb-pcard" href="<?php the_permalink(); ?>">
				  <span class="sb-pcard-img" style="background-image:url('<?php echo esc_url( $simg ); ?>')"></span>
				  <span class="sb-pcard-body">
					<span class="sb-pcard-price"><span class="sb-cur"><?php echo esc_html( $curr ); ?></span><?php echo esc_html( $sp ); ?></span>
					<span class="sb-pcard-title"><?php the_title(); ?></span>
					<?php if ( $sct ) : ?><span class="sb-pcard-loc"><?php echo esc_html( ucwords( str_replace( array( '-', '_' ), ' ', $sct ) ) ); ?></span><?php endif; ?>
					<span class="sb-pcard-specs">
					  <?php if ( '' !== (string) $sbd ) : ?><span><?php echo esc_html( $sbd ); ?> Beds</span><?php endif; ?>
					  <?php if ( '' !== (string) $sba ) : ?><span><?php echo esc_html( $sba ); ?> Baths</span><?php endif; ?>
					  <?php if ( $sar ) : ?><span><?php echo esc_html( $sar ); ?></span><?php endif; ?>
					</span>
				  </span>
				</a>
			<?php endwhile; ?>
		  </div>
		</section>
		<?php endif; wp_reset_postdata(); ?>

	  </div>
	</div>

	<?php /* Gallery lightbox (prev/next) handled by the shared assets/sayban-lightbox.js, enqueued on single listings. */ ?>

	<?php
endwhile;

get_footer();
