<?php
/**
 * Sayban.pk — single Developer Project (e.g. Saudagran Enclave).
 * Polished developer-site layout: hero gallery, key facts, overview, features,
 * payment-plan tables (tabbed) + PDF download, location map, inquiry + WhatsApp.
 */
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) : the_post();
	$pid      = get_the_ID();
	$loc      = sayban_pf( $pid, 'location' );
	$city     = sayban_pf( $pid, 'city' );
	$status   = sayban_pf( $pid, 'status' );
	$ptype    = sayban_pf( $pid, 'ptype' );
	$possess  = sayban_pf( $pid, 'possession' );
	$price    = sayban_pf( $pid, 'price_from' );
	$area     = sayban_pf( $pid, 'area_range' );
	$tagline  = sayban_pf( $pid, 'tagline' );
	$phone    = sayban_pf( $pid, 'phone' );
	$wa       = preg_replace( '/\D/', '', sayban_pf( $pid, 'whatsapp' ) );
	$brochure = sayban_pf( $pid, 'brochure' );
	$lat      = sayban_pf( $pid, 'lat' );
	$lng      = sayban_pf( $pid, 'lng' );
	$features = sayban_project_features( $pid );
	$plans    = sayban_project_plans( $pid );
	$gallery  = sayban_project_gallery( $pid );
	$dev      = 'Sayban Builders';

	$fact_rows = array_filter( array(
		'Location'   => $loc,
		'Type'       => $ptype,
		'Unit sizes' => $area,
		'Possession' => $possess,
		'Developer'  => $dev,
		'Status'     => $status,
	) );

	$inq = isset( $_GET['inq'] ) ? sanitize_key( $_GET['inq'] ) : '';
	?>

	<div class="sbp-single">

	  <!-- ================= HERO ================= -->
	  <section class="sbp-hero">
		<?php if ( $gallery ) : $mainimg = wp_get_attachment_image_url( $gallery[0], 'full' ); ?>
		  <div class="sbp-hero-media" style="background-image:url('<?php echo esc_url( $mainimg ); ?>')"></div>
		<?php endif; ?>
		<div class="sbp-hero-scrim"></div>
		<div class="sbp-hero-inner">
		  <nav class="sbp-crumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'sayban_project' ) ); ?>">Projects</a><span>/</span>
			<b><?php the_title(); ?></b></nav>
		  <span class="sbp-kicker">By <?php echo esc_html( $dev ); ?></span>
		  <h1 class="sbp-hero-title"><?php the_title(); ?></h1>
		  <?php if ( $loc ) : ?><p class="sbp-hero-loc">&#9673; <?php echo esc_html( $loc ); ?></p><?php endif; ?>
		  <div class="sbp-hero-chips">
			<?php if ( $status ) : ?><span class="sbp-chip sbp-chip-live"><?php echo esc_html( $status ); ?></span><?php endif; ?>
			<?php if ( $ptype ) : ?><span class="sbp-chip"><?php echo esc_html( $ptype ); ?></span><?php endif; ?>
			<?php if ( $price ) : ?><span class="sbp-chip sbp-chip-price">From <?php echo esc_html( $price ); ?></span><?php endif; ?>
		  </div>
		  <div class="sbp-hero-cta">
			<?php if ( $plans ) : ?>
			  <a class="sbp-btn sbp-btn-gold" href="#payment">View Payment Plan</a>
			  <a class="sbp-btn sbp-btn-glass" href="#inquire">Request a Call Back</a>
			<?php else : ?>
			  <a class="sbp-btn sbp-btn-gold" href="#inquire">Register Interest</a>
			<?php endif; ?>
		  </div>
		</div>
	  </section>

	  <div class="sbp-wrap">
		<div class="sbp-layout">
		  <div class="sbp-main">

			<!-- ===== key facts ===== -->
			<section class="sbp-facts">
			  <?php foreach ( $fact_rows as $k => $v ) : ?>
				<div class="sbp-fact"><span><?php echo esc_html( $k ); ?></span><b><?php echo esc_html( $v ); ?></b></div>
			  <?php endforeach; ?>
			</section>

			<!-- ===== overview ===== -->
			<section class="sbp-sec">
			  <span class="sbp-eyebrow">Overview</span>
			  <h2 class="sbp-h2"><?php echo $tagline ? esc_html( $tagline ) : 'A signature Sayban development'; ?></h2>
			  <div class="sbp-prose"><?php the_content(); ?></div>
			</section>

			<!-- ===== gallery ===== -->
			<?php if ( count( $gallery ) > 1 ) : ?>
			<section class="sbp-sec">
			  <span class="sbp-eyebrow">Gallery</span>
			  <h2 class="sbp-h2">Inside the community</h2>
			  <div class="sbp-gallery">
				<?php foreach ( $gallery as $i => $gid ) :
					$full = wp_get_attachment_image_url( $gid, 'full' );
					$thumb = wp_get_attachment_image_url( $gid, 'large' ); ?>
				  <a class="sbp-gal-item sb-lb<?php echo $i === 0 ? ' sbp-gal-lead' : ''; ?>" href="<?php echo esc_url( $full ); ?>" data-full="<?php echo esc_url( $full ); ?>" data-group="project"
					 style="background-image:url('<?php echo esc_url( $thumb ); ?>')"></a>
				<?php endforeach; ?>
			  </div>
			</section>
			<?php endif; ?>

			<!-- ===== features ===== -->
			<?php if ( $features ) : ?>
			<section class="sbp-sec">
			  <span class="sbp-eyebrow">Why Saudagran</span>
			  <h2 class="sbp-h2">Built to live well</h2>
			  <div class="sbp-features">
				<?php foreach ( $features as $f ) : ?>
				  <div class="sbp-feature"><span class="sbp-feature-ic">&#10003;</span><?php echo esc_html( $f ); ?></div>
				<?php endforeach; ?>
			  </div>
			</section>
			<?php endif; ?>

			<!-- ===== payment plans ===== -->
			<?php if ( $plans ) : ?>
			<section class="sbp-sec" id="payment">
			  <span class="sbp-eyebrow">Payment Plans</span>
			  <div class="sbp-pay-head">
				<h2 class="sbp-h2">Easy installment plans</h2>
				<?php if ( $brochure ) : ?><a class="sbp-btn sbp-btn-ghostgold sbp-btn-sm" href="<?php echo esc_url( $brochure ); ?>" target="_blank" rel="noopener">&#8681; Download Payment Plan (PDF)</a><?php endif; ?>
			  </div>

			  <?php if ( count( $plans ) > 1 ) : ?>
			  <div class="sbp-tabs" role="tablist">
				<?php foreach ( $plans as $i => $plan ) : ?>
				  <button type="button" class="sbp-tab<?php echo $i === 0 ? ' on' : ''; ?>" data-tab="<?php echo (int) $i; ?>"><?php echo esc_html( $plan['name'] ?? ( 'Plan ' . ( $i + 1 ) ) ); ?></button>
				<?php endforeach; ?>
			  </div>
			  <?php endif; ?>

			  <?php foreach ( $plans as $i => $plan ) : ?>
				<div class="sbp-plan<?php echo $i === 0 ? ' on' : ''; ?>" data-panel="<?php echo (int) $i; ?>">
				  <?php if ( ! empty( $plan['total'] ) || ! empty( $plan['subtitle'] ) ) : ?>
				  <div class="sbp-plan-topline">
					<?php if ( ! empty( $plan['subtitle'] ) ) : ?><span class="sbp-plan-sub"><?php echo esc_html( $plan['subtitle'] ); ?></span><?php endif; ?>
					<?php if ( ! empty( $plan['total'] ) ) : ?><span class="sbp-plan-total">Total <b><?php echo esc_html( $plan['total'] ); ?></b></span><?php endif; ?>
				  </div>
				  <?php endif; ?>
				  <table class="sbp-plan-table">
					<thead><tr><th>Milestone</th><th class="sbp-ta-right">Amount (PKR)</th></tr></thead>
					<tbody>
					  <?php foreach ( (array) ( $plan['rows'] ?? array() ) as $row ) : ?>
						<tr>
						  <td><?php echo esc_html( $row['label'] ?? '' ); ?><?php echo ! empty( $row['note'] ) ? ' <span class="sbp-row-note">' . esc_html( $row['note'] ) . '</span>' : ''; ?></td>
						  <td class="sbp-ta-right"><?php echo esc_html( $row['amount'] ?? '' ); ?></td>
						</tr>
					  <?php endforeach; ?>
					</tbody>
					<?php if ( ! empty( $plan['total'] ) ) : ?>
					<tfoot><tr><td>Total Amount</td><td class="sbp-ta-right"><?php echo esc_html( $plan['total'] ); ?></td></tr></tfoot>
					<?php endif; ?>
				  </table>
				  <?php if ( ! empty( $plan['notes'] ) ) : ?>
					<ul class="sbp-plan-notes">
					  <?php foreach ( (array) $plan['notes'] as $n ) : ?><li><?php echo esc_html( $n ); ?></li><?php endforeach; ?>
					</ul>
				  <?php endif; ?>
				</div>
			  <?php endforeach; ?>
			  <p class="sbp-pay-disclaimer">Prices &amp; schedules are indicative and set by the developer; taxes, documentation and development charges may apply. Contact us to confirm current pricing and availability.</p>
			</section>
			<?php endif; ?>

			<!-- ===== location ===== -->
			<?php if ( $lat && $lng ) : ?>
			<section class="sbp-sec" id="location">
			  <span class="sbp-eyebrow">Location</span>
			  <h2 class="sbp-h2"><?php echo esc_html( $city ? $city : 'On the map' ); ?></h2>
			  <div class="sbp-map">
				<iframe loading="lazy" title="Project location" src="https://www.openstreetmap.org/export/embed.html?bbox=<?php echo esc_attr( ( $lng - 0.02 ) . ',' . ( $lat - 0.015 ) . ',' . ( $lng + 0.02 ) . ',' . ( $lat + 0.015 ) ); ?>&layer=mapnik&marker=<?php echo esc_attr( $lat . ',' . $lng ); ?>"></iframe>
			  </div>
			  <?php if ( $loc ) : ?><p class="sbp-map-addr">&#9673; <?php echo esc_html( $loc ); ?></p><?php endif; ?>
			</section>
			<?php endif; ?>

		  </div><!-- .sbp-main -->

		  <!-- ================= STICKY RAIL ================= -->
		  <aside class="sbp-rail" id="inquire">
			<div class="sbp-rail-card">
			  <div class="sbp-rail-price">
				<?php if ( $price ) : ?><span>Starting from</span><b><?php echo esc_html( $price ); ?></b><?php else : ?><b>Request pricing</b><?php endif; ?>
			  </div>
			  <p class="sbp-rail-lead">Get the full payment plan &amp; a call back from our team.</p>

			  <?php if ( $inq === 'ok' ) : ?>
				<div class="sbp-inq-ok">&#10003; Thanks! Our team will contact you shortly.</div>
			  <?php elseif ( $inq === 'error' ) : ?>
				<div class="sbp-inq-err">Please add your name and phone number and try again.</div>
			  <?php endif; ?>

			  <form class="sbp-inq-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="sayban_project_inquiry">
				<input type="hidden" name="project_id" value="<?php echo (int) $pid; ?>">
				<?php wp_nonce_field( 'sayban_project_inquiry', 'sayban_inq_nonce' ); ?>
				<input type="text" name="inq_name" placeholder="Your name*" required>
				<input type="tel" name="inq_phone" placeholder="Phone number*" required>
				<input type="email" name="inq_email" placeholder="Email (optional)">
				<textarea name="inq_message" rows="3" placeholder="I'm interested in <?php echo esc_attr( get_the_title() ); ?>…"></textarea>
				<input type="text" name="sayban_hp" class="sbp-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
				<button type="submit" class="sbp-btn sbp-btn-gold sbp-btn-block">Request Call Back</button>
			  </form>

			  <div class="sbp-rail-contact">
				<?php if ( $wa ) : ?><a class="sbp-btn sbp-btn-wa sbp-btn-block" href="https://wa.me/<?php echo esc_attr( $wa ); ?>?text=<?php echo rawurlencode( "Hi, I'm interested in " . get_the_title() ); ?>" target="_blank" rel="noopener">&#9993; WhatsApp Us</a><?php endif; ?>
				<?php if ( $phone ) : ?><a class="sbp-btn sbp-btn-charcoal sbp-btn-block" href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>">&#9742; Call <?php echo esc_html( $phone ); ?></a><?php endif; ?>
			  </div>
			  <div class="sbp-rail-trust">&#10003; Verified documentation &nbsp;·&nbsp; &#10003; Direct from developer</div>
			</div>
		  </aside>

		</div>
	  </div>
	</div>

	<script>
	(function () {
	  // payment-plan tabs
	  var root = document.getElementById('payment');
	  if (root) {
		var tabs = root.querySelectorAll('.sbp-tab');
		var panels = root.querySelectorAll('.sbp-plan');
		tabs.forEach(function (t) {
		  t.addEventListener('click', function () {
			var idx = t.getAttribute('data-tab');
			tabs.forEach(function (x) { x.classList.remove('on'); });
			panels.forEach(function (p) { p.classList.toggle('on', p.getAttribute('data-panel') === idx); });
			t.classList.add('on');
		  });
		});
	  }
	})();
	</script>

<?php endwhile; ?>

<?php
get_footer();
