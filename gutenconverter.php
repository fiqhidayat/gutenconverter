<?php

/**
 *
 * @link              https://www.fiqhidayat.com
 * @since             1.0.0
 * @package           gutenconverter
 *
 * @wordpress-plugin
 * Plugin Name:       GutenConverter
 * Plugin URI:        https://www.fiqhidayat.com/
 * Description:       Simple WordPress plugin to Mass Convert Article To Gutenberg Block.
 * Version:           1.0.0
 * Author:            Taufik Hidayat
 * Author URI:        https://www.fiqhidayat.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gutenconverter
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function gcConverter($text){

    $text = str_replace('<p', '<!-- wp:paragraph --><p', $text);
    $text = str_replace('</p>', '</p><!-- /wp:paragraph -->', $text);

    $text = str_replace('<h1', '<!-- wp:heading {"level":1} --><h1', $text);
    $text = str_replace('</h1>', '</h1><!-- /wp:heading -->', $text);

    $text = str_replace('<h2', '<!-- wp:heading {"level":2} --><h2', $text);
    $text = str_replace('</h2>', '</h2><!-- /wp:heading -->', $text);

    $text = str_replace('<h3', '<!-- wp:heading {"level":3} --><h3', $text);
    $text = str_replace('</h3>', '</h3><!-- /wp:heading -->', $text);

    $text = str_replace('<h4', '<!-- wp:heading {"level":4} --><h4', $text);
    $text = str_replace('</h4>', '</h4><!-- /wp:heading -->', $text);

    $text = str_replace('<h5', '<!-- wp:heading {"level":5} --><h5', $text);
    $text = str_replace('</h5>', '</h5><!-- /wp:heading -->', $text);

    $text = str_replace('<h6', '<!-- wp:heading {"level":6} --><h6', $text);
    $text = str_replace('</h6>', '</h6><!-- /wp:heading -->', $text);

    $text = str_replace('<blockquote', '<!-- wp:quote --><blockquote', $text);
    $text = str_replace('</blockquote>', '</blockquote><!-- /wp:quote -->', $text);

    $text = str_replace('<pre', '<!-- wp:preformatted --><pre', $text);
    $text = str_replace('</pre>', '</pre><!-- /wp:preformatted -->', $text);

    $text = str_replace('<ol', '<!-- wp:list {"ordered":true} --><ol', $text);
    $text = str_replace('</ol>', '</ol><!-- /wp:list -->', $text);

    $text = str_replace('<ul', '<!-- wp:list --><ul', $text);
    $text = str_replace('</ul>', '</ul><!-- /wp:list -->', $text);

    return $text;
}


add_action( 'wp_ajax_gutenconvert', 'guten_convert' );
function guten_convert(){

	global $wpdb;

	check_ajax_referer( 'gutenconvert', 'nonce' );

	$paged = $_GET['paged'];

	$limit = $paged * 20;
	$offset = (int)$paged == 1 ? 0 : ($paged -1) * 20;
	$status = $_GET['status'];

	$sql = "SELECT ID, post_content FROM $wpdb->posts WHERE post_type = 'post'";
	if( $status ):
		$sql .= " AND post_status = '$status'";
	endif;
	$sql .= " ORDER BY ID DESC LIMIT $limit OFFSET $offset";

	$posts = $wpdb->get_results($sql);

	foreach( $posts as $post ):

		if( strpos( $post->post_content, '<!-- wp:paragraph -->' ) !== false) continue; // skip jika sudah ada tag gutenberg block

		$content = gcConverter($post->post_content); //tambahkan tag html gutenbrg block

		$wpdb->update( $wpdb->prefix.'posts', ['post_content' => $content], ['ID' => $post->ID] ); // update post content

	endforeach;
	echo 1;
	exit;
}

add_action('admin_head', 'custom_js_to_head');
function custom_js_to_head() {

	$paged = isset($_GET['paged']) ? $_GET['paged'] : 1;
	$status = isset($_GET['post_status']) ? $status : '';
    ?>
    <script>
    jQuery(function(){
        jQuery("body.post-type-post .wrap h1").append('<a class="page-title-action guten-convert">Convert To Gutenberg</a>');
    });

	jQuery(document).ready(function(){
        jQuery('.guten-convert').click(function(){
            jQuery.ajax({
                url:  ajaxurl,
                data: {
                    action: 'gutenconvert',
					nonce: '<?php echo wp_create_nonce('gutenconvert'); ?>',
					paged: '<?php echo $paged; ?>',
					status: '<?php echo $status; ?>'
                },
                beforeSend: function(){
					var content = '<div style="position:relative;width:100%;height:100%;">';
					content += '<div style="text-align:center;color:#ffffff;font-size:20px;font-weight:bold;line-height:25px;margin-top:25%;">Lagi Proses Om, silahkan ngopi dulu ...<br>Jangan di close, jangan di refresh selama proses</div>';
					content += '</div>';
					jQuery('body').append('<div id="gcloader" style="position:fixed;width:100%;height:100%;z-index:99999;top:0;left:0;background:rgba(0,0,0,0.9);">'+content+'</div>');
                },
				success: function(){
					jQuery('#gcloader').remove();
				}

            })
        })
    })
    </script>
    <?php
}
