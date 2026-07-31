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
	  <div class="sb-step" data-step="3"><span class="sb-step-n">3</span><div class="sb-step-t"><b>About You &amp; Finish</b><span>tagline, skills, create</span></div></div>
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
  var form = document.querySelector('#agent_login') || document.querySelector('form[id*="agent"]');
  var stepper = document.getElementById('sb-stepper');
  if (!form || !stepper) return;

  // REM outputs the fields as a flat <ul class="profile create"> of <li> items;
  // group those <li> by field name into the 3 wizard steps.
  var lis = Array.prototype.slice.call(form.querySelectorAll('li')).filter(function (li) {
	return li.querySelector('input[name], select[name], textarea[name]');
  });
  if (lis.length < 3) return;

  function stepForLi(li) {
	var inp = li.querySelector('input[name], select[name], textarea[name]');
	var name = inp ? (inp.getAttribute('name') || '').toLowerCase() : '';
	if (/tagline|skills/.test(name)) return 3;
	if (/agent_url|mobile|whatsapp|facebook|twitter|linkedin|instagram|youtube/.test(name)) return 2;
	return 1; // name, email, password, description, avatar
  }

  var LAST = 3;
  var titles = { 1: 'Your Details', 2: 'Contact &amp; Social', 3: 'About You' };
  var srcUl = lis[0].parentNode;
  var host = srcUl.parentNode;
  var panels = {}, uls = {};
  for (var s = 1; s <= LAST; s++) {
	var p = document.createElement('div'); p.className = 'sb-wz-panel'; p.setAttribute('data-step', s);
	var h = document.createElement('h3'); h.className = 'sb-wz-h'; h.innerHTML = titles[s];
	var u = document.createElement('ul'); u.className = 'sb-wz-ul ' + (srcUl.className || 'profile create');
	p.appendChild(h); p.appendChild(u); panels[s] = p; uls[s] = u;
  }
  lis.forEach(function (li) { uls[ stepForLi(li) ].appendChild(li); });

  // review + real submit → last step
  var submit = form.querySelector('.signin-button') || form.querySelector('[type="submit"]');
  if (submit) {
	var rev = document.createElement('div'); rev.className = 'sb-wz-review';
	rev.innerHTML = '<p>Almost there — create your account and start listing. It only takes a minute.</p>';
	panels[LAST].appendChild(rev);
	panels[LAST].appendChild(submit);
  }
  for (var s2 = 1; s2 <= LAST; s2++) { host.appendChild(panels[s2]); }

  // hide REM's original scaffolding (empty section headers, the now-empty
  // source lists, the leftover map, tab-wrap markers) — our panels replace them.
  var hide = function (sel) { Array.prototype.forEach.call(form.querySelectorAll(sel), function (el) { el.style.display = 'none'; }); };
  hide('.section-title');
  hide('[class*="tab-wrap-"]');
  Array.prototype.forEach.call(form.querySelectorAll('ul'), function (u) { if (!u.closest('.sb-wz-panel')) { u.style.display = 'none'; } });
  Array.prototype.forEach.call(form.querySelectorAll('br'), function (el) { el.style.display = 'none'; });

  var nav = document.createElement('div'); nav.className = 'sb-wz-nav';
  nav.innerHTML = '<button type="button" class="sb-wz-back">&#8592; Back</button><button type="button" class="sb-wz-next">Continue &#8594;</button>';
  host.appendChild(nav);
  var backBtn = nav.querySelector('.sb-wz-back');
  var nextBtn = nav.querySelector('.sb-wz-next');

  var current = 1;
  function show(step) {
	current = step;
	for (var s = 1; s <= LAST; s++) { panels[s].style.display = (s === step) ? 'block' : 'none'; }
	Array.prototype.forEach.call(stepper.querySelectorAll('.sb-step'), function (el) {
	  var n = +el.getAttribute('data-step');
	  el.classList.toggle('active', n === step);
	  el.classList.toggle('done', n < step);
	});
	backBtn.style.visibility = step === 1 ? 'hidden' : 'visible';
	nextBtn.style.display = step === LAST ? 'none' : '';
	var head = document.querySelector('.sb-post-head'); if (head) head.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
  nextBtn.addEventListener('click', function () { if (current < LAST) show(current + 1); });
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
