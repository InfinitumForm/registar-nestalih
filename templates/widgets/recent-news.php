<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $missing_response;

do_action('registar_nestalih_before_widget_news', $missing_response);

$listings = new WP_Query();
$listings->query('post_type=missing-persons-news&posts_per_page=' . $missing_response['posts_per_page'] );

if( $listings->found_posts > 0 ) : ?>
	<ul class="missing-persons-news-widget">
	<?php while ($listings->have_posts()) : $listings->the_post(); ?>
		<li>
			<?php if( has_post_thumbnail($listings->ID) ) :
				echo get_the_post_thumbnail($listings->ID, 'missing_persons_news_widget_thumb');
			else: ?>
			<div class="noThumb"></div>
			<?php endif; ?>
			<a href="<?php echo esc_url( get_permalink() ); ?>">
				<?php echo esc_html( mb_strimwidth(get_the_title(), 0, 160, '...') ); ?>
			</a>
			<span><?php echo get_the_date(); ?></span>
		</li>
	<?php endwhile; wp_reset_postdata();  ?>
	</ul>
<?php else : ?>
	<p style="padding:25px;"><?php _e('No News Found', 'registar-nestalih') ?></p>
<?php endif;

do_action('registar_nestalih_after_widget_news', $missing_response);