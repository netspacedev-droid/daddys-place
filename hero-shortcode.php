<?php
/**
 * Hero section with a background video: [xh_hero]
 *
 * Install: Code Snippets > Add New > paste this (without the opening <?php line)
 * > "Run snippet everywhere" > Save & Activate.
 *
 * Then edit your Home page in Elementor and add a "Shortcode" widget at the very top of the page:  [xh_hero]
 * (Put it in a Full Width container with 0 padding so the video can reach the screen edges.
 *  The header is transparent and sits on top of this section.)
 */

add_shortcode( 'xh_hero', function () {

	// ---- edit these ----
	$video_url  = 'https://palegoldenrod-tarsier-648955.hostingersite.com/wp-content/uploads/2026/10/kemiiiiiii.mp4';
	$poster_url = '';          // optional: image shown while the video loads (e.g. a .jpg from Media Library)
	$word_white = 'Digital';   // first word  (white)
	$word_green = 'Wonders';   // second word (#BAEAA0)
	$kicker     = 'Affordable Advertising Solutions';   // small line ABOVE the heading (leave '' to hide)
	$sub        = '';          // optional small line BELOW the heading
	// --------------------

	$css = <<<'CSS'
  .xhero {
    --xhero-white: #ffffff;
    --xhero-green: #BAEAA0;
    --xhero-max: 1320px;

    position: relative; isolation: isolate; overflow: hidden;
    display: flex; align-items: flex-end;
    width: 100%; min-height: 100vh; min-height: 100svh;
    background: #050505; color: var(--xhero-white);
    font-family: "Poppins", "Segoe UI", Arial, sans-serif;
  }
  .xhero *, .xhero *::before, .xhero *::after { box-sizing: border-box; }

  .xhero__video { position: absolute; top: 0; right: 0; bottom: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: -2; }
  /* dark at the top (so the header is readable) and at the bottom (so the text is readable) */
  .xhero__shade {
    position: absolute; top: 0; right: 0; bottom: 0; left: 0; z-index: -1;
    background: linear-gradient(180deg, rgba(0,0,0,.60) 0%, rgba(0,0,0,.08) 30%, rgba(0,0,0,.12) 55%, rgba(0,0,0,.80) 100%);
  }

  .xhero__inner { width: 100%; max-width: var(--xhero-max); margin: 0 auto; padding: 120px 24px clamp(36px, 9vh, 110px); }

  .xhero .xhero__kicker,
  .xhero .xhero__sub { margin: 0; color: var(--xhero-white); font-weight: 300; line-height: 1.1; text-transform: none; letter-spacing: 0; }
  .xhero .xhero__kicker { font-size: clamp(22px, 3.6vw, 54px); margin-bottom: .15em; }
  .xhero .xhero__sub    { font-size: clamp(20px, 3.2vw, 46px); margin-top: .25em; }

  .xhero .xhero__title {
    display: flex; flex-wrap: wrap; column-gap: .22em; margin: 0; padding: 0;
    font-family: "Anton", "Bebas Neue", Impact, "Arial Narrow", sans-serif; font-weight: 400;
    font-size: clamp(68px, 13vw, 210px); line-height: .92; letter-spacing: .01em; text-transform: uppercase;
  }
  .xhero .xhero__title span:first-child { color: var(--xhero-white); }
  .xhero .xhero__title span:last-child  { color: var(--xhero-green); }

  @media (max-width: 920px) {
    .xhero__inner { display: flex; flex-direction: column; padding-top: 140px; padding-left: 16px; padding-right: 16px; }
    /* mobile: heading first, small line goes UNDER it */
    .xhero .xhero__title  { order: 1; }
    .xhero .xhero__kicker { order: 2; margin: .5em 0 0; font-size: clamp(18px, 5vw, 28px); }
    .xhero .xhero__sub    { order: 3; }
  }
CSS;

	$js = <<<'JS'
(function () {
  var v = document.querySelector('.xhero__video');
  if (!v) return;
  // respect "reduce motion" settings; otherwise make sure the muted video starts
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) { v.removeAttribute('autoplay'); v.pause(); return; }
  var p = v.play && v.play();
  if (p && p.catch) p.catch(function () {});
})();
JS;

	ob_start();
	?>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Anton&display=swap">
	<style><?php echo $css; // phpcs:ignore ?></style>

	<section class="xhero">
	  <video class="xhero__video" autoplay muted loop playsinline preload="auto" aria-hidden="true"<?php if ( $poster_url ) : ?> poster="<?php echo esc_url( $poster_url ); ?>"<?php endif; ?>>
	    <source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
	  </video>
	  <div class="xhero__shade"></div>

	  <div class="xhero__inner">
	    <?php if ( $kicker ) : ?><p class="xhero__kicker"><?php echo esc_html( $kicker ); ?></p><?php endif; ?>
	    <h2 class="xhero__title"><span><?php echo esc_html( $word_white ); ?></span> <span><?php echo esc_html( $word_green ); ?></span></h2>
	    <?php if ( $sub ) : ?><p class="xhero__sub"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
	  </div>
	</section>

	<script><?php echo $js; // phpcs:ignore ?></script>
	<?php
	return ob_get_clean();
} );
