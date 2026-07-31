<?php
/**
 * Sayban.pk — unified member Dashboard (matches site/dashboard.html).
 * One shell (sidebar + main) shared by three existing REM pages via the
 * template_include filter:
 *   my-properties (458)  → view = listings  ([rem_my_properties])
 *   edit-profile  (456)  → view = profile   ([rem_agent_edit])
 *   edit-property (457)  → view = edit       ([rem_edit_property], wizard)
 * REM's forms + submission logic stay intact — this only wraps + re-skins them.
 */
defined( 'ABSPATH' ) || exit;

get_header();

/* ---------- which view ---------- */
if ( is_page( 'edit-profile' ) )       { $view = 'profile'; }
elseif ( is_page( 'edit-property' ) )  { $view = 'edit'; }
else                                   { $view = 'listings'; } // my-properties

$logged = is_user_logged_in();

/* ---------- logged-out: centered login card (reuses create styling) ---------- */
if ( ! $logged ) {
	echo '<div class="sb-post-page"><div class="sb-post-wrap"><div class="sb-post-head" style="text-align:center">'
	   . '<div class="sb-kicker" style="justify-content:center">Member Area</div>'
	   . '<h1 class="sb-post-title">Sign in to your dashboard</h1>'
	   . '<p class="sb-post-sub">Manage your listings, edit your profile and track your ads.</p></div>'
	   . '<div class="sb-post-grid sb-post-grid-login"><div class="sb-form-card sb-login-card">'
	   . do_shortcode( '[rem_agent_login heading="Log in to continue"]' )
	   . '</div></div></div></div>';
	get_footer();
	return;
}

/* ---------- current user + real stats ---------- */
$u        = wp_get_current_user();
$uid      = $u->ID;
$name     = $u->display_name ? $u->display_name : $u->user_login;
$initial  = strtoupper( mb_substr( $name, 0, 1 ) );
$city     = get_user_meta( $uid, 'agent_city', true );
if ( ! $city ) { $city = get_user_meta( $uid, 'city', true ); }
$role_lbl = in_array( 'administrator', (array) $u->roles, true ) ? 'Admin account' : 'Owner account';

$count_status = function ( $status ) use ( $uid ) {
	$q = new WP_Query( array(
		'post_type'      => 'rem_property',
		'author'         => $uid,
		'post_status'    => $status,
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => false,
	) );
	$n = (int) $q->found_posts; wp_reset_postdata(); return $n;
};
$n_active  = $count_status( 'publish' );
$n_pending = $count_status( array( 'pending', 'draft' ) );
$n_total   = $count_status( array( 'publish', 'pending', 'draft', 'private' ) );
$n_feat    = (int) ( new WP_Query( array(
	'post_type' => 'rem_property', 'author' => $uid, 'post_status' => 'publish', 'posts_per_page' => 1,
	'fields' => 'ids', 'meta_query' => array( array( 'key' => 'rem_property_featured', 'value' => 'Yes' ) ),
) ) )->found_posts;

$url_list = sayban_page_url( 'dashboard' );
$url_prof = sayban_page_url( 'profile' );
$url_add  = sayban_page_url( 'create' );
$url_out  = wp_logout_url( home_url( '/' ) );

/* edit view: which property */
$edit_pid  = ( $view === 'edit' && isset( $_GET['property_id'] ) ) ? (int) $_GET['property_id'] : 0;
$edit_title = $edit_pid ? get_the_title( $edit_pid ) : '';

$titles = array(
	'listings' => 'My Listings',
	'profile'  => 'Profile &amp; Contact Info',
	'edit'     => 'Edit Property',
);
?>

<div class="sb-dash">
  <div class="sb-dash-wrap">
	<div class="sb-dash-grid">

	  <!-- ================= SIDEBAR ================= -->
	  <aside class="sb-dash-side">
		<div class="sb-dash-user">
		  <span class="sb-dash-avatar"><?php echo esc_html( $initial ); ?></span>
		  <div class="sb-dash-user-meta">
			<b><?php echo esc_html( $name ); ?></b>
			<span><?php echo esc_html( $role_lbl . ( $city ? ' · ' . ucwords( $city ) : '' ) ); ?></span>
		  </div>
		</div>
		<nav class="sb-dash-menu">
		  <a href="<?php echo esc_url( $url_list ); ?>" class="<?php echo ( $view === 'listings' || $view === 'edit' ) ? 'on' : ''; ?>">
			<span class="sb-dash-ico">▤</span> My Listings</a>
		  <a href="<?php echo esc_url( $url_add ); ?>"><span class="sb-dash-ico">＋</span> Add Property</a>
		  <a href="<?php echo esc_url( $url_prof ); ?>" class="<?php echo $view === 'profile' ? 'on' : ''; ?>">
			<span class="sb-dash-ico">◔</span> Profile &amp; Contact</a>
		  <a href="<?php echo esc_url( $url_out ); ?>" class="sb-dash-logout"><span class="sb-dash-ico">⇥</span> Logout</a>
		</nav>
	  </aside>

	  <!-- ================= MAIN ================= -->
	  <div class="sb-dash-main">

		<?php if ( $view === 'listings' ) : ?>

		  <div class="sb-dash-tiles">
			<div class="sb-tile"><b><?php echo (int) $n_active; ?></b><span>Active Listings</span></div>
			<div class="sb-tile"><b class="sb-tile-gold"><?php echo (int) $n_pending; ?></b><span>Pending Review</span></div>
			<div class="sb-tile"><b class="sb-tile-teal"><?php echo (int) $n_feat; ?></b><span>Featured</span></div>
			<div class="sb-tile"><b><?php echo (int) $n_total; ?></b><span>Total Listings</span></div>
		  </div>

		  <div class="sb-dash-bar">
			<h1 class="sb-dash-h1">My Listings</h1>
			<a class="sb-btn sb-btn-gold" href="<?php echo esc_url( $url_add ); ?>">+ Add Property</a>
		  </div>

		  <div class="sb-dash-tablecard">
			<?php echo do_shortcode( '[rem_my_properties]' ); ?>
		  </div>

		<?php elseif ( $view === 'profile' ) : ?>

		  <div class="sb-dash-bar"><h1 class="sb-dash-h1">Profile &amp; Contact Info</h1></div>
		  <p class="sb-dash-lead">Keep your public agent details and contact numbers up to date — buyers see these on your listings.</p>
		  <div class="sb-post-page sb-dash-formhost"><div class="sb-form-card">
			<?php echo do_shortcode( '[rem_agent_edit]' ); ?>
		  </div></div>

		<?php else : /* edit */ ?>

		  <div class="sb-dash-crumb"><a href="<?php echo esc_url( $url_list ); ?>">&larr; My Listings</a></div>
		  <div class="sb-dash-bar">
			<h1 class="sb-dash-h1">Edit Property<?php echo $edit_title ? ' <span class="sb-dash-h1-sub">— ' . esc_html( $edit_title ) . '</span>' : ''; ?></h1>
		  </div>

		  <div class="sb-stepper" id="sb-stepper">
			<div class="sb-step active" data-step="1"><span class="sb-step-n">1</span><div class="sb-step-t"><b>Purpose &amp; Details</b><span>type, price, area</span></div></div>
			<div class="sb-step" data-step="2"><span class="sb-step-n">2</span><div class="sb-step-t"><b>Location</b><span>city, area, map pin</span></div></div>
			<div class="sb-step" data-step="3"><span class="sb-step-n">3</span><div class="sb-step-t"><b>Photos &amp; Features</b><span>images, amenities</span></div></div>
			<div class="sb-step" data-step="4"><span class="sb-step-n">4</span><div class="sb-step-t"><b>Review &amp; Save</b><span>save changes</span></div></div>
		  </div>

		  <div class="sb-post-page sb-dash-formhost"><div class="sb-form-card">
			<?php echo do_shortcode( '[rem_edit_property]' ); ?>
		  </div></div>

		<?php endif; ?>

	  </div><!-- .sb-dash-main -->
	</div>
  </div>
</div>

<?php if ( $view === 'edit' ) : ?>
<script>
/* Reuse the create-property wizard for the edit form (same #create-property id,
   same section names). Submit here is <input type="submit"> (not #form-submit). */
(function () {
  var form = document.querySelector('#create-property');
  var stepper = document.getElementById('sb-stepper');
  if (!form || !stepper) return;
  var blocks = Array.prototype.slice.call(form.querySelectorAll('.info-block'));
  if (blocks.length < 2) return;

  function stepFor(text) {
	text = (text || '').toLowerCase();
	if (/image|photo|video|feature|amenit|attachment/.test(text)) return 3;
	if (/\bmap\b|location|place on/.test(text)) return 2;
	return 1;
  }

  var host = blocks[0].parentNode;
  var panels = {};
  for (var s = 1; s <= 4; s++) { var p = document.createElement('div'); p.className = 'sb-wz-panel'; p.setAttribute('data-step', s); panels[s] = p; }
  blocks.forEach(function (b) { var h = b.querySelector('.section-title, h3'); panels[stepFor(h ? h.textContent : '')].appendChild(b); });

  var review = document.createElement('div');
  review.className = 'sb-wz-review';
  review.innerHTML = '<h3 class="title">Review &amp; Save</h3><p>Give your listing a final look, then save — changes go live after a quick review.</p>';
  panels[4].appendChild(review);
  var submit = form.querySelector('#form-submit, input[type="submit"], .button-form');
  if (submit) { panels[4].appendChild(submit); }

  for (var s2 = 1; s2 <= 4; s2++) { host.appendChild(panels[s2]); }

  var nav = document.createElement('div');
  nav.className = 'sb-wz-nav';
  nav.innerHTML = '<button type="button" class="sb-wz-back">&#8592; Back</button><button type="button" class="sb-wz-next">Continue &#8594;</button>';
  host.appendChild(nav);
  var backBtn = nav.querySelector('.sb-wz-back');
  var nextBtn = nav.querySelector('.sb-wz-next');

  var current = 1;
  function show(step) {
	current = step;
	for (var s = 1; s <= 4; s++) { panels[s].style.display = (s === step) ? 'block' : 'none'; }
	Array.prototype.forEach.call(stepper.querySelectorAll('.sb-step'), function (el) {
	  var n = +el.getAttribute('data-step');
	  el.classList.toggle('active', n === step);
	  el.classList.toggle('done', n < step);
	});
	backBtn.style.visibility = step === 1 ? 'hidden' : 'visible';
	nextBtn.style.display = step === 4 ? 'none' : '';
	if (step === 2) { setTimeout(function () { window.dispatchEvent(new Event('resize')); }, 60); }
	var head = document.querySelector('.sb-dash-bar'); if (head) head.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
  nextBtn.addEventListener('click', function () { if (current < 4) show(current + 1); });
  backBtn.addEventListener('click', function () { if (current > 1) show(current - 1); });
  Array.prototype.forEach.call(stepper.querySelectorAll('.sb-step'), function (el) {
	el.addEventListener('click', function () { show(+el.getAttribute('data-step')); });
  });
  show(1);
})();
</script>
<?php endif; ?>

<?php
get_footer();
