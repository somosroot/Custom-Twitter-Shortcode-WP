<?php
/*
  Plugin Name: Shorcodes RADD
  Plugin URI: https://github.com/soyroot/Custom-Twitter-Shortcode-WP
  Description: Muestra los ultimos post o tweets de un hashtag o usuario con el mismo formato a través de un shortcode.
  Version: 1.0
  Author: Agencia Root
  Author URI: http://www.agenciaroot.es
  Text Domain: radd
 */


//Carga del dominio de traducciones
load_plugin_textdomain('radd', false, dirname( plugin_basename( __FILE__ ) ).'/lang');


// integracion con twitter
require "lib/twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

define('CONSUMER_KEY', '');
define('CONSUMER_SECRET', '');
define('ACCESS_TOKEN', '');
define('ACCESS_TOKEN_SECRET', '');

// Funciones principales del plugin
function info_tc( $atts ) {

    extract( shortcode_atts( array(
        'tag' => 'TerritorioCreativo',
        'type' => 'post',
        'prefix' => '',
        'sufix' => '',
        'posts_per_page' => 2,
        'include_date' => true,
    ), $atts ) );
    

    $output = '';

    //Comprobamos que sea sólo del tipo de contenido post
    if($type == 'post'){
        
        $args = array(
            'post_type'=>'post',
            'posts_per_page' => $posts_per_page,
            'post_status' => 'publish',
            'orderby' => 'post_date',
            'order' => 'DESC',
        );

        $loop = new WP_Query(  $args );

        while ( $loop->have_posts() ) : $loop->the_post();

            $output .= '<h4><a href="'.get_permalink(get_the_ID()).'">'.get_the_title().'</a></h4>'; 
            $output .= '<p>'.substr(get_the_excerpt(),0,135).'...'.'</p>';
            // $output .= '<p>' . get_permalink() .'</p>';  //Oculto mientras definimos si hay algún acortador de urls disponile
            $time = get_the_time( 'Y-m-d h:i', get_the_ID() ); 
            $output .= '<p>'. $prefix . time_elapsed(strtotime( $time )) . $sufix .'</p>'; 

        endwhile;

        wp_reset_query();
    } else if($type == 'twitter' || $type == 'tweet'){

        $toa = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);

        $max_id = "";
        foreach (range(1, 10) as $i) { // up to 10 result pages

            $query = array(
                "q" => "#Niche since:2014-05-17 until:2014-05-19", // Change here
                "count" => 20,
                "result_type" => "recent",
                "max_id" => $max_id,
            );

            $results = $toa->get('search/tweets', $query);

            foreach ($results->statuses as $result) {
                print $output .= " [" . $result->created_at . "] " . $result->user->screen_name . ": " . $result->text . "\n";
                $max_id = $result->id_str; // Set max_id for the next search result page
            }
        }
    }


    return $output;
}
add_shortcode( 'info_tc', 'info_tc' );




//Formateo de fechas tipo "hace 9 meses"
function time_elapsed ($time)
{
    $time = time() - $time;

        $year   = array(__('year','radd'),__('years','radd'));
        $month  = array(__('month','radd'),__('months','radd'));
        $week   = array(__('week','radd'),__('weeks','radd'));
        $day    = array(__('day','radd'),__('days','radd'));
        $hour   = array(__('hour','radd'),__('hours','radd'));
        $minute = array(__('minute','radd'),__('minutes','radd'));
        $second = array(__('second','radd'),__('seconds','radd'));

    $tokens = array (
        31536000 => $year,
        2592000 => $month,
        604800 => $week,
        86400 => $day,
        3600 => $hour,
        60 => $minute,
        1 => $second,
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits.' '.(($numberOfUnits>1) ? $text[1] : $text[0]);
    }

}