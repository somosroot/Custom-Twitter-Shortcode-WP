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

//carga de configuración
require "class/shortcodes-radd.class.php";

// integracion con twitter
require "lib/twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

//Cargamos los tokes definidos en el settings de la administración
$options = get_option( 'scradd_option_name' );
define('CONSUMER_KEY', $options['consumer_key']);
define('CONSUMER_SECRET', $options['consumer_secret']);
define('ACCESS_TOKEN', $options['access_token']);
define('ACCESS_TOKEN_SECRET', $options['access_token_secret']);


// Funciones principales del plugin
function sc_radd( $atts ) {

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
            
            $time = get_the_time( 'Y-m-d h:i', get_the_ID() ); 

            $output .= '<p class="title"><strong><a href="'.get_permalink(get_the_ID()).'">'.get_the_title().'</a></strong></p>'; 
            $output .= '<p class="excerpt">'.substr(get_the_excerpt(),0,135).'...'.'</p>';
            $output .= '<p class="date">'. $prefix . time_elapsed(strtotime( $time )) . $sufix .'</p>'; 

        endwhile;

        wp_reset_query();
    } else if($type == 'twitter' || $type == 'tweet'){

        $toa = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);

        $max_id = "";

        $query = array(
            "q" => $tag, // Change here
            "count" => $posts_per_page,
            "result_type" => "recent",
            "max_id" => $max_id,
        );

        $results = $toa->get('search/tweets', $query);
        
        foreach ($results->statuses as $result) {

            $time = get_the_time( 'Y-m-d h:i', get_the_ID() ); 

            $output .= '<p class="title"><strong>'.$result->user->name .'</strong></p>'; 
            $output .= '<p class="excerpt">'. $result->text  .'</p>';
            $output .= '<p class="url">'. $result->url  .'</p>';
            $output .= '<p class="date">'. $prefix . time_elapsed(strtotime( $result->created_at )) . $sufix .'</p>'; 

            $max_id = $result->id_str; // Set max_id for the next search result page
        }
    }

    return $output;
}
add_shortcode( 'sc_radd', 'sc_radd' );

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