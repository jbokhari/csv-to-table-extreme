<?php
/**
 * Plugin Name: CSV to table (extreme edition)
 * Plugin URI: http://www.anchorwave.com/
 * Description: Uses Advanced Custom Fields file field called <code>csv_to_table</code> on a post (has to be made separate from the plugin). When a CSV is uploaded, the plugin will convert that file to a table and display where the shortcode <code>[csv_directory]</code> is present.
 * Version: The plugin's version number. 0.0.0
 * Author: Anchor Wave Internet Solutions
 * Author URI: http://www.anchorwave.com/
 * License: GPL2
 */
if ( !defined("ABSPATH") ){
    wp_die("Do not access directly");
    exit;
}
class csvToTable {
    public function __construct($csv){

        $this->csv = $csv;
        $this->plugindir = plugin_dir_url( __FILE__ );

    }
    public function wrap($element, $value = ""){

        return "<{$element}>$value</{$element}>";

    }

    public function enqueue_scripts( $atts, $content = null ){

        wp_enqueue_script( 'datatables', "//cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js");
        wp_enqueue_script( 'datatables-colvis', "//cdnjs.cloudflare.com/ajax/libs/datatables-colvis/1.1.0/js/datatables.colvis.min.js", array( 'datatables') );
        wp_enqueue_style( 'datatables', '//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css' );
        wp_enqueue_style( 'datatablescolvis', '//cdnjs.cloudflare.com/ajax/libs/datatables-colvis/1.1.0/css/datatables.colvis.min.css' );
        wp_enqueue_style( 'datatablescolvisjqueryui', '//cdnjs.cloudflare.com/ajax/libs/datatables-colvis/1.1.0/css/datatables.colvis.jqueryui.min.css' );

    }

    public function build_csv_directory( $atts, $content = null ){

        if ( $this->csv && isset( $this->csv['url'] ) ){

            $file = @fopen( $this->csv['url'], "r");
            $headers = fgetcsv( $file );

            $return = '';
            ob_start();
            ?>
            <style>
                .table-wrap {
                }
                table.dataTable tbody tr th,
                table.dataTable tbody tr td {
                    border: none;
                    min-width: 130px;
                }
                table.dataTable tbody tr {
                    background: transparent;
                }
                thead tr {
                    background: #EADCCD;
                }
                table.dataTable tbody tr:nth-child(even) {
                    background: #F7E9CC;  
                }
                td {
                    background: transparent;
                    border: 1px solid grey;
                }
                .ColVis {
                    margin-right: 140px;
                }
                .table-wrap {
                    background: #F9EFDA;
                    overflow: scroll;
                    max-width: 100%;
                    padding: 20px;
                    box-sizing: border-box;
                    -webkit-box-sizing: border-box;
                    -moz-box-sizing: border-box;
                    border: 2px solid silver;
                }
                .ss-directory {
                    display: none;
                }
                .table-wrap.loading{
                    background: #F9EFDA url( <?php echo $this->plugindir . '/loading.gif'; ?> ) center center no-repeat;
                    min-height: 500px;
                }
            </style>
            <div class="table-wrap loading">
            <table class='ss-directory'>
            <thead>
            <tr>
            <?php
            foreach( $headers as $header ){
                $index = array();
                echo $this->wrap( "th", $header );
            }
            ?>
            </tr>
            </thead>
            <tbody>
            <?php
            while( $row = fgetcsv($file) ){
                echo "<tr>";
                foreach ($row as $value) {
                    echo $this->wrap( "td", $value );
                }
                echo "</tr>";
            }
            ?>
            </tbody>
            </table>
            </div>
            <script>
                jQuery(document).ready(function($){
                    var table = $(".ss-directory");
                    var datatable = $(".ss-directory").on("init.dt", function(){
                        $('.table-wrap').removeClass("loading");
                        table.show();
                    }).dataTable({autoWidth: true});
                    var colvis = new $.fn.dataTable.ColVis( datatable );
                    $( colvis.button() ).insertBefore( table );
                });
            </script>
            <?php
            $return = ob_get_contents();
            ob_clean();
            return $return;
        } else {
            return "<!-- No CSV file found -->";
        }
    }
}

function init_csvToTable(){
    if ( ! i