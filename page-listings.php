<?php
/**
 * Sayban.pk — listings / search page template (matches site/listings.html).
 * Assigned to the "Search Properties Page" via functions.php template_include.
 * Re-lays REM's real search form + results into the mockup's sidebar + grid,
 * so filtering keeps working (REM's GET/AJAX form is untouched, only re-styled).
 */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="sb-listings-page">
  <div class="sb-listings-wrap">

	<nav class="sb-breadcrumb">
	  <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
	  <span>/</span><b><?php echo esc_html( get_the_title() ); ?></b>
	</nav>

	<div class="sb-map-strip">
	  <span class="sb-map-strip-txt">◈&nbsp;&nbsp;Browse verified listings — owners, agents &amp; Sayban Builders' own projects. · auto-deploy test ✓</span>
	</div>

	<div class="sb-listings">
	  <?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
	  ?>
	</div>

  </div>
</div>

<script>
(function () {
  var listings = document.querySelector('.sb-listings');
  if (!listings) return;
  var results = listings.querySelector('.search-results');
  if (!results) return;
  var observer = null;

  function ensureHead() {
	var head = results.querySelector('.sb-results-head');
	if (!head) {
	  head = document.createElement('div');
	  head.className = 'sb-results-head';
	  head.innerHTML = '<h1 class="sb-results-title">Properties</h1><div class="sb-results-count"></div>';
	  results.insertBefore(head, results.firstChild);
	}
  }

  function updateCount() {
	var wrap = listings.querySelector('.searched-properties');
	var countEl = results.querySelector('.sb-results-count');
	if (!wrap || !countEl) return;
	// count actual rendered cards (works for the initial grid and AJAX search results)
	var n = wrap.querySelectorAll('.rem-property-box').length;
	if (!n) { n = wrap.querySelectorAll('.m-item').length; }
	var text = n + ' result' + (n === 1 ? '' : 's');
	if (countEl.textContent === text) return;      // guard: unchanged → no DOM write, no loop
	if (observer) { observer.disconnect(); }        // never observe our own write
	countEl.textContent = text;
	if (observer) { observer.observe(results, { childList: true, subtree: true }); }
  }

  function init() {
	ensureHead();
	if (window.MutationObserver) {
	  observer = new MutationObserver(updateCount);
	  observer.observe(results, { childList: true, subtree: true });
	}
	updateCount();
  }
  if (document.readyState !== 'loading') init();
  else document.addEventListener('DOMContentLoaded', init);
})();
</script>

<?php
get_footer();
