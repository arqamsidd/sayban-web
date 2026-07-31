<?php
/**
 * Sayban.pk — Projects archive (/projects/). Sayban Builders' developments.
 */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="sbp-archive">
  <header class="sbp-arch-head">
	<div class="sbp-arch-inner">
	  <nav class="sbp-crumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><b>Projects</b></nav>
	  <span class="sbp-kicker">By Sayban Builders</span>
	  <h1 class="sbp-arch-title">Signature Projects</h1>
	  <p class="sbp-arch-sub">Developments built and backed by Sayban Builders — with verified documentation and easy installment plans.</p>
	</div>
  </header>

  <div class="sbp-arch-wrap">
	<?php if ( have_posts() ) : ?>
	  <div class="sbp-grid">
		<?php while ( have_posts() ) : the_post(); echo sayban_project_card_html( get_the_ID() ); endwhile; ?>
	  </div>
	  <?php
	  $pg = paginate_links( array( 'prev_text' => '&larr; Prev', 'next_text' => 'Next &rarr;', 'type' => 'list' ) );
	  if ( $pg ) { echo '<div class="sbp-arch-pager">' . $pg . '</div>'; }
	  ?>
	<?php else : ?>
	  <p class="sbp-arch-empty">No projects published yet — check back soon.</p>
	<?php endif; ?>
  </div>
</div>
<?php
get_footer();
