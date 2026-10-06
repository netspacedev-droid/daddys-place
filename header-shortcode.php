<?php
/**
 * Custom header shortcode: [xh_header]
 *
 * Install: WordPress admin > Snippets (Code Snippets plugin) > Add New > paste this
 * (without the opening <?php line if the plugin adds it) > "Run snippet everywhere" > Save & Activate.
 * (Or paste it at the end of your CHILD theme's functions.php.)
 *
 * Then in Elementor Theme Builder > Header, drop a "Shortcode" widget and enter:  [xh_header]
 *
 * The menu is read from the WordPress menu named "Main Menu" (Appearance > Menus),
 * so any link you add/remove/re-order there shows up in the header automatically.
 */

add_shortcode( 'xh_header', function () {

	// Menu name (or slug / ID) from Appearance > Menus
	$menu = wp_nav_menu( array(
		'menu'        => 'Main Menu',
		'container'   => false,
		'menu_class'  => 'xh__menu',
		'menu_id'     => '',
		'depth'       => 2,
		'echo'        => false,
		'fallback_cb' => false,
	) );

	// ---- edit these ----
	$logo_url = 'https://palegoldenrod-tarsier-648955.hostingersite.com/wp-content/uploads/2026/10/WhatsApp-Image-2026-10-06-at-9.50.39-AM.jpeg';
	$email    = 'info@yourdomain.com';
	$phone    = '(+94) 00 000 0000';
	$phone_href = 'tel:+94000000000';
	$cta_text = 'Pay Online';
	$cta_url  = '/pay-online/';
	// --------------------

	$css = <<<'CSS'
  .xh {
    --xh-accent: #00853F;          /* green accent (match logo) */
    --xh-accent-dark: #006b32;     /* darker green for button gradient */
    --xh-accent-rgb: 0,133,63;     /* same green as r,g,b (used for fades) */
    --xh-text: #ffffff;
    --xh-muted: #9c8f84;
    --xh-overlay-top: rgba(0,0,0,.95);     /* darkness at the very top  (0 = fully clear, 1 = black) */
    --xh-overlay-mid: rgba(0,0,0,.70);     /* darkness around the middle */
    --xh-overlay-bottom: rgba(0,0,0,0);    /* darkness at the bottom edge (0 = no hard line) */
    --xh-logo-h: 120px;                    /* logo height */
    --xh-height-nav: 56px;
    --xh-menu-gap: 36px;                   /* space between menu items */
    --xh-max: 1320px;

    /* sits on top of the hero/slider below it, so the hero image shows through */
    position: absolute;
    top: 0; left: 0; right: 0;
    z-index: 999;
    width: 100%;
    color: var(--xh-text);
    font-family: "Poppins", "Segoe UI", Arial, sans-serif;
    background: linear-gradient(180deg, var(--xh-overlay-top) 0%, var(--xh-overlay-mid) 60%, var(--xh-overlay-bottom) 100%);
    box-sizing: border-box;
  }
  .xh *, .xh *::before, .xh *::after { box-sizing: border-box; }
  .xh a { color: inherit; text-decoration: none; }
  .xh ul { list-style: none; margin: 0; padding: 0; }

  .xh__inner {
    max-width: var(--xh-max);
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    align-items: stretch;
    gap: 40px;
  }

  /* ---------- logo ---------- */
  .xh .xh__logo {
    flex: 0 0 auto;
    align-self: flex-start;
    margin-top: 24px;
    display: block;
    line-height: 0;
  }
  .xh .xh__logo img { height: var(--xh-logo-h); width: auto; max-width: none; display: block; }

  /* ---------- right column ---------- */
  .xh__main { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; }

  .xh__top {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 32px;
    padding: 26px 0 22px;
    flex-wrap: wrap;
  }
  .xh__contact { font-size: 16px; font-weight: 500; white-space: nowrap; }
  .xh__contact span { margin-right: 6px; }
  .xh__phone { font-size: 18px; font-weight: 600; color: var(--xh-muted); letter-spacing: .5px; white-space: nowrap; }

  .xh__search { position: relative; display: flex; align-items: center; }
  .xh .xh__search-trigger {
    display: flex; align-items: center; gap: 12px; margin: 0;
    padding: 4px 4px 4px 14px; border-radius: 12px; cursor: pointer;
    background: rgba(0,0,0,.35); border: 1px solid rgba(255,255,255,.28); box-shadow: none;
    color: #fff; font: 600 12px/1 inherit; font-family: inherit; text-transform: none; letter-spacing: 0; min-height: 0;
    transition: border-color .2s;
  }
  .xh .xh__search-trigger:hover, .xh .xh__search.is-open .xh__search-trigger { border-color: var(--xh-accent); background: rgba(0,0,0,.35); color: #fff; }
  .xh__search-ico { width: 34px; height: 34px; border-radius: 50%; background: var(--xh-accent); display: grid; place-items: center; }
  .xh__search-ico svg { width: 15px; height: 15px; stroke: #fff; fill: none; stroke-width: 2.4; }

  /* dropdown search panel */
  .xh__search-panel {
    position: absolute; top: calc(100% + 16px); right: -40px; z-index: 5;
    width: 440px; max-width: calc(100vw - 32px); padding: 14px;
    background: rgba(14,14,14,.96); border: 1px solid rgba(255,255,255,.12); border-radius: 18px;
    box-shadow: 0 20px 50px rgba(0,0,0,.6);
    opacity: 0; visibility: hidden; transform: translateY(8px); transition: .2s;
  }
  .xh__search.is-open .xh__search-panel { opacity: 1; visibility: visible; transform: none; }
  .xh__search-form { display: flex; gap: 10px; margin: 0; }
  .xh .xh__search-form input[type=search] {
    flex: 1; min-width: 0; height: 46px; margin: 0; padding: 0 16px; border-radius: 12px;
    border: 1px solid rgba(255,255,255,.14); background: rgba(255,255,255,.08); color: #fff;
    font-size: 16px; outline: 0; box-shadow: none; -webkit-appearance: none; appearance: none;
  }
  .xh .xh__search-form input[type=search]::placeholder { color: rgba(255,255,255,.55); }
  .xh .xh__search-form input[type=search]:focus { border-color: var(--xh-accent); }
  .xh .xh__search-form input[type=search]::-webkit-search-cancel-button { -webkit-appearance: none; display: none; }
  .xh .xh__search-go {
    height: 46px; margin: 0; padding: 0 22px; border: 0; border-radius: 12px; cursor: pointer;
    background: linear-gradient(180deg, var(--xh-accent), var(--xh-accent-dark)); color: #fff;
    font-size: 14px; font-weight: 700; text-transform: none; letter-spacing: 0; box-shadow: none; min-height: 0;
  }
  .xh .xh__search-go:hover { filter: brightness(1.15); color: #fff; }

  /* live results */
  .xh__results { display: none; margin-top: 10px; max-height: 340px; overflow: auto; }
  .xh__results.has-content { display: block; }
  .xh__res { display: flex; align-items: center; gap: 12px; padding: 8px; border-radius: 10px; color: #fff; }
  .xh__res:hover { background: rgba(255,255,255,.08); }
  .xh__res img { width: 48px; height: 48px; flex: none; object-fit: cover; border-radius: 8px; background: #222; }
  .xh__res-t { flex: 1; min-width: 0; font-size: 13px; font-weight: 600; line-height: 1.3; }
  .xh__res-p { font-size: 13px; color: #9be3b6; white-space: nowrap; }
  .xh__res-p del { opacity: .5; margin-right: 4px; }
  .xh__res-p ins { text-decoration: none; }
  .xh__res-msg { padding: 10px 8px; font-size: 13px; color: #bbb; }
  .xh__res-all { display: block; margin-top: 4px; padding: 10px; text-align: center; border-top: 1px solid rgba(255,255,255,.1); font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #fff; }

  .xh__cta {
    display: inline-block;
    padding: 10px 26px;
    border-radius: 999px;
    font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;
    background: linear-gradient(180deg, var(--xh-accent), var(--xh-accent-dark));
    box-shadow: 0 0 0 1px rgba(255,255,255,.25) inset;
    color: #fff;
    transition: filter .2s;
  }
  .xh__cta:hover { filter: brightness(1.12); }

  /* ---------- nav ---------- */
  .xh__nav { border-top: 1px solid rgba(255,255,255,.22); position: relative; }
  .xh__menu { display: flex; align-items: center; justify-content: flex-end; gap: var(--xh-menu-gap); height: var(--xh-height-nav); }
  .xh .xh__menu > li { position: relative; height: 100%; display: flex; align-items: center; margin: 0; padding: 0; line-height: 1; }
  .xh__menu > li > a {
    font-size: 12px; font-weight: 600; letter-spacing: 1.6px; text-transform: uppercase;
    color: #e9e9e9; padding: 0 4px; height: 100%; display: flex; align-items: center; gap: 8px;
    transition: color .2s; white-space: nowrap; line-height: 1;
  }
  .xh__menu > li:hover > a,
  .xh__menu > li.current-menu-item > a,
  .xh__menu > li.current-menu-ancestor > a { color: #fff; }
  .xh__menu > li.current-menu-item > a,
  .xh__menu > li.current-menu-ancestor > a { font-weight: 700; }
  /* green tab crossing the nav line, behind the hovered / active item */
  .xh__menu > li::before {
    content: ""; position: absolute; left: -16px; right: -16px; top: -6px;   /* starts a little above the nav line */
    height: 40px;
    background: linear-gradient(180deg, var(--xh-accent) 0%, rgba(var(--xh-accent-rgb),0) 100%);
    opacity: 0; transition: opacity .25s;
    pointer-events: none;
  }
  .xh__menu > li:hover::before,
  .xh__menu > li:focus-within::before,
  .xh__menu > li.current-menu-item::before,
  .xh__menu > li.current-menu-ancestor::before { opacity: 1; }
  .xh__menu > li.menu-item-has-children > a::after { content: ""; width: 0; height: 0; border-left: 3.5px solid transparent; border-right: 3.5px solid transparent; border-top: 4px solid currentColor; }

  /* dropdown */
  .xh__menu .sub-menu {
    position: absolute; top: 100%; left: -16px; min-width: 220px;
    background: #101010; border-top: 2px solid var(--xh-accent);
    padding: 8px 0; opacity: 0; visibility: hidden; transform: translateY(8px);
    transition: .2s; box-shadow: 0 12px 30px rgba(0,0,0,.5);
  }
  .xh__menu > li:hover > .sub-menu,
  .xh__menu > li:focus-within > .sub-menu { opacity: 1; visibility: visible; transform: none; }
  .xh__menu .sub-menu li { margin: 0; padding: 0; }
  .xh__menu .sub-menu a { display: block; padding: 10px 20px; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; color: #ddd; }
  .xh__menu .sub-menu a:hover { background: var(--xh-accent); color: #fff; }

  /* hamburger */
  .xh__burger {
    display: none; margin-left: auto; align-self: center;
    width: 44px; height: 44px; border: 1px solid rgba(255,255,255,.3); border-radius: 8px;
    background: transparent; cursor: pointer; padding: 0; position: relative;
  }
  .xh__burger span, .xh__burger span::before, .xh__burger span::after {
    content: ""; position: absolute; left: 11px; right: 11px; height: 2px; background: #fff; transition: .25s;
  }
  .xh__burger span { top: 21px; }
  .xh__burger span::before { left: 0; right: 0; top: -7px; }
  .xh__burger span::after  { left: 0; right: 0; top: 7px; }
  .xh.is-open .xh__burger span { background: transparent; }
  .xh.is-open .xh__burger span::before { top: 0; transform: rotate(45deg); }
  .xh.is-open .xh__burger span::after  { top: 0; transform: rotate(-45deg); }

  /* ---------- tablet / mobile ---------- */
  @media (max-width: 1100px) {
    .xh__inner { gap: 24px; }
    .xh__top { gap: 18px; }
    .xh__menu { gap: 24px; }
    .xh__menu > li > a { letter-spacing: 1px; font-size: 11px; }
  }
  @media (max-width: 920px) {
    .xh__inner { flex-wrap: wrap; gap: 0; padding: 12px 16px; }
    .xh .xh__logo { margin-top: 0; }
    .xh .xh__logo img { height: 64px; }
    .xh__burger { display: block; }
    .xh__main { flex: 1 0 100%; order: 3; }
    .xh.is-open { background: rgba(0,0,0,.88); }
    .xh__top, .xh__nav { display: none; }
    .xh.is-open .xh__top { display: flex; justify-content: flex-start; padding: 16px 0; gap: 14px 22px; }
    .xh.is-open .xh__nav { display: block; }
    .xh__search { width: 100%; flex-direction: column; align-items: flex-start; }
    .xh__search-panel { position: static; width: 100%; max-width: none; margin-top: 10px; display: none; opacity: 1; visibility: visible; transform: none; }
    .xh__search.is-open .xh__search-panel { display: block; }
    .xh__menu { flex-direction: column; align-items: stretch; justify-content: flex-start; gap: 0; height: auto; }
    .xh .xh__menu > li { height: auto; flex-direction: column; align-items: stretch; border-bottom: 1px solid rgba(255,255,255,.1); }
    .xh__menu > li > a { padding: 14px 4px; }
    .xh__menu > li::before { display: none; }
    .xh__menu > li.current-menu-item > a,
    .xh__menu > li.current-menu-ancestor > a { box-shadow: inset 3px 0 0 var(--xh-accent); padding-left: 12px; }
    .xh__menu > li:hover > a { color: #fff; }
    .xh__menu .sub-menu { position: static; opacity: 1; visibility: visible; transform: none; box-shadow: none; border-top: 0; background: transparent; display: none; padding: 0 0 8px 14px; }
    .xh__menu > li.is-sub-open > .sub-menu { display: block; }
  }
CSS;

	$js = <<<'JS'
(function () {
  var h = document.getElementById('xh');
  if (!h) return;

  // mobile menu toggle
  var burger = h.querySelector('.xh__burger');
  burger.addEventListener('click', function () {
    var open = h.classList.toggle('is-open');
    burger.setAttribute('aria-expanded', open);
  });

  // mobile: tap "Products" to open its sub menu
  h.querySelectorAll('.xh__menu > li').forEach(function (li) {
    var sub = li.querySelector('.sub-menu');
    if (!sub) return;
    li.firstElementChild.addEventListener('click', function (e) {
      if (window.innerWidth <= 920) { e.preventDefault(); li.classList.toggle('is-sub-open'); }
    });
  });

  // search: click the box to open the panel, live product results while typing
  var s = document.getElementById('xhSearch');
  if (s) {
    var trigger = s.querySelector('.xh__search-trigger');
    var input = s.querySelector('input[type=search]');
    var box = document.getElementById('xhResults');
    var endpoint = s.getAttribute('data-endpoint');
    var timer, ctrl;

    function setOpen(o) {
      s.classList.toggle('is-open', o);
      trigger.setAttribute('aria-expanded', o);
      if (o) setTimeout(function () { input.focus(); }, 60);
    }
    trigger.addEventListener('click', function (e) { e.stopPropagation(); setOpen(!s.classList.contains('is-open')); });
    document.addEventListener('click', function (e) { if (!s.contains(e.target)) setOpen(false); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });

    function decode(t) { return new DOMParser().parseFromString(t || '', 'text/html').documentElement.textContent; }
    function price(p) {
      if (p.price_html) return p.price_html;
      var pr = p.prices; if (!pr) return '';
      var m = pr.currency_minor_unit || 0;
      return (pr.currency_prefix || '') + (parseInt(pr.price, 10) / Math.pow(10, m)).toFixed(m) + (pr.currency_suffix || '');
    }
    function clear() { box.innerHTML = ''; box.className = 'xh__results'; }
    function render(items) {
      box.innerHTML = '';
      if (!items.length) {
        var msg = document.createElement('div'); msg.className = 'xh__res-msg'; msg.textContent = 'No products found.';
        box.appendChild(msg);
      } else {
        items.forEach(function (p) {
          var a = document.createElement('a'); a.className = 'xh__res'; a.href = p.permalink;
          if (p.images && p.images[0]) { var im = document.createElement('img'); im.src = p.images[0].thumbnail || p.images[0].src; im.alt = ''; a.appendChild(im); }
          var t = document.createElement('span'); t.className = 'xh__res-t'; t.textContent = decode(p.name); a.appendChild(t);
          var pp = document.createElement('span'); pp.className = 'xh__res-p'; pp.innerHTML = price(p); a.appendChild(pp);
          box.appendChild(a);
        });
        var all = document.createElement('a'); all.className = 'xh__res-all'; all.href = '#'; all.textContent = 'View all results';
        all.addEventListener('click', function (e) { e.preventDefault(); s.querySelector('form').submit(); });
        box.appendChild(all);
      }
      box.className = 'xh__results has-content';
    }
    function search(q) {
      if (ctrl) ctrl.abort();
      ctrl = window.AbortController ? new AbortController() : null;
      var url = endpoint + (endpoint.indexOf('?') > -1 ? '&' : '?') + 'search=' + encodeURIComponent(q) + '&per_page=6';
      fetch(url, ctrl ? { signal: ctrl.signal } : {})
        .then(function (r) { return r.json(); })
        .then(function (d) { if (input.value.trim() === q && Array.isArray(d)) render(d); })
        .catch(function () {});
    }
    input.addEventListener('input', function () {
      clearTimeout(timer);
      var q = input.value.trim();
      if (q.length < 2) { clear(); return; }
      timer = setTimeout(function () { search(q); }, 250);
    });
  }
})();
JS;

	ob_start();
	?>
	<style><?php echo $css; // phpcs:ignore ?></style>

	<header class="xh" id="xh">
	  <div class="xh__inner">

	    <a class="xh__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Home">
	      <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
	    </a>

	    <button class="xh__burger" type="button" aria-label="Toggle menu" aria-expanded="false"><span></span></button>

	    <div class="xh__main">
	      <div class="xh__top">
	        <div class="xh__contact"><span>e-mail :</span><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></div>
	        <a class="xh__phone" href="<?php echo esc_attr( $phone_href ); ?>"><?php echo esc_html( $phone ); ?></a>

	        <div class="xh__search" id="xhSearch" data-endpoint="<?php echo esc_url( rest_url( 'wc/store/v1/products' ) ); ?>">
	          <button class="xh__search-trigger" type="button" aria-expanded="false" aria-controls="xhSearchPanel">
	            <span>Search Products</span>
	            <i class="xh__search-ico"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg></i>
	          </button>
	          <div class="xh__search-panel" id="xhSearchPanel">
	            <form class="xh__search-form" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
	              <input type="search" name="s" placeholder="Search Products..." autocomplete="off" aria-label="Search Products">
	              <input type="hidden" name="post_type" value="product">
	              <button class="xh__search-go" type="submit">Search</button>
	            </form>
	            <div class="xh__results" id="xhResults" aria-live="polite"></div>
	          </div>
	        </div>

	        <a class="xh__cta" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_text ); ?></a>
	      </div>

	      <nav class="xh__nav" aria-label="Main">
	        <?php echo $menu; // phpcs:ignore ?>
	      </nav>
	    </div>
	  </div>
	</header>

	<script><?php echo $js; // phpcs:ignore ?></script>
	<?php
	return ob_get_clean();
} );
