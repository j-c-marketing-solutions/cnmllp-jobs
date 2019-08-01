<?php
/*
	Plugin Name: CNM LLP Lever.co Integration
	Plugin URI: #
	Description: CNM LLP Lever.co API Integration via shortcode.
	Text Domain: ns-plugin-template
	Author: Jerry Rodriguez
	Author URI: https://www.jcmarketingsolutuions.com
	Version: 1.0.1
	Tested up to: 4.9.8
	License: GPLv2 or later
*/

/*
	Copyright 2019 J & C Marketing Solutions
	
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // exit if accessed directly!
}

//require_once(plugin_dir_path(__FILE__).'js/cnm-custom.js');
//require_once(plugin_dir_path(__FILE__).'css/cnm-custom.css');
//require_once(plugin_dir_path(__FILE__).'ns-sidebar/ns-sidebar.php');

//Register & enqueue scripts
// include custom jQuery
function cnm_scripts() {

    wp_enqueue_script('jquery-block-ui', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.blockUI/2.70/jquery.blockUI.js', array('jquery'), null, true);
	wp_enqueue_script('cnmllp-scripts', plugin_dir_url( __FILE__ ) .'js/cnm-custom.js', array('jquery'), null, true);
	wp_enqueue_style('cnmllp-style', plugin_dir_url( __FILE__ ) .'css/cnm-custom.css');

    wp_localize_script( 'cnmllp-scripts', 'my_ajax_object',
        array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

}

add_action('wp_enqueue_scripts', 'cnm_scripts');



//CNMllp shortcode
	 // Add Shortcode
	function cnm_lever_shortcode_init() {
		function cnm_lever_shortcode(){


            global $wpdb;

            $table_name = $wpdb->prefix . 'cnmllp_jobs';

            $api_settings = get_option( 'lever_api_settings' );

            $paginate = isset($api_settings['pagination_on']) ? ' LIMIT ' . $api_settings['pagination_num'] : '';

            //truncate previous datas..
            $jobs = $wpdb->get_results('SELECT * FROM ' . $table_name . $paginate); //. ' ORDER BY RAND()');

            //counting total for pagination..
            $total_jobs = $wpdb->get_results('SELECT * FROM ' . $table_name);

            $_jobs = '';
            $cat = [];
            $team = [];

            if(!empty($jobs)) {


                foreach ($jobs as $job) {

                    $_jobs .= '<div class="job">';
                    $_jobs .= '<div class="job-details">';
                    $_jobs .= '<a class="job-title" href="'.$job->link.'">'.$job->title.'</a>';
                    $_jobs .= '<p class="tags"><span>'.$job->team.'</span><span>'.$job->location.'</span><span>'.$job->commitment.'</span></p>';
                    $_jobs .= '</div>';
                    $_jobs .= '<div class="job-btn">';
                    $_jobs .= '<a class="cnm-btn" href="'.$job->link.'">Learn more</a>';
                    $_jobs .= '</div>';
                    $_jobs .= '</div>';

                }


            } else {

                return "No jobs found! Please make sure you have set the company string correctly in the settings or the company has jobs posted in lever.";

            }


            if(!empty($total_jobs)) {


                foreach ($total_jobs as $job) {

                    if(!in_array($job->location,$cat)) {
                        $cat[] = $job->location;
                    }

                    if(!in_array($job->team,$team)) {
                        $team[] = $job->team;
                    }

                }


            }


            $locations = [];

            if(!empty($cat)) {

                foreach ($cat as $c) {

                    $ct = explode(",",$c);

                    $locations[$c] = $ct[1] . " - " .$ct[0];

                }

                sort($locations);

            }

            $teams = [];

            if(!empty($team)) {

                foreach ($team as $t) {

                    $teams[] = $t;

                }

                sort($teams);

            }

            if(!isset($api_settings['company']) || empty($api_settings['company'])) {

                return "Please set company string in the Settings!";

            }

			$content = '<section>';
			$content.= '<div class="container" id="jobs-container">';
    		//$content.= '<h3>Open jobs</h3>';
    		$content.= '<div class="jobs-teams">';
    		$content.= '</div>';
            $content.= '<div id="job_searchbar" class="row">';
            $content.= '<div class="col-search">
                            <select id="team-filter">
                            <option value="">Practice Areas</option>
                            ';

            if(!empty($teams)) {

                foreach ($teams as $label) {


                    $content.= '<option value="'.$label.'">' . $label . '</option>';

                }

            }

            $content.= '    </select>
                        </div>';
            $content.= '<div class="col-search">
                            <select id="location-filter">
                            <option value="">Location</option>
                            ';

                            if(!empty($locations)) {

                                foreach ($locations as $label) {

                                    $loc = explode("-",$label);

                                    $content.= '<option value="'.trim($loc[1]).', '.trim($loc[0]).'">' . $label . '</option>';

                                }

                            }

            $content.= '    </select>
                        </div>';
            $content.= '<div class="col-search searchinp"><input type="text" id="job_search" value="" placeholder="Search"><br />';
            $content.= '<p><small class="cnm-btn frmsrcbtn" id="job_search_btn">Search</small><small class="cnm-btn frmsrcbtn" id="resetjs">Reset Job Search</small></p></div></div>';
    		$content.= '<div class="jobs-list" id="jlist">';
    		$content.= $_jobs;
    		$content.= '</div>';
    		$content.= '</div>';

    		if($api_settings['pagination_on']) {

                $content.= '<div id="lever_pagination">';
                $content.= lever_pagination($total_jobs,$api_settings);
                $content.= '</div>';

            }


    		$content.= '</section>';

			return $content;
		}
	}

add_shortcode( 'cnmlever', 'cnm_lever_shortcode' );
add_action('init', 'cnm_lever_shortcode_init');


function lever_pagination($jobs,$settings){

    $total = ceil(count($jobs) / $settings['pagination_num']);

    $pagination = '';

    if($total > 1) {

        $pagination .= '<div id="pagination_wrap">';

        $pagination .= '<p id="lever_pagination_title">Pages: </p>';

        $pagination .= '<ul id="lever_pagination">';

        for ($i = 1; $i <= $total; $i++) {
            $pagination .= '<li><a href="#" data-page="' . $i . '" class="lever-page' . ($i == 1 ? ' lever-page-active' : '') . '">' . $i . '</a></li>';
        }

        $pagination .= '</ul>';

        $pagination .= '</div>';

    }

    return $pagination;

}

//INSTALLATION

global $cnmllp_db_version;
$cnmllp_db_version = '1.0';

function cnmllp_install() {
    global $wpdb;
    global $cnmllp_db_version;

    $table_name = $wpdb->prefix . 'cnmllp_jobs';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		location VARCHAR(255),
		title VARCHAR(500),
		commitment VARCHAR(100),
		team VARCHAR(300),
		link VARCHAR(300),
		PRIMARY KEY  (id)
	) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    add_option( 'cnmllp_db_version', $cnmllp_db_version );
    add_option( 'cnmllp_db_last_update', strtotime('-3 hours'));
}

/*
function cnmllp_install_data() {

    global $wpdb;

    $table_name = $wpdb->prefix . 'cnmllp_jobs';

    //truncate previous datas..
    $wpdb->query('TRUNCATE TABLE ' . $table_name);

    $jobs = json_decode(file_get_contents('https://api.lever.co/v0/postings/cnmllp?group=team&mode=json'));

    foreach ($jobs as $job) {

        foreach($job->postings as $j){

            $wpdb->insert(
                $table_name,
                array(
                    'location' => $j->categories->location,
                    'title' => $j->text,
                    'commitment' => $j->categories->commitment,
                    'team' => $j->categories->team,
                    'link' => $j->hostedUrl
                )
            );

        }

    }


}
*/

register_activation_hook( __FILE__, 'cnmllp_install' );
//register_activation_hook( __FILE__, 'cnmllp_install_data' );


//Plugin Deactivation..
function plugin_deactivated() {

    update_option('cnmllp_db_last_update',strtotime('-3 hours'));
}

register_deactivation_hook( __FILE__, "plugin_deactivated");

//END INSTALLATION


//JOB RESULTS/SEARCHES

add_action( 'wp_loaded', 'reload_jobs' );

function reload_jobs(){

    global $wpdb;

    $last_update = get_option('cnmllp_db_last_update');
    $api_settings = get_option( 'lever_api_settings' );

    $elapse = strtotime('now') - $last_update;

    //if 1 hour elapsed, refresh the db..
    if($elapse >= 3600 && (isset($api_settings['company']) && !empty($api_settings['company']))) {

        $table_name = $wpdb->prefix . 'cnmllp_jobs';

        //truncate previous datas..
        $wpdb->query('TRUNCATE TABLE ' . $table_name);

        function get_http_response_code($url) {
            $headers = get_headers($url);
            return substr($headers[0], 9, 3);
        }

        $lever_url = 'https://api.lever.co/v0/postings/'.$api_settings['company'].'?group=team&mode=json';

        if(get_http_response_code($lever_url) == "200"){

            $jobs = json_decode(file_get_contents($lever_url));

            foreach ($jobs as $job) {

                foreach($job->postings as $j){

                    $wpdb->insert(
                        $table_name,
                        array(
                            'location' => $j->categories->location,
                            'title' => $j->text,
                            'commitment' => $j->categories->commitment,
                            'team' => $j->categories->team,
                            'link' => $j->hostedUrl
                        )
                    );

                }

            }

            update_option('cnmllp_db_last_update',time());

        }

    }


}

add_action( 'wp_ajax_job_search', 'job_search' );
add_action( 'wp_ajax_nopriv_job_search', 'job_search' );

function job_search(){

    global $wpdb;

    $table_name = $wpdb->prefix . 'cnmllp_jobs';

    $lever_api_settings = get_option( 'lever_api_settings' );

    $term = $_POST['term'];
    $location = $_POST['location'];
    $team = $_POST['team'];
    $paginate = isset($_POST['page']) ? 'LIMIT ' . $lever_api_settings['pagination_num'] . ' OFFSET ' . (($_POST['page']-1)*$lever_api_settings['pagination_num']): 'LIMIT ' . $lever_api_settings['pagination_num'];

    $jobs = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE title LIKE "%' . $term . '%" AND team LIKE "%'.$team.'%" AND location LIKE "%'.$location.'%" ORDER BY title ASC ' . $paginate);
    $total_jobs = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE title LIKE "%' . $term . '%" AND team LIKE "%'.$team.'%" AND location LIKE "%'.$location.'%" ORDER BY title ASC ');
    $html = '';

    if(!empty($jobs)) {

        foreach ($jobs as $job) {

            $html .= '<div class="job">';
            $html .= '<div class="job-details">';
            $html .= '<a class="job-title" href="'.$job->link.'" target="_blank">'.$job->title.'</a>';
            $html .= '<p class="tags"><span>'.$job->team.'</span><span>'.$job->location.'</span><span>'.$job->commitment.'</span></p>';
            $html .= '</div>';
            $html .= '<div class="job-btn">';
            $html .= '<a class="cnm-btn" href="'.$job->link.'" target="_blank">Learn more</a>';
            $html .= '</div>';
            $html .= '</div>';

        }

        if($lever_api_settings['pagination_on']) {

            $pages = '<div id="lever_pagination">';
            $pages.= lever_pagination($total_jobs,$lever_api_settings);
            $pages.= '</div>';

        }


    } else {
        $html = '<div style="margin: 30px auto;"><h3>No jobs found matching your query.</h3></div>';
    }

    echo json_encode([
        'html' => $html,
        'pages' => $pages
    ]);

    wp_die();

}

add_action( 'wp_ajax_load_jobs', 'load_jobs' );
add_action( 'wp_ajax_nopriv_load_jobs', 'load_jobs' );

function load_jobs(){

    global $wpdb;

    $table_name = $wpdb->prefix . 'cnmllp_jobs';

    $lever_api_settings = get_option( 'lever_api_settings' );

    $paginate = isset($lever_api_settings['pagination_on']) ? ' LIMIT ' . $lever_api_settings['pagination_num'] : '';

    //truncate previous datas..
    $jobs = $wpdb->get_results('SELECT * FROM ' . $table_name . $paginate); //. ' ORDER BY RAND()');
    $total_jobs = $wpdb->get_results('SELECT * FROM ' . $table_name);

    $_jobs = '';

    if(!empty($jobs)) {

        foreach ($jobs as $job) {

            $_jobs .= '<div class="job">';
            $_jobs .= '<div class="job-details">';
            $_jobs .= '<a class="job-title" href="'.$job->link.'">'.$job->title.'</a>';
            $_jobs .= '<p class="tags"><span>'.$job->team.'</span><span>'.$job->location.'</span><span>'.$job->commitment.'</span></p>';
            $_jobs .= '</div>';
            $_jobs .= '<div class="job-btn">';
            $_jobs .= '<a class="cnm-btn" href="'.$job->link.'">Learn more</a>';
            $_jobs .= '</div>';
            $_jobs .= '</div>';

        }


        if($lever_api_settings['pagination_on']) {

            $pages = '<div id="lever_pagination">';
            $pages.= lever_pagination($total_jobs,$lever_api_settings);
            $pages.= '</div>';

        }

    }

    echo json_encode([
        'html' => $_jobs,
        'pages' => $pages
    ]);

    wp_die();

}

//END JOB RESULTS/SEARCHES


// PLUGIN SETTINGS..

add_action( 'admin_menu', 'lever_api_add_admin_menu' );
add_action( 'admin_init', 'lever_api_settings_init' );

function lever_api_add_admin_menu(  ) {
    add_options_page( 'Lever API', 'Lever API Settings', 'manage_options', 'lever-api-settings', 'lever_api_page' );
}

function lever_api_settings_init(  ) {

    register_setting( 'leverAPI', 'lever_api_settings' );


    add_settings_section(
        'lever_api_section',
        __( 'Lever API Settings', 'wordpress' ),
        'lever_api_settings_section_callback',
        'leverAPI'
    );

    add_settings_field(
        'company',
        __( 'Company String', 'wordpress' ),
        'company_field',
        'leverAPI',
        'lever_api_section'
    );

    add_settings_field(
        'pagination_on',
        __( 'Enable Pagination?', 'wordpress' ),
        'enable_pagination_field',
        'leverAPI',
        'lever_api_section'
    );

    add_settings_field(
        'pagination_num',
        __( 'Records per page?', 'wordpress' ),
        'pagination_num_field',
        'leverAPI',
        'lever_api_section'
    );

}

function company_field(  ) {
    $options = get_option( 'lever_api_settings' );
    ?>
    <input type='text' name='lever_api_settings[company]' value='<?php echo $options['company']; ?>'>
    <?php
}

function enable_pagination_field(  ) {
    $options = get_option( 'lever_api_settings' );
    ?>
    <select name='lever_api_settings[pagination_on]'>
        <option value='0' <?php selected( $options['pagination_on'], 0 ); ?>>No</option>
        <option value='1' <?php selected( $options['pagination_on'], 1 ); ?>>Yes</option>
    </select>

    <?php
}

function pagination_num_field(  ) {
    $options = get_option( 'lever_api_settings' );
    ?>
    <input type='number' name='lever_api_settings[pagination_num]' value='<?php echo !empty($options['pagination_num']) ? $options['pagination_num'] : 10; ?>' min="1">
    <?php
}

function lever_api_settings_section_callback(  ) {
    //echo __( 'This Section Description', 'wordpress' );
}

function lever_api_page() {
    ?>
    <form action='options.php' method='post'>

        <?php
        settings_fields( 'leverAPI' );
        do_settings_sections( 'leverAPI' );
        submit_button();
        ?>

    </form>
    <?php
}

//Hook after saving the option..

function on_api_update($option_name, $old_value, $option_value ) {

    update_option('cnmllp_db_last_update',strtotime('-3 hours'));

}

add_action( 'update_option_lever_api_settings','on_api_update', 10, 3);

// END PLUGIN SETTINGS..