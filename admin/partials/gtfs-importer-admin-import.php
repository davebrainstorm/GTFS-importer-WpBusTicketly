<?php
/**
 * Provides the import view for the plugin
 *
 * This file is used to markup the admin-facing import page of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    GTFS_Importer
 * @subpackage GTFS_Importer/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Get plugin settings
$settings = get_option('gtfs_importer_settings', array());
?>

<div class="wrap gtfs-importer-admin">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="gtfs-admin-notices">
        <?php settings_errors('gtfs_importer_notices'); ?>
    </div>

    <div class="gtfs-import-container">
        <h2><?php _e('Import GTFS Data', 'gtfs-importer'); ?></h2>
        <p class="description">
            <?php _e('Import GTFS data from a ZIP file, URL, or FTP server.', 'gtfs-importer'); ?>
        </p>
        
        <div class="gtfs-import-tabs">
            <ul class="gtfs-tabs-nav">
                <li class="active"><a href="#tab-upload"><?php _e('Upload File', 'gtfs-importer'); ?></a></li>
                <li><a href="#tab-url"><?php _e('Import from URL', 'gtfs-importer'); ?></a></li>
                <li><a href="#tab-ftp"><?php _e('Import from FTP', 'gtfs-importer'); ?></a></li>
            </ul>
            
            <div class="gtfs-tabs-content">
                <!-- Upload File Tab -->
                <div id="tab-upload" class="gtfs-tab-content active">
                    <form id="gtfs-upload-form" method="post" enctype="multipart/form-data" class="gtfs-import-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="feed_name"><?php _e('Feed Name', 'gtfs-importer'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="feed_name" id="feed_name" class="regular-text" required>
                                    <p class="description"><?php _e('Give this feed a descriptive name for identification.', 'gtfs-importer'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="gtfs_file"><?php _e('GTFS File (ZIP)', 'gtfs-importer'); ?></label>
                                </th>
                                <td>
                                    <input type="file" name="gtfs_file" id="gtfs_file" accept=".zip" required>
                                    <p class="description"><?php _e('Upload a ZIP file containing GTFS data.', 'gtfs-importer'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <input type="hidden" name="import_type" value="upload">
                        <input type="hidden" name="action" value="gtfs_import_file">
                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('gtfs_import_nonce'); ?>">
                        
                        <p class="submit">
                            <button type="submit" class="button button-primary"><?php _e('Import GTFS Data', 'gtfs-importer'); ?></button>
                        </p>
                    </form>
                </div>
                
                <!-- URL Tab -->
                <div id="tab-url" class="gtfs-tab-content">
                    <form id="gtfs-url-form" method="post" class="gtfs-import-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="feed_name_url"><?php _e('Feed Name', 'gtfs-importer'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="feed_name" id="feed_name_url" class="regular-text" required>
                                    <p class="description"><?php _e('Give this feed a descriptive name for identification.', 'gtfs-importer'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="feed_url"><?php _e('GTFS Feed URL', 'gtfs-importer'); ?></label>
                                </th>
                                <td>
                                    <input type="url" name="feed_url" id="feed_url" class="regular-text" required placeholder="https://example.com/gtfs.zip">
                                    <p class="description"><?php _e('Enter the URL of a GTFS ZIP file.', 'gtfs-importer'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <input type="hidden" name="import_type" value="url">
                        <input type="hidden" name="action" value="gtfs_import_file">
                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('gtfs_import_nonce'); ?>">
                        
                        <p class="submit">
                            <button type="submit" class="button button-primary"><?php _e('Import GTFS Data', 'gtfs-importer'); ?></button>
                        </p>
                    </form>
                </div>
                
                <!-- FTP Tab -->
                <div id="tab-ftp" class="gtfs-tab-content">
                    <form id="gtfs-ftp-form" method="post" class="gtfs-import-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="feed_name_ftp"><?php _e('Feed Name', 'gtfs-importer'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="feed_name" id="feed_name_ftp" class="regular-text" required>
                                    <p class="description"><?php _e('Give this feed a descriptive name for identification.', 'gtfs-importer'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="ftp_server"><?php _e('FTP Server', 'gtfs-importer'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="ftp_server" id="ftp_server" class="regular-text" required placeholder="ftp.example.com">
                                    <p class="description"><?php _e('Enter the FTP server address.', 'gtfs-importer'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="ftp_username"><?php _e('FTP Username', 'gtfs-importer'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="ftp_username" id="ftp_username" class="regular-text" required>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="ftp_password"><?php _e('FTP Password', 'gtfs-importer'); ?></label>
                                </th>
                                <td>
                                    <input type="password" name="ftp_password" id="ftp_password" class="regular-text" required>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="ftp_path"><?php _e('Path to GTFS File', 'gtfs-importer'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="ftp_path" id="ftp_path" class="regular-text" required placeholder="/path/to/gtfs.zip">
                                    <p class="description"><?php _e('Enter the path to the GTFS ZIP file on the FTP server.', 'gtfs-importer'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <input type="hidden" name="import_type" value="ftp">
                        <input type="hidden" name="action" value="gtfs_import_file">
                        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('gtfs_import_nonce'); ?>">
                        
                        <p class="submit">
                            <button type="submit" class="button button-primary"><?php _e('Import GTFS Data', 'gtfs-importer'); ?></button>
                        </p>
                    </form>
                </div>
            </div>
        </div>
        
        <div id="gtfs-import-progress" class="gtfs-import-progress hidden">
            <h3><?php _e('Import Progress', 'gtfs-importer'); ?></h3>
            <div class="progress-bar-container">
                <div class="progress-bar"></div>
            </div>
            <div class="progress-status">
                <p class="progress-text"><?php _e('Importing...', 'gtfs-importer'); ?></p>
                <p class="progress-percentage">0%</p>
            </div>
            <div class="import-log"></div>
        </div>
    </div>
</div>
