<?php
/**
 * Plugin Name: Digital Wonders Works
 * Description: "Our Works" portfolio with AJAX service tabs. Add a Work in the admin, pick its Service, set the Work image, then place the [dw_works] shortcode anywhere.
 * Version:     1.0.0
 * Author:      Digital Wonders
 * Text Domain: dw-works
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DW_WORKS_CPT', 'dw_work' );
define( 'DW_WORKS_TAX', 'dw_service' );

/* ------------------------------------------------------------------
 * Default services (created on activation, editable under Works → Services)
 * ------------------------------------------------------------------ */
function dw_works_default_services() {
	return array(
		'exhibition-stalls'    => 'Exhibition Stalls',
		'flag-posts'           => 'Flag Posts',
		'dealer-boards'        => 'Dealer Boards',
		'hoardings'            => 'Hoardings',
		'led-neon-signage'     => 'LED & Neon Signage',
		'popsicles-lollipops'  => 'Popsicles & Lollipops',
		'vehicle-branding'     => 'Vehicle Branding',
		'light-boards'         => 'Light Boards',
	);
}

/* ------------------------------------------------------------------
 * Post type + taxonomy
 * ------------------------------------------------------------------ */
function dw_works_register() {
	register_post_type(
		DW_WORKS_CPT,
		array(
			'labels'        => array(
				'name'                  => 'Works',
				'singular_name'         => 'Work',
				'add_new'               => 'Add New Work',
				'add_new_item'          => 'Add New Work',
				'edit_item'             => 'Edit Work',
				'new_item'              => 'New Work',
				'view_item'             => 'View Work',
				'search_items'          => 'Search Works',
				'not_found'             => 'No works found',
				'not_found_in_trash'    => 'No works found in Trash',
				'all_items'             => 'All Works',
				'menu_name'             => 'Works',
				'featured_image'        => 'Work image',
				'set_featured_image'    => 'Set work image',
				'remove_featured_image' => 'Remove work image',
				'use_featured_image'    => 'Use as work image',
			),
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'show_in_rest'  => false,
			'menu_position' => 21,
			'menu_icon'     => 'dashicons-format-gallery',
			'supports'      => array( 'title', 'thumbnail' ),
			'has_archive'   => false,
			'rewrite'       => false,
		)
	);

	register_taxonomy(
		DW_WORKS_TAX,
		DW_WORKS_CPT,
		array(
			'labels'            => array(
				'name'          => 'Services',
				'singular_name' => 'Service',
				'menu_name'     => 'Services',
				'all_items'     => 'All Services',
				'edit_item'     => 'Edit Service',
				'add_new_item'  => 'Add New Service',
				'search_items'  => 'Search Services',
			),
			'public'            => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'hierarchical'      => false,
			'show_in_rest'      => false,
			'meta_box_cb'       => false, // we use our own dropdown below
			'rewrite'           => false,
		)
	);
}
add_action( 'init', 'dw_works_register' );

// Classic editor for this post type (keeps the Service dropdown simple & reliable).
add_filter(
	'use_block_editor_for_post_type',
	function ( $use, $type ) {
		return DW_WORKS_CPT === $type ? false : $use;
	},
	10,
	2
);

function dw_works_activate() {
	dw_works_register();
	foreach ( dw_works_default_services() as $slug => $name ) {
		if ( ! get_term_by( 'slug', $slug, DW_WORKS_TAX ) ) {
			wp_insert_term( $name, DW_WORKS_TAX, array( 'slug' => $slug ) );
		}
	}
}
register_activation_hook( __FILE__, 'dw_works_activate' );

/* ------------------------------------------------------------------
 * Admin: Service dropdown on the Work edit screen
 * ------------------------------------------------------------------ */
function dw_works_add_box() {
	add_meta_box( 'dw_service_box', 'Service', 'dw_works_box', DW_WORKS_CPT, 'side', 'high' );
}
add_action( 'add_meta_boxes', 'dw_works_add_box' );

function dw_works_box( $post ) {
	wp_nonce_field( 'dw_works_save', 'dw_works_nonce' );

	$terms = get_terms(
		array(
			'taxonomy'   => DW_WORKS_TAX,
			'hide_empty' => false,
			'orderby'    => 'term_id',
		)
	);
	$cur   = wp_get_object_terms( $post->ID, DW_WORKS_TAX, array( 'fields' => 'ids' ) );
	$cur   = ( $cur && ! is_wp_error( $cur ) ) ? (int) $cur[0] : 0;

	echo '<p>Select the service this work belongs to.</p>';
	echo '<select name="dw_service_term" style="width:100%">';
	echo '<option value="0">— Select service —</option>';
	if ( $terms && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $t ) {
			printf(
				'<option value="%d"%s>%s</option>',
				(int) $t->term_id,
				selected( $cur, $t->term_id, false ),
				esc_html( $t->name )
			);
		}
	}
	echo '</select>';
	echo '<p class="description">Then set the <strong>Work image</strong> (right sidebar). Works without an image are not shown on the site.</p>';
}

function dw_works_save( $post_id ) {
	if ( ! isset( $_POST['dw_works_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dw_works_nonce'] ) ), 'dw_works_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$term_id = isset( $_POST['dw_service_term'] ) ? absint( $_POST['dw_service_term'] ) : 0;
	wp_set_object_terms( $post_id, $term_id ? array( $term_id ) : array(), DW_WORKS_TAX );
}
add_action( 'save_post_' . DW_WORKS_CPT, 'dw_works_save' );

// Admin list: thumbnail column.
add_filter(
	'manage_' . DW_WORKS_CPT . '_posts_columns',
	function ( $cols ) {
		$new = array();
		foreach ( $cols as $key => $label ) {
			if ( 'title' === $key ) {
				$new['dw_thumb'] = 'Image';
			}
			$new[ $key ] = $label;
		}
		return $new;
	}
);
add_action(
	'manage_' . DW_WORKS_CPT . '_posts_custom_column',
	function ( $col, $post_id ) {
		if ( 'dw_thumb' === $col ) {
			$img = get_the_post_thumbnail( $post_id, array( 70, 70 ) );
			echo $img ? $img : '—'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	},
	10,
	2
);

/* ------------------------------------------------------------------
 * Query + markup for the work cards
 * ------------------------------------------------------------------ */
function dw_works_items( $term_id, $page, $per ) {
	$args = array(
		'post_type'      => DW_WORKS_CPT,
		'post_status'    => 'publish',
		'posts_per_page' => $per,
		'paged'          => $page,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'meta_query'     => array(
			array(
				'key'     => '_thumbnail_id',
				'compare' => 'EXISTS',
			),
		),
	);
	if ( $term_id ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => DW_WORKS_TAX,
				'field'    => 'term_id',
				'terms'    => $term_id,
			),
		);
	}

	$q    = new WP_Query( $args );
	$html = '';

	while ( $q->have_posts() ) {
		$q->the_post();
		$id   = get_the_ID();
		$full = get_the_post_thumbnail_url( $id, 'full' );
		if ( ! $full ) {
			continue;
		}
		$title = get_the_title();
		$img   = get_the_post_thumbnail(
			$id,
			'large',
			array(
				'class'    => 'dww-img',
				'loading'  => 'lazy',
				'decoding' => 'async',
				'alt'      => wp_strip_all_tags( $title ),
			)
		);
		$terms = get_the_terms( $id, DW_WORKS_TAX );
		$svc   = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';

		$html .= sprintf(
			'<a class="dww-item" href="%1$s" data-full="%1$s" data-title="%2$s" data-service="%3$s">%4$s<span class="dww-cap">%5$s<strong>%6$s</strong></span><span class="dww-zoom" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></span></a>',
			esc_url( $full ),
			esc_attr( $title ),
			esc_attr( $svc ),
			$img,
			$svc ? '<small>' . esc_html( $svc ) . '</small>' : '',
			esc_html( $title )
		);
	}
	wp_reset_postdata();

	return array(
		'html' => $html,
		'more' => $page < (int) $q->max_num_pages,
	);
}

/* ------------------------------------------------------------------
 * AJAX endpoint (read-only, public → no nonce so cached pages keep working)
 * ------------------------------------------------------------------ */
function dw_works_ajax() {
	// phpcs:disable WordPress.Security.NonceVerification
	$term = isset( $_POST['term'] ) ? absint( $_POST['term'] ) : 0;
	$page = isset( $_POST['page'] ) ? max( 1, absint( $_POST['page'] ) ) : 1;
	$per  = isset( $_POST['per'] ) ? min( 24, max( 1, absint( $_POST['per'] ) ) ) : 9;
	// phpcs:enable
	wp_send_json_success( dw_works_items( $term, $page, $per ) );
}
add_action( 'wp_ajax_dw_works', 'dw_works_ajax' );
add_action( 'wp_ajax_nopriv_dw_works', 'dw_works_ajax' );

/* ------------------------------------------------------------------
 * Shortcode: [dw_works per_page="9" eyebrow="Our portfolio" title="Our Works"]
 * ------------------------------------------------------------------ */
function dw_works_shortcode( $atts ) {
	static $assets_printed = false;

	$a   = shortcode_atts(
		array(
			'per_page' => 9,
			'eyebrow'  => 'Our portfolio',
			'title'    => 'Our Works',
		),
		$atts,
		'dw_works'
	);
	$per = max( 1, min( 24, (int) $a['per_page'] ) );

	$terms = get_terms(
		array(
			'taxonomy'   => DW_WORKS_TAX,
			'hide_empty' => true,
			'orderby'    => 'term_id',
		)
	);
	if ( is_wp_error( $terms ) ) {
		$terms = array();
	}
	$first = dw_works_items( 0, 1, $per );

	ob_start();

	if ( ! $assets_printed ) {
		$assets_printed = true;
		echo '<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">';
		echo '<style>' . dw_works_css() . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	?>
	<section class="dww" data-ajax="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-per="<?php echo (int) $per; ?>">
		<div class="dww-wrap">

			<div class="dww-head">
				<?php if ( $a['eyebrow'] ) : ?>
					<span class="dww-eyebrow"><?php echo esc_html( $a['eyebrow'] ); ?></span>
				<?php endif; ?>
				<h2 class="dww-title"><?php echo esc_html( $a['title'] ); ?></h2>
			</div>

			<?php if ( $terms ) : ?>
				<div class="dww-tabs" role="tablist" aria-label="Filter works by service">
					<button type="button" class="dww-tab is-active" role="tab" data-term="0">All</button>
					<?php foreach ( $terms as $t ) : ?>
						<button type="button" class="dww-tab" role="tab" data-term="<?php echo (int) $t->term_id; ?>"><?php echo esc_html( $t->name ); ?></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="dww-grid" aria-live="polite">
				<?php
				if ( $first['html'] ) {
					echo $first['html']; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in dw_works_items()
				} else {
					echo '<p class="dww-empty">Projects coming soon.</p>';
				}
				?>
			</div>

			<div class="dww-foot">
				<button type="button" class="dww-more" <?php echo $first['more'] ? '' : 'hidden'; ?>>Load more</button>
			</div>

		</div>
	</section>
	<script><?php echo dw_works_js(); // phpcs:ignore WordPress.Security.EscapeOutput ?></script>
	<?php
	return ob_get_clean();
}
add_shortcode( 'dw_works', 'dw_works_shortcode' );

/* ------------------------------------------------------------------
 * CSS
 * ------------------------------------------------------------------ */
function dw_works_css() {
	return <<<'CSS'
.dww,.dww *{box-sizing:border-box;}
.dww{background:#fff!important;font-family:'Poppins',sans-serif!important;color:#444!important;width:100%!important;}
.dww-wrap{max-width:1280px!important;margin:0 auto!important;padding:24px 20px 80px!important;}

/* Head */
.dww-head{margin-bottom:28px!important;}
.dww-eyebrow{display:inline-block!important;margin:0 0 14px!important;padding:6px 14px!important;background:#f5f5f5!important;border:1px solid #e0e0e0!important;border-radius:50px!important;color:#111!important;font-size:12px!important;font-weight:500!important;letter-spacing:1.2px!important;text-transform:uppercase!important;}
.dww-title{margin:0!important;padding:0!important;color:#111!important;font-family:'Poppins',sans-serif!important;font-size:clamp(28px,3.6vw,44px)!important;font-weight:600!important;line-height:1.15!important;letter-spacing:-.5px!important;text-transform:none!important;}

/* Tabs */
.dww-tabs{display:flex!important;flex-wrap:wrap!important;gap:10px!important;margin:0 0 32px!important;}
.dww-tab{-webkit-appearance:none!important;appearance:none!important;flex:none!important;display:inline-block!important;width:auto!important;height:auto!important;min-height:0!important;min-width:0!important;margin:0!important;padding:9px 20px!important;background:#f5f5f5!important;color:#111!important;border:1px solid #e0e0e0!important;border-radius:50px!important;font-family:'Poppins',sans-serif!important;font-size:14px!important;font-weight:500!important;line-height:1.3!important;letter-spacing:0!important;text-transform:none!important;box-shadow:none!important;cursor:pointer!important;transition:background .25s,color .25s,border-color .25s,transform .25s!important;}
.dww-tab:hover{background:#e9e9e9!important;color:#111!important;transform:translateY(-1px)!important;}
.dww-tab.is-active,.dww-tab.is-active:hover{background:#000!important;border-color:#000!important;color:#fff!important;transform:none!important;}

/* Grid */
.dww-grid{display:grid!important;grid-template-columns:repeat(3,1fr)!important;gap:20px!important;transition:opacity .2s ease,transform .2s ease;}
.dww-grid.is-out{opacity:0;transform:translateY(8px);}
.dww-empty{grid-column:1 / -1;margin:0!important;padding:48px 0!important;text-align:center!important;color:#777!important;font-size:15px!important;}

/* Card */
.dww-item{position:relative!important;display:block!important;aspect-ratio:4/3;overflow:hidden!important;border-radius:24px!important;background:#f0f0f0!important;text-decoration:none!important;color:#fff!important;isolation:isolate;-webkit-mask-image:-webkit-radial-gradient(white,black);}
.dww-item:link,.dww-item:visited,.dww-item:hover,.dww-item:focus{color:#fff!important;text-decoration:none!important;}
.dww-item img.dww-img{position:absolute!important;inset:0!important;width:100%!important;height:100%!important;max-width:none!important;object-fit:cover!important;display:block!important;transition:transform .8s cubic-bezier(.22,.61,.36,1)!important;}
.dww-item:hover img.dww-img,.dww-item:focus-visible img.dww-img{transform:scale(1.08)!important;}
.dww-item::after{content:"";position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.72) 0%,rgba(0,0,0,.05) 55%,rgba(0,0,0,0) 100%);opacity:0;transition:opacity .4s ease;z-index:1;}
.dww-item:hover::after,.dww-item:focus-visible::after{opacity:1;}
.dww-cap{position:absolute!important;left:0!important;right:0!important;bottom:0!important;z-index:2!important;display:flex!important;flex-direction:column!important;gap:2px!important;padding:20px 22px!important;opacity:0;transform:translateY(14px);transition:opacity .4s ease,transform .4s ease;}
.dww-cap small{color:#d6d6d6!important;font-size:12px!important;font-weight:500!important;letter-spacing:.8px!important;text-transform:uppercase!important;}
.dww-cap strong{color:#fff!important;font-size:17px!important;font-weight:600!important;line-height:1.3!important;}
.dww-item:hover .dww-cap,.dww-item:focus-visible .dww-cap{opacity:1;transform:none;}
.dww-zoom{position:absolute!important;top:16px!important;right:16px!important;z-index:2!important;display:flex!important;align-items:center!important;justify-content:center!important;width:40px!important;height:40px!important;border-radius:50%!important;background:#fff!important;color:#000!important;transform:scale(.4) rotate(-45deg);opacity:0;transition:transform .45s cubic-bezier(.34,1.56,.64,1),opacity .3s;}
.dww-item:hover .dww-zoom,.dww-item:focus-visible .dww-zoom{transform:none;opacity:1;}

/* New items animate in */
@keyframes dwwIn{from{opacity:0;transform:translateY(22px) scale(.97);}to{opacity:1;transform:none;}}
.dww-item.dww-new{animation:dwwIn .6s cubic-bezier(.22,.61,.36,1) both;animation-delay:calc(var(--i,0) * 70ms);}

/* Load more */
.dww-foot{display:flex!important;justify-content:center!important;margin-top:36px!important;}
.dww-more{-webkit-appearance:none!important;appearance:none!important;display:inline-block!important;width:auto!important;height:auto!important;min-height:0!important;margin:0!important;padding:9px 28px!important;background:#fff!important;color:#111!important;border:1px solid #111!important;border-radius:50px!important;font-family:'Poppins',sans-serif!important;font-size:15px!important;font-weight:500!important;line-height:1.3!important;text-transform:none!important;letter-spacing:0!important;box-shadow:none!important;cursor:pointer!important;transition:background .25s,color .25s,transform .25s!important;}
.dww-more:hover{background:#000!important;color:#fff!important;transform:translateY(-2px)!important;}
.dww-more[hidden]{display:none!important;}
.dww.is-loading .dww-more{opacity:.6!important;pointer-events:none!important;}

/* Lightbox */
.dww-lb{position:fixed;inset:0;z-index:2147483000;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.9);padding:24px;font-family:'Poppins',sans-serif;}
.dww-lb.is-open{display:flex;animation:dwwFade .3s ease;}
@keyframes dwwFade{from{opacity:0;}to{opacity:1;}}
.dww-lb-fig{margin:0;max-width:92vw;text-align:center;}
.dww-lb-fig img{display:block;max-width:92vw;max-height:76vh;width:auto;height:auto;margin:0 auto;border-radius:20px;transition:opacity .25s ease;}
.dww-lb-fig img.is-swap{opacity:0;}
.dww-lb-fig figcaption{margin-top:16px;color:#fff;font-size:15px;font-weight:500;line-height:1.5;}
.dww-lb-fig figcaption small{display:block;color:#bdbdbd;font-size:12px;letter-spacing:.8px;text-transform:uppercase;}
.dww-lb button{-webkit-appearance:none;appearance:none;position:absolute;display:flex;align-items:center;justify-content:center;width:46px;height:46px;min-width:46px;min-height:46px;margin:0;padding:0;background:rgba(255,255,255,.14);color:#fff;border:0;border-radius:50%;font-size:0;line-height:1;cursor:pointer;box-shadow:none;transition:background .2s,color .2s;}
.dww-lb button:hover{background:#fff;color:#000;}
.dww-lb button svg{display:block;}
.dww-lb-x{top:18px;right:18px;}
.dww-lb-prev{left:18px;top:50%;margin-top:-23px!important;}
.dww-lb-next{right:18px;top:50%;margin-top:-23px!important;}

/* Tablet */
@media (max-width:1024px){
  .dww-grid{grid-template-columns:repeat(2,1fr)!important;}
  .dww-wrap{padding:16px 20px 64px!important;}
}

/* Mobile */
@media (max-width:640px){
  .dww-wrap{padding:8px 14px 52px!important;}
  .dww-grid{grid-template-columns:1fr!important;gap:14px!important;}
  .dww-item{border-radius:20px!important;}
  .dww-tabs{flex-wrap:nowrap!important;overflow-x:auto!important;margin:0 -14px 24px!important;padding:2px 14px 6px!important;-webkit-overflow-scrolling:touch;scrollbar-width:none;}
  .dww-tabs::-webkit-scrollbar{display:none;}
  .dww-lb button{width:40px;height:40px;min-width:40px;min-height:40px;}
  .dww-lb-prev{left:8px;}
  .dww-lb-next{right:8px;}
}

/* Touch devices: captions always visible */
@media (hover:none){
  .dww-item::after{opacity:1;}
  .dww-cap{opacity:1;transform:none;}
  .dww-zoom{display:none!important;}
}

@media (prefers-reduced-motion:reduce){
  .dww-item.dww-new{animation:none;}
  .dww-item img.dww-img,.dww-cap,.dww-zoom,.dww-grid{transition:none!important;}
}
CSS;
}

/* ------------------------------------------------------------------
 * JS (tabs via AJAX, load more, lightbox)
 * ------------------------------------------------------------------ */
function dw_works_js() {
	return <<<'JS'
(function () {
  'use strict';
  if (window.__dwWorksInit) { if (window.__dwWorksScan) window.__dwWorksScan(); return; }
  window.__dwWorksInit = true;

  /* ---------- Lightbox (one shared instance) ---------- */
  var lb, lbImg, lbCap, list = [], cur = 0;

  function buildLb() {
    if (lb) return;
    lb = document.createElement('div');
    lb.className = 'dww-lb';
    lb.setAttribute('role', 'dialog');
    lb.setAttribute('aria-modal', 'true');
    lb.innerHTML =
      '<button type="button" class="dww-lb-x" aria-label="Close"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg></button>' +
      '<button type="button" class="dww-lb-prev" aria-label="Previous"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></button>' +
      '<button type="button" class="dww-lb-next" aria-label="Next"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></button>' +
      '<figure class="dww-lb-fig"><img alt=""><figcaption></figcaption></figure>';
    document.body.appendChild(lb);
    lbImg = lb.querySelector('img');
    lbCap = lb.querySelector('figcaption');

    lb.querySelector('.dww-lb-x').addEventListener('click', closeLb);
    lb.querySelector('.dww-lb-prev').addEventListener('click', function () { show(cur - 1); });
    lb.querySelector('.dww-lb-next').addEventListener('click', function () { show(cur + 1); });
    lb.addEventListener('click', function (e) { if (e.target === lb) closeLb(); });

    document.addEventListener('keydown', function (e) {
      if (!lb.classList.contains('is-open')) return;
      if (e.key === 'Escape') closeLb();
      if (e.key === 'ArrowLeft') show(cur - 1);
      if (e.key === 'ArrowRight') show(cur + 1);
    });

    var sx = 0;
    lb.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend', function (e) {
      var dx = e.changedTouches[0].clientX - sx;
      if (Math.abs(dx) > 50) show(cur + (dx < 0 ? 1 : -1));
    }, { passive: true });
  }

  function show(i) {
    if (!list.length) return;
    cur = (i + list.length) % list.length;
    var a = list[cur];
    lbImg.classList.add('is-swap');
    var src = a.getAttribute('data-full');
    var pre = new Image();
    pre.onload = pre.onerror = function () {
      lbImg.src = src;
      lbImg.alt = a.getAttribute('data-title') || '';
      lbImg.classList.remove('is-swap');
    };
    pre.src = src;
    var svc = a.getAttribute('data-service');
    lbCap.innerHTML = '';
    if (svc) { var s = document.createElement('small'); s.textContent = svc; lbCap.appendChild(s); }
    lbCap.appendChild(document.createTextNode(a.getAttribute('data-title') || ''));
    lb.querySelector('.dww-lb-prev').style.display = list.length > 1 ? '' : 'none';
    lb.querySelector('.dww-lb-next').style.display = list.length > 1 ? '' : 'none';
  }

  function openLb(items, index) {
    buildLb();
    list = items;
    lb.classList.add('is-open');
    document.documentElement.style.overflow = 'hidden';
    show(index);
  }

  function closeLb() {
    lb.classList.remove('is-open');
    document.documentElement.style.overflow = '';
  }

  /* ---------- Each works section ---------- */
  function init(root) {
    if (root.__dwInit) return;
    root.__dwInit = true;

    var ajax = root.getAttribute('data-ajax');
    var per  = root.getAttribute('data-per') || '9';
    var grid = root.querySelector('.dww-grid');
    var more = root.querySelector('.dww-more');
    var tabs = root.querySelectorAll('.dww-tab');
    var state = { term: 0, page: 1, busy: false };

    function render(data, append, term, page) {
      if (!append) grid.innerHTML = '';
      var tmp = document.createElement('div');
      tmp.innerHTML = data.html || '';
      var items = [].slice.call(tmp.children);
      items.forEach(function (el, i) {
        el.classList.add('dww-new');
        el.style.setProperty('--i', i);
        grid.appendChild(el);
      });
      if (!grid.children.length) {
        grid.innerHTML = '<p class="dww-empty">No works in this category yet.</p>';
      }
      state.term = term;
      state.page = page;
      more.hidden = !data.more;
      grid.classList.remove('is-out');
    }

    function finish() {
      state.busy = false;
      root.classList.remove('is-loading');
      more.textContent = 'Load more';
    }

    function load(term, page, append) {
      if (state.busy) return;
      state.busy = true;
      root.classList.add('is-loading');
      if (append) more.textContent = 'Loading…'; else grid.classList.add('is-out');

      var t0 = Date.now();
      var fd = new FormData();
      fd.append('action', 'dw_works');
      fd.append('term', term);
      fd.append('page', page);
      fd.append('per', per);

      fetch(ajax, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (!res || !res.success) throw new Error('bad response');
          var wait = append ? 0 : Math.max(0, 220 - (Date.now() - t0));
          setTimeout(function () { render(res.data, append, term, page); finish(); }, wait);
        })
        .catch(function () {
          grid.classList.remove('is-out');
          if (!append) grid.innerHTML = '<p class="dww-empty">Couldn’t load works. Please try again.</p>';
          finish();
        });
    }

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        if (state.busy || tab.classList.contains('is-active')) return;
        tabs.forEach(function (t) { t.classList.remove('is-active'); });
        tab.classList.add('is-active');
        if (tab.scrollIntoView) tab.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' });
        load(parseInt(tab.getAttribute('data-term'), 10) || 0, 1, false);
      });
    });

    more.addEventListener('click', function () { load(state.term, state.page + 1, true); });

    grid.addEventListener('click', function (e) {
      var a = e.target.closest ? e.target.closest('.dww-item') : null;
      if (!a || !grid.contains(a)) return;
      e.preventDefault();
      var items = [].slice.call(grid.querySelectorAll('.dww-item'));
      openLb(items, items.indexOf(a));
    });
  }

  function scan() {
    [].slice.call(document.querySelectorAll('.dww')).forEach(init);
  }
  window.__dwWorksScan = scan;

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', scan);
  else scan();
})();
JS;
}
