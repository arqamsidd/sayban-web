<?php
/**
 * Sayban.pk — shared blog helpers (used by page-blog.php + single.php).
 * Read-time estimate, excerpt, category chip, and the editorial post card.
 */
defined( 'ABSPATH' ) || exit;

/** Estimated read time in whole minutes (~200 wpm), min 1. */
function sayban_read_time( $post_id ) {
	$content = get_post_field( 'post_content', $post_id );
	$words   = str_word_count( wp_strip_all_tags( strip_shortcodes( $content ) ) );
	return max( 1, (int) round( $words / 200 ) );
}

/** A short, tag-free excerpt for cards (falls back to trimmed content). */
function sayban_blog_excerpt( $post, $words = 22 ) {
	$post = get_post( $post );
	if ( ! $post ) { return ''; }
	$raw = has_excerpt( $post ) ? $post->post_excerpt : $post->post_content;
	$raw = wp_strip_all_tags( strip_shortcodes( $raw ) );
	return wp_trim_words( $raw, $words, '…' );
}

/** The listing's primary category term (first assigned), or null. */
function sayban_primary_category( $post_id ) {
	$cats = get_the_category( $post_id );
	if ( empty( $cats ) ) { return null; }
	// Prefer a specific topic over the generic "Blog" bucket.
	foreach ( $cats as $c ) {
		if ( strtolower( $c->slug ) !== 'blog' && strtolower( $c->slug ) !== 'uncategorized' ) { return $c; }
	}
	return $cats[0];
}

/** Small category pill markup. */
function sayban_cat_chip( $post_id ) {
	$cat = sayban_primary_category( $post_id );
	if ( ! $cat ) { return ''; }
	return '<a class="sb-chip" href="' . esc_url( get_category_link( $cat->term_id ) ) . '">' . esc_html( $cat->name ) . '</a>';
}

/**
 * One editorial blog card.
 * $variant: 'grid' (default) or 'lead' (large horizontal feature).
 */
function sayban_blog_card_html( $post, $variant = 'grid' ) {
	$post = get_post( $post );
	if ( ! $post ) { return ''; }
	$pid   = $post->ID;
	$link  = get_permalink( $pid );
	$img   = get_the_post_thumbnail_url( $pid, $variant === 'lead' ? 'large' : 'medium_large' );
	$cat   = sayban_primary_category( $pid );
	$rt    = sayban_read_time( $pid );
	$date  = get_the_date( 'M j, Y', $pid );
	$excerpt = sayban_blog_excerpt( $post, $variant === 'lead' ? 34 : 20 );

	ob_start(); ?>
	<article class="sb-post <?php echo $variant === 'lead' ? 'sb-post-lead' : ''; ?>">
	  <a class="sb-post-img" href="<?php echo esc_url( $link ); ?>" <?php if ( $img ) echo 'style="background-image:url(\'' . esc_url( $img ) . '\')"'; ?>>
		<?php if ( ! $img ) : ?><span class="sb-post-img-fallback" aria-hidden="true">S</span><?php endif; ?>
	  </a>
	  <div class="sb-post-body">
		<div class="sb-post-meta">
		  <?php if ( $cat ) : ?><span class="sb-chip"><?php echo esc_html( $cat->name ); ?></span><?php endif; ?>
		  <span class="sb-post-dot">·</span>
		  <span><?php echo esc_html( $rt ); ?> min read</span>
		</div>
		<h3 class="sb-post-title"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( get_the_title( $pid ) ); ?></a></h3>
		<?php if ( $excerpt ) : ?><p class="sb-post-excerpt"><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
		<div class="sb-post-foot">
		  <span class="sb-post-date"><?php echo esc_html( $date ); ?></span>
		  <a class="sb-post-more" href="<?php echo esc_url( $link ); ?>">Read article <span aria-hidden="true">&rarr;</span></a>
		</div>
	  </div>
	</article>
	<?php return ob_get_clean();
}
