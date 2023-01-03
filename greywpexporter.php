<?php
/**
 * Grey Exporter Plugin
 *
 * @package           greywpexporter
 * @author            Grey Bear Enterprises
 * @copyright         2022 Grey Bear Enterprises
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:      Grey Exporter Plugin   
 * Plugin URI:        https://plugins.greybearenterprises.com/greywpexporter
 * Description:       Simple WordPress exporting plugin.
 * Version:           1.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Grey Bear Enterpries
 * Author URI:        https://greybearenterprises.com 
 * Text Domain:       greywpexporter
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:        https://plugins.greybearenterprises.com/greywpexporter
 */
if ( ! class_exists( 'GBE_ExportPlugin' ) ) {
    class GBE_ExportPlugin {
        public static function init() {
           add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
           add_action( 'admin_post_GExportWP', array( __CLASS__, 'exporter' ) );
        }

        public static function admin_menu() {
           
           add_menu_page( 'WP Export Plugin', 'WP Export Plugin', 'manage_options', 'grey-wpexport',  array( __CLASS__, 'admin_page'), '', 5);

        }
        public static function admin_page() {
            if ( !current_user_can( 'manage_options' ) )  {
                wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
            }

            global $wpdb;
            ob_start();?>
            <div class="wrap" style="background-color: #f9f9f9; margin: 10px auto; padding: 40px; border-radius:10px; ">
                <div >
                    <h1>WordPress Exporter</h1><br/>
                    
                    <h3>Taxonomy Terms Export</h3>
                    <?php $attributes = get_taxonomies(array(),'objects'); ?>

                    <form action="<?=site_url()."/wp-admin/";?>admin-post.php" method="POST" style="width: 100%;">   
                        <input type="hidden" name="action" value="GExportWP" />
                        <select name="argument" id="argument">
                            <?php   
                                foreach ($attributes as $att) {
                                    echo '<option value="'.$att->name.'">';
                                    echo $att->label.'</option>'; 
                                }
                            ?>
                        </select>
                        <input type="submit" value="Attributes Export" class="" style="padding: 10px; background: #000000; color: #ffffff;" />
                    </form>
                </div>
            </div>

            <?php
            ob_end_flush();
        }
        

        public static function exporter() {
            ob_start();
            $arg = $_POST['argument'];
            $filename = $arg.".csv";
        
            $terms = get_terms( array(
                'taxonomy' => $arg,
                'hide_empty' => false,
            ) );
        
        
            $header_row = array(
                '#',
                'Term ID',
                'Name',
                'Slug',
                'Taxonomy',
                'Count',
                'Description'      
            );
            $data_rows = array();
            $i = 1;
        
            foreach ($terms as $term) {
            
            $row1 = array(
                $i,
                $term->term_id,
                $term->name, 
                $term->slug,
                $arg,
                $term->count,
                $term->description
                
            );
            $data_rows[] = $row1;
            $i++;
        
            }
            $fh = @fopen( 'php://output', 'w' );
            fprintf( $fh, chr(0xEF) . chr(0xBB) . chr(0xBF) );
            header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
            header( 'Content-Description: File Transfer' );
            header( 'Content-type: text/csv' );
            header( "Content-Disposition: attachment; filename={$filename}" );
            header( 'Expires: 0' );
            header( 'Pragma: public' );
            fputcsv( $fh, $header_row );
            foreach ( $data_rows as $data_row ) {
                fputcsv( $fh, $data_row );
            }
            fclose( $fh );
            ob_end_flush();
            die();
        }
        


    }
    GBE_ExportPlugin::init();
    
}