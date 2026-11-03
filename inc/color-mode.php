<?php
/**
 * Light / dark mode.
 *
 * A tiny inline script in <head> sets <html data-theme="light|dark"> before the page is
 * painted, so there is no flash. The mode comes from, in order:
 *   1. the visitor's own choice (the header button, remembered in localStorage);
 *   2. the Theme Panel default: time of day, the device setting, or always light/dark.
 * Choosing the mode that automatic would pick anyway clears the saved choice, so the
 * visitor goes back to automatic.
 *
 * @package Khobor_Press
 */

defined( 'ABSPATH' ) || exit;

/** Print the mode script as early as possible. */
function np_color_mode_script() {
	$cfg = wp_json_encode( array(
		'm' => np_opt( 'color_mode' ),
		'f' => (int) np_opt( 'dark_from' ),
		'u' => (int) np_opt( 'dark_until' ),
		't' => np_opt( 'mode_toggle' ) ? 1 : 0,
	) );
	?>
<script>
(function(){var d=document.documentElement,C=<?php echo $cfg; // phpcs:ignore WordPress.Security.EscapeOutput ?>,K='npMode';
function auto(){if(C.m==='light'||C.m==='dark'){return C.m}
if(C.m==='system'){return matchMedia('(prefers-color-scheme:dark)').matches?'dark':'light'}
var h=new Date().getHours();return(C.f>C.u?(h>=C.f||h<C.u):(h>=C.f&&h<C.u))?'dark':'light'}
function saved(){if(!C.t){return ''}try{var s=localStorage.getItem(K);return(s==='dark'||s==='light')?s:''}catch(e){return ''}}
function sync(){var t=d.getAttribute('data-theme'),b=document.querySelectorAll('.mode-btn');for(var i=0;i<b.length;i++){b[i].setAttribute('aria-pressed',t==='dark'?'true':'false')}}
function set(t){d.setAttribute('data-theme',t);d.style.colorScheme=t;sync()}
set(saved()||auto());
document.addEventListener('DOMContentLoaded',sync);
document.addEventListener('click',function(e){var b=e.target.closest&&e.target.closest('.mode-btn');if(!b){return}
var t=d.getAttribute('data-theme')==='dark'?'light':'dark';
try{if(t===auto()){localStorage.removeItem(K)}else{localStorage.setItem(K,t)}}catch(x){}
set(t)});
})();
</script>
	<?php
}
add_action( 'wp_head', 'np_color_mode_script', 1 );

/** The header button that switches the mode. */
function np_mode_button() {
	if ( ! np_opt( 'mode_toggle' ) ) {
		return;
	}
	printf(
		'<button class="icon-btn mode-btn" type="button" aria-pressed="false" aria-label="%1$s" title="%1$s"><span class="mi mi-moon">%2$s</span><span class="mi mi-sun">%3$s</span></button>',
		esc_attr__( 'Dark mode', 'khobor-press' ),
		np_icon( 'moon', 20 ), // phpcs:ignore WordPress.Security.EscapeOutput
		np_icon( 'sun', 20 ) // phpcs:ignore WordPress.Security.EscapeOutput
	);
}
