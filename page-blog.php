<?php
/**
 * Sayban.pk — Blog index (/blog/). Matches the Claude Design language.
 * Editorial header, category filter chips, featured lead post + card grid,
 * pagination. Own WP_Query. Assigned to the "blog" page via functions.php.
 */
defined( 'ABSPATH' ) || exit;

get_header();

/* ---------- current filter + paging ---------- */
$f_cat = isset( $_GET['cat'] ) ? sanitize_title( $_GET['cat'] ) : '';
$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ), isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1 );
$per   = 9;

$base_url = get_permalink();

/* Categories that actually have posts (skip empty + generic buckets). */
$all_cats = array_filter(
	get_categories( array( 'hide_empty' => true, 'orderby' => 'name' ) ),
	function ( $c ) { return ! in_array( strtolower( $c->slug ), array( 'blog', 'uncategorized' ), true ); }
);

/* ---------- query ---------- */
$args = array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => $per,
	'paged'          => $paged,
	'ignore_sticky_posts' => true,
);
if ( $f_cat ) {
	$args['category_name'] = $f_cat;
}
$q = new WP_Query( $args );

/* On the unfiltered first page, the newest post becomes the large lead card —
   but only when enough posts remain to fill a full grid row below it (avoids a
   lopsided single-lead + 2-card layout). */
$use_lead = ( ! $f_cat && $paged === 1 && $q->post_count >= 4 );

$cat_url = function ( $slug ) use ( $base_url ) {
	return $slug ? add_query_arg( 'cat', $slug, $base_url ) : $base_url;
};
$active_cat_obj = $f_cat ? get_category_by_slug( $f_cat ) : null;
?>

<div class="sb-blog">

  <!-- ============ header band ============ -->
  <header class="sb-blog-head">
	<div class="sb-blog-head-inner">
	  <nav class="sb-breadcrumb">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><b>Blog</b>
	  </nav>
	  <span class="sb-kicker">The Sayban Journal</span>
	  <h1 class="sb-blog-h1">Property Guides &amp; Market Insight</h1>
	  <p class="sb-blog-sub">Plain-English guides to buying, renting and investing in Pakistan — marla conversions,
		document verification, and the numbers behind the market.</p>
	</div>
  </header>

  <div class="sb-blog-wrap">

	<!-- ============ category filter ============ -->
	<nav class="sb-blog-filter" aria-label="Blog categories">
	  <a class="sb-fchip <?php echo $f_cat === '' ? 'on' : ''; ?>" href="<?php echo esc_url( $cat_url( '' ) ); ?>">All</a>
	  <?php foreach ( $all_cats as $c ) : ?>
		<a class="sb-fchip <?php echo $f_cat === $c->slug ? 'on' : ''; ?>" href="<?php echo esc_url( $cat_url( $c->slug ) ); ?>"><?php echo esc_html( $c->name ); ?></a>
	  <?php endforeach; ?>
	</nav>

	<?php if ( $active_cat_obj ) : ?>
	  <div class="sb-blog-filterhead">
		<h2 class="sb-blog-filtertitle"><?php echo esc_html( $active_cat_obj->name ); ?></h2>
		<span class="sb-blog-filtercount"><?php echo (int) $q->found_posts; ?> article<?php echo $q->found_posts === 1 ? '' : 's'; ?></span>
	  </div>
	<?php endif; ?>

	<?php if ( ! $q->have_posts() ) : ?>

	  <div class="sb-blog-empty">
		<p>No articles here yet.</p>
		<a class="sb-btn sb-btn-ghost" href="<?php echo esc_url( $base_url ); ?>">View all articles</a>
	  </div>

	<?php else : ?>

	  <?php
	  $posts = $q->posts;
	  if ( $use_lead && $posts ) {
		  $lead = array_shift( $posts );
		  echo '<div class="sb-blog-lead-wrap">' . sayban_blog_card_html( $lead, 'lead' ) . '</div>';
	  }
	  if ( $posts ) {
		  echo '<div class="sb-blog-grid">';
		  foreach ( $posts as $p ) { echo sayban_blog_card_html( $p, 'grid' ); }
		  echo '</div>';
	  }
	  ?>

	  <?php
	  $links = paginate_links( array(
		  'base'      => trailingslashit( $base_url ) . '%_%',
		  'format'    => 'page/%#%/',
		  'current'   => $paged,
		  'total'     => $q->max_num_pages,
		  'add_args'  => $f_cat ? array( 'cat' => $f_cat ) : array(),
		  'prev_text' => '&larr; Prev',
		  'next_text' => 'Next &rarr;',
		  'type'      => 'list',
	  ) );
	  if ( $links ) { echo '<div class="sb-blog-pagination">' . $links . '</div>'; }
	  ?>

	<?php endif; wp_reset_postdata(); ?>

  </div><!-- .sb-blog-wrap -->

  <!-- ============ closing CTA (charcoal band) ============ -->
  <section class="sb-blog-cta">
	<div class="sb-blog-cta-inner">
	  <span class="sb-kicker sb-kicker-light">Ready when you are</span>
	  <h2 class="sb-blog-cta-title">Find your place in Pakistan.</h2>
	  <p class="sb-blog-cta-sub">Thousands of verified homes, plots and rentals — direct owner contact, zero commission.</p>
	  <div class="sb-blog-cta-btns">
		<a class="sb-btn sb-btn-gold" href="<?php echo esc_url( sayban_page_url( 'listings' ) ); ?>">Browse Listings</a>
		<a class="sb-btn sb-btn-lightghost" href="<?php echo esc_url( sayban_page_url( 'create' ) ); ?>">Post a Property</a>
	  </div>
	</div>
  </section>

</div><!-- .sb-blog -->

<?php
get_footer();
