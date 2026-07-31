<?php
/**
 * Sayban.pk — Post a Property page (matches site/post-property.html).
 * Wraps REM's [rem_create_property] form in the design chrome (kicker, title,
 * 4-step stepper, tips rail) and turns REM's .info-block sections into a wizard
 * with JS — REM's form + submission logic stay 100% intact.
 */
defined( 'ABSPATH' ) || exit;

get_header();
$logged = is_user_logged_in();
?>
<div class="sb-post-page">
  <div class="sb-post-wrap">

	<div class="sb-post-head">
	  <div class="sb-kicker">Free Listing</div>
	  <h1 class="sb-post-title">Post Your Property</h1>
	  <p class="sb-post-sub">Goes live after a quick review by our team — usually within 24 hours.</p>
	</div>

	<?php if ( $logged ) : ?>
	<div class="sb-stepper" id="sb-stepper">
	  <div class="sb-step active" data-step="1"><span class="sb-step-n">1</span><div class="sb-step-t"><b>Purpose &amp; Details</b><span>type, price, area</span></div></div>
	  <div class="sb-step" data-step="2"><span class="sb-step-n">2</span><div class="sb-step-t"><b>Location</b><span>city, area, map pin</span></div></div>
	  <div class="sb-step" data-step="3"><span class="sb-step-n">3</span><div class="sb-step-t"><b>Photos &amp; Features</b><span>images, amenities</span></div></div>
	  <div class="sb-step" data-step="4"><span class="sb-step-n">4</span><div class="sb-step-t"><b>Review &amp; Publish</b><span>submit for review</span></div></div>
	</div>
	<?php endif; ?>

	<div class="sb-post-grid<?php echo $logged ? '' : ' sb-post-grid-login'; ?>">
	  <div class="sb-form-card">
		<?php echo do_shortcode( '[rem_create_property][rem_agent_login heading="Please log in to post your property."][/rem_create_property]' ); ?>
	  </div>

	  <?php if ( $logged ) : ?>
	  <aside class="sb-post-rail">
		<div class="sb-tip-card">
		  <b>Tips for a fast approval</b>
		  <ul>
			<li>Use a real price — "Contact for price" gets 4× fewer leads</li>
			<li>Add at least 6 photos in daylight</li>
			<li>Mention document status (registry, intiqal, NOC)</li>
			<li>Don't repeat the same ad in multiple cities</li>
		  </ul>
		</div>
		<div class="sb-next-card">
		  <b>What happens next?</b>
		  Our moderators review every ad against listing rules. You'll get an SMS + email when it's live.
		</div>
	  </aside>
	  <?php endif; ?>
	</div>
  </div>
</div>

<?php if ( $logged ) : ?>
<script>
(function () {
  var form = document.querySelector('#create-property');
  var stepper = document.getElementById('sb-stepper');
  if (!form || !stepper) return;
  var blocks = Array.prototype.slice.call(form.querySelectorAll('.info-block'));
  if (blocks.length < 2) return; // not the create form (maybe login) — leave as-is

  function stepFor(text) {
	text = (text || '').toLowerCase();
	if (/image|photo|video|feature|amenit|attachment/.test(text)) return 3;
	if (/\bmap\b|location|place on/.test(text)) return 2;
	return 1; // basic info, details, internal structure, category, tags
  }

  var host = blocks[0].parentNode;
  var panels = {};
  for (var s = 1; s <= 4; s++) { var p = document.createElement('div'); p.className = 'sb-wz-panel'; p.setAttribute('data-step', s); panels[s] = p; }

  blocks.forEach(function (b) {
	var h = b.querySelector('.section-title, h3');
	panels[ stepFor(h ? h.textContent : '') ].appendChild(b);
  });

  // Step 4 = review + the real submit button (moved, still inside the form)
  var review = document.createElement('div');
  review.className = 'sb-wz-review';
  review.innerHTML = '<h3 class="title">Review &amp; Publish</h3><p>Give your listing a final look, then submit — it goes to our team for a quick review before going live.</p>';
  panels[4].appendChild(review);
  var submit = form.querySelector('#form-submit');
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
	if (step === 2) { setTimeout(function () { window.dispatchEvent(new Event('resize')); }, 60); } // nudge map
	var head = document.querySelector('.sb-post-head'); if (head) head.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
