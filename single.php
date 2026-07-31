<?php
/**
 * Sayban.pk — single blog post (article reading page).
 * On-brand replacement for the bare parent-theme single template.
 * Only affects standard `post` type (rem_property has its own template).
 */
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) : the_post();
	$pid   = get_the_ID();
	$cat   = sayban_primary_category( $pid );
	$rt    = sayban_read_time( $pid );
	$date  = get_the_date( 'F j, Y', $pid );
	$img   = get_the_post_thumbnail_url( $pid, 'full' );
	$author_id   = (int) get_the_author_meta( 'ID' );
	$author_name = get_the_author_meta( 'display_name', $author_id );
	$author_bio  = get_the_author_meta( 'description', $author_id );
	$blog_url    = get_permalink( get_page_by_path( 'blog' ) );
	if ( ! $blog_url ) { $blog_url = home_url( '/blog/' ); }
	$share_url   = rawurlencode( get_permalink( $pid ) );
	$share_title = rawurlencode( get_the_title( $pid ) );
	?>

	<article class="sb-article">

	  <!-- ============ header ============ -->
	  <header class="sb-article-head">
		<div class="sb-article-head-inner">
		  <nav class="sb-breadcrumb">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span>
			<a href="<?php echo esc_url( $blog_url ); ?>">Blog</a><span>/</span>
			<b><?php echo esc_html( wp_trim_words( get_the_title( $pid ), 6, '…' ) ); ?></b>
		  </nav>

		  <div class="sb-article-meta">
			<?php if ( $cat ) : ?><a class="sb-chip" href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"><?php echo esc_html( $cat->name ); ?></a><?php endif; ?>
			<span class="sb-post-dot">·</span>
			<span><?php echo esc_html( $rt ); ?> min read</span>
		  </div>

		  <h1 class="sb-article-title"><?php the_title(); ?></h1>

		  <div class="sb-article-byline">
			<span class="sb-avatar"><?php echo esc_html( strtoupper( mb_substr( $author_name, 0, 1 ) ) ); ?></span>
			<span class="sb-byline-txt">
			  <b><?php echo esc_html( $author_name ); ?></b>
			  <span><?php echo esc_html( $date ); ?></span>
			</span>
		  </div>
		</div>
	  </header>

	  <?php if ( $img ) : ?>
		<div class="sb-article-hero" style="background-image:url('<?php echo esc_url( $img ); ?>')"></div>
	  <?php endif; ?>

	  <!-- ============ body ============ -->
	  <div class="sb-article-body">
		<div class="sb-article-content">
		  <?php the_content(); ?>
		  <?php wp_link_pages( array( 'before' => '<div class="sb-article-pagelinks">Pages: ', 'after' => '</div>' ) ); ?>
		</div>

		<!-- share + tags -->
		<div class="sb-article-share">
		  <span class="sb-share-label">Share</span>
		  <a class="sb-share-btn" target="_blank" rel="noopener nofollow" href="https://wa.me/?text=<?php echo $share_title; ?>%20<?php echo $share_url; ?>" aria-label="Share on WhatsApp">WhatsApp</a>
		  <a class="sb-share-btn" target="_blank" rel="noopener nofollow" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" aria-label="Share on Facebook">Facebook</a>
		  <a class="sb-share-btn" target="_blank" rel="noopener nofollow" href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" aria-label="Share on X">X</a>
		</div>

		<?php if ( $author_name ) : ?>
		<div class="sb-article-author">
		  <span class="sb-avatar sb-avatar-lg"><?php echo esc_html( strtoupper( mb_substr( $author_name, 0, 1 ) ) ); ?></span>
		  <div>
			<span class="sb-kicker">Written by</span>
			<h4><?php echo esc_html( $author_name ); ?></h4>
			<?php if ( $author_bio ) : ?><p><?php echo esc_html( $author_bio ); ?></p><?php else : ?><p>The Sayban editorial team — practical guides for buyers, renters and investors across Pakistan.</p><?php endif; ?>
		  </div>
		</div>
		<?php endif; ?>
	  </div>

	  <?php
	  /* ============ related posts ============ */
	  $rel_ids = array();
	  if ( $cat ) {
		  $rel = new WP_Query( array(
			  'post_type'      => 'post',
			  'post_status'    => 'publish',
			  'posts_per_page' => 3,
			  'post__not_in'   => array( $pid ),
			  'category__in'   => array( $cat->term_id ),
			  'no_found_rows'  => true,
		  ) );
		  foreach ( $rel->posts as $rp ) { $rel_ids[] = $rp->ID; }
		  wp_reset_postdata();
	  }
	  if ( count( $rel_ids ) < 3 ) {
		  $fill = new WP_Query( array(
			  'post_type'      => 'post',
			  'post_status'    => 'publish',
			  'posts_per_page' => 3 - count( $rel_ids ),
			  'post__not_in'   => array_merge( array( $pid ), $rel_ids ),
			  'orderby'        => 'date',
			  'order'          => 'DESC',
			  'no_found_rows'  => true,
		  ) );
		  foreach ( $fill->posts as $rp ) { $rel_ids[] = $rp->ID; }
		  wp_reset_postdata();
	  }
	  if ( $rel_ids ) : ?>
		<section class="sb-article-related">
		  <div class="sb-related-inner">
			<div class="sb-related-head">
			  <h2 class="sb-related-title">Keep reading</h2>
			  <a class="sb-post-more" href="<?php echo esc_url( $blog_url ); ?>">All articles <span aria-hidden="true">&rarr;</span></a>
			</div>
			<div class="sb-blog-grid">
			  <?php foreach ( $rel_ids as $rid ) { echo sayban_blog_card_html( $rid, 'grid' ); } ?>
			</div>
		  </div>
		</section>
	  <?php endif; ?>

	</article>

<?php endwhile; ?>

<?php
get_footer();
