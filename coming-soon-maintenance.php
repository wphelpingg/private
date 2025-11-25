<?php
/*
Plugin Name: Coming Soon & Maintenance Mode - WPCoderMind
Description: Coming Soon / Maintenance Mode with templates, preview, countdown and integrations (step-by-step build).
Version: 1.0.0
Author: WPCoderMind
Text Domain: csmm
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'CSMM_PATH', plugin_dir_path( __FILE__ ) );
define( 'CSMM_URL',  plugin_dir_url(  __FILE__ ) );

// Includes (module 1)
require_once CSMM_PATH . 'includes/helpers.php';
// Load main classes
require_once CSMM_PATH . 'includes/class-csmm-settings.php';
require_once CSMM_PATH . 'includes/class-csmm-frontend.php';
//require_once CSMM_PATH . 'includes/class-csmm-preview-admin-ajax.php';
require_once CSMM_PATH . 'includes/class-csmm-elementor.php';


// Initialize
add_action('plugins_loaded', function() {
    new CSMM_Settings();
    new CSMM_Frontend();
    new CSMM_Elementor();
});

// Activation: add default options if not present
register_activation_hook( __FILE__, function() {
    $defaults = array(
        'enabled'               => 0,
        'mode'                  => 'coming_soon',
        'title'                 => "We're coming soon!",
        'description'           => 'We are working on our website. Stay tuned!',
        'template'              => 'classic',
        'bg_type'               => 'color',
        'bg_color'              => '#f5f7fb',
        'bg_image'              => '',
        'countdown_enabled'     => 0,
        'countdown_end'         => '',
        'auto_disable'          => 0,
        'ip_whitelist'          => '',
        'bypass_logged_in'      => 1,
        'subscribe_enabled'     => 0,
        'subscribe_admin_email' => get_option('admin_email'),
        'social'                => array(),
        'custom_css'            => '',
        'contact_email'         => get_option('admin_email'),
        'help_url'              => '',

    );

    if ( false === get_option( 'csmm_options' ) ) {
        add_option( 'csmm_options', $defaults );
    }
});



add_action('admin_menu', 'my_form_analytics_page');
function my_form_analytics_page() {
    add_menu_page(
        'Form Analytics',
        'Form Analytics',
        'manage_options',
        'my-form-analytics',
        'my_form_analytics_screen',
        'dashicons-chart-bar'
    );
}

function my_form_analytics_screen() {
?>
<div class="wrap">
    <h2>Form Analytics Dashboard</h2>

    <div style="display:flex; gap:15px; margin:20px 0;">
        
        <!-- Date Range -->
        <input type="date" id="from_date" value="2021-01-01">
        <input type="date" id="to_date" value="2021-12-31">

        <!-- Form Selector -->
        <select id="form_name">
            <option value="student_form">Student Form</option>
            <option value="enquiry_form">Enquiry Form</option>
            <option value="register_form">Registration Form</option>
        </select>

        <!-- Grouping -->
        <select id="group_by">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly" selected>Monthly</option>
        </select>
        <button class="button button-primary" onclick="loadAnalytics()">Filter</button>
        <button class="button" onclick="changeChart('bar')">Bar Chart</button>
        <button class="button" onclick="changeChart('line')">Line Chart</button>

    </div>

    <canvas id="analyticsChart" width="1100" height="500"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let chart;
let chartType = "bar"; // default

function loadAnalytics() {

    let data = {
        action: "load_form_stats",
        from: document.getElementById('from_date').value,
        to: document.getElementById('to_date').value,
        form: document.getElementById('form_name').value,
        group: document.getElementById('group_by').value,
    };

    fetch(ajaxurl, {
        method: "POST",
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(response => {

        let labels = response.labels;
        let values = response.values;

        if(chart) chart.destroy();

        let ctx = document.getElementById('analyticsChart').getContext('2d');
        chart = new Chart(ctx, {
            type: chartType,
            data: {
                labels: labels,
                datasets: [{
                    label: "Form Entries",
                    data: values,
                    borderWidth: 2,
                    backgroundColor: "rgba(30,144,255,0.4)",
                    borderColor: "rgba(30,144,255,1)",
                    pointRadius: 5,
                    pointHoverRadius: 7,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    });
}

function changeChart(type) {
    chartType = type;
    loadAnalytics();
}

document.addEventListener('DOMContentLoaded', loadAnalytics);
</script>

<?php
}

add_action("wp_ajax_load_form_stats", "load_form_stats_callback");
function load_form_stats_callback() {

    $group = $_POST['group'] ?? 'monthly';

    if($group == 'daily') {
        $labels = ["Day 1", "Day 2", "Day 3", "Day 4", "Day 5", "Day 6", "Day 7"];
        $values = [10, 20, 15, 18, 30, 25, 22];
    }
    elseif($group == 'weekly') {
        $labels = ["Week 1", "Week 2", "Week 3", "Week 4"];
        $values = [80, 95, 70, 100];
    }
    else {
        $labels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        $values = [120, 135, 100, 180, 160, 140, 190, 175, 150, 200, 195, 210];
    }

    wp_send_json([
        "labels" => $labels,
        "values" => $values
    ]);
}
