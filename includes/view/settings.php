
<div class="wrap">
<h1><?php esc_html_e('Plugin Settings', 'woo-gallery-link'); ?></h1>
<form method="post" action="options.php">
    <?php settings_fields('woo_gallery_link_settings'); ?>
    <?php do_settings_sections('woo_gallery_link_settings'); ?>
    <?php submit_button(); ?>
</form>
</div>

