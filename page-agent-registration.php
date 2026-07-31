<?php
/**
 * Sayban.pk — Agent Registration page. Same treatment as the create page:
 * design chrome + a 4-step wizard around REM's [rem_register_agent] form.
 * REM groups the form into `.tab-wrap-*` sections (personal / social / skills /
 * location) — we turn those into the wizard steps. Reuses sayban-create.css.
 */
defined( 'ABSPATH' ) || exit;

get_header();
$logged = is_user_logged_in();
?>
<div class="sb-post-page">
  <div class="sb-post-wrap">

	<div class="sb-post-head">
	  <div class="sb-kicker">Free Account</div>
	  <h1 class="sb-post-title">Create Your Sayban Account</h1>
	  <p class="sb-post-sub">List properties, manage your leads, and reach buyers directly — no commission.</p>
	</div>

	<?php if ( ! $logged ) : ?>
	<div class="sb-stepper" id="sb-stepper">
	  <div class="sb-step active" data-step="1"><span class="sb-step-n">1</span><div class="sb-step-t"><b>Your Details</b><span>name, email, password</span></div></div>
	  <div class="sb-step" data-step="2"><span class="sb-step-n">2</span><div class="sb-step-t"><b>Contact &amp; Social</b><span>phone, WhatsApp, links</span></div></div>
	  <div class="sb-step" data-step="3"><span class="sb-step-n">3</span><div class="sb-step-t"><b>About You</b><span>tagline, skills</span></div></div>
	  <div class="sb-step" data-step="4"><span class="sb-step-n">4</span><div class="sb-step-t"><b>Location &amp; Finish</b><span>map, create account</span></div></div>
	</div>
	<?php endif; ?>

	<div class="sb-post-grid<?php echo $logged ? ' sb-post-grid-login' : ''; ?>">
	  <div class="sb-form-card">
		<?php echo do_shortcode( '[rem_register_agent]You are already logged in — head to your dashboard to manage listings.[/rem_register_agent]' ); ?>
	  </div>

	  <?php if ( ! $logged ) : ?>
	  <aside class="sb-post-rail">
		<div class="sb-tip-card">
		  <b>Why join Sayban?</b>
		  <ul>
			<li>Free to list — zero commission</li>
			<li>Direct buyer &amp; renter leads (call + WhatsApp)</li>
			<li>Manage everything from your dashboard</li>
			<li>Get the verified-agent badge on your listings</li>
		  </ul>
		</div>
		<div class="sb-next-card">
		  <b>Already have an account?</b>
		  <a href="<?php echo esc_url( home_url( '/agent-login-page/' ) ); ?>" style="color:var(--gold-deep);font-weight:700;">Log in here →</a>
		</div>
	  </aside>
	  <?php endif; ?>
	</div>
  </div>
</div>

<?php if ( ! $logged ) : ?>
<script>
(function () {
  var form = document.querySelector('#agent_login, form.register-agent, form[id*="agent"]');
  var stepper = document.getElementById('sb-stepper');
  if (!form || !stepper) return;
  var blocks = Array.prototype.slice.call(form.querySelectorAll('[class*="tab-wrap-"]'));
  if (blocks.length < 2) return;

  function stepFor(cls) {
	cls = (cls || '').toLowerCase();
	if (/social/.test(cls)) return 2;
	if (/skill/.test(cls)) return 3;
	if (/location/.test(cls)) return 4;
	return 1; // personal_info
  }

  var host = blocks[0].parentNode;
  var panels = {};
  for (var s = 1; s <= 4; s++) { var p = document.createElement('div'); p.className = 'sb-wz-panel'; p.setAttribute('data-step', s); panels[s] = p; }
  blocks.forEach(function (b) { panels[ stepFor(b.className) ].appendChild(b); });

  // move the real submit button into step 4
  var submit = form.querySelector('.signin-button, button[type="submit"], input[type="submit"]');
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
	if (step === 4) { setTimeout(function () { window.dispatchEvent(new Event('resize')); }, 60); }
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
