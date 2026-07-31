<?php
/**
 * Sayban.pk — Agent Login page. Centered, styled login card in the design
 * chrome (reuses sayban-create.css). Wraps REM's [rem_agent_login] form.
 */
defined( 'ABSPATH' ) || exit;

get_header();
$logged = is_user_logged_in();
?>
<div class="sb-post-page">
  <div class="sb-post-wrap">

	<div class="sb-post-head">
	  <div class="sb-kicker">Welcome Back</div>
	  <h1 class="sb-post-title">Log in to Sayban</h1>
	  <p class="sb-post-sub">Manage your listings, leads, and profile.</p>
	</div>

	<div class="sb-post-grid sb-post-grid-login">
	  <div class="sb-form-card sb-login-card">
		<?php if ( $logged ) : ?>
		  <p style="font-size:15px;color:var(--ink2);margin:0 0 14px;">You're already logged in.</p>
		  <a class="sb-wz-next" style="display:inline-block;text-decoration:none;" href="<?php echo esc_url( sayban_page_url( 'dashboard' ) ); ?>">Go to your dashboard &#8594;</a>
		<?php else : ?>
		  <?php echo do_shortcode( '[rem_agent_login heading="Please enter your email and password."]' ); ?>
		  <div class="sb-login-alt">New to Sayban? <a href="<?php echo esc_url( sayban_page_url( 'register' ) ); ?>">Create an account &#8594;</a></div>
		<?php endif; ?>
	  </div>
	</div>
  </div>
</div>

<?php
get_footer();
