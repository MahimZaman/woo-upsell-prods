<?php

/**
 * Plugin Name: Woo Upsell Products
 * Description: Adds upsell products (including variations) as checkbox items on the product page. Checked items are added to cart with the main product.
 * Version: 1.1.0
 * Author: Mahim Zaman
 * Author URI: https://mahimzaman.com
 * Text Domain: wc-upsell-checkbox
 */

if (! defined('ABSPATH')) {
    exit;
}

define('WUP_PATH', trailingslashit(plugin_dir_path(__FILE__)));
define('WUP_URL', trailingslashit(plugin_dir_url(__FILE__)));

class WC_Upsell_Products
{

    public function __construct()
    {
        add_action('woocommerce_before_add_to_cart_button', [$this, 'render_upsell_checkboxes']);
        add_filter('woocommerce_add_to_cart_validation', [$this, 'add_upsells_to_cart'], 10, 3);
        add_action('woocommerce_single_product_summary', [$this, 'custom_show_upsell_products_with_add_to_cart'], 35);
        add_shortcode('wup_upsells', [$this, 'wup_upsells_callback']);
        add_action('wp_enqueue_scripts', [$this, 'public_scripts']);
    }

    public function public_scripts()
    {
        wp_enqueue_style('wup-swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], time(), 'all');
        wp_enqueue_style('wup-css', WUP_URL . 'assets/css/public.css', [], time(), 'all');
        wp_enqueue_script('wup-swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], time(), true);
        wp_enqueue_script('wup-js', WUP_URL . 'assets/js/public.js', [], time(), true);
    }

    public function wup_upsells_callback()
    {
        ob_start();
        $this->custom_show_upsell_products_with_add_to_cart();
        return ob_get_clean();
    }

    public function custom_show_upsell_products_with_add_to_cart()
    {
        global $product;

        $upsells = $product->get_upsell_ids();

        if (empty($upsells)) return;
?>
        <div class="custom-upsell-products">
            <h3><?php echo __('Choose your box', 'woocommerce'); ?></h3>
            <div class="upsell-items">
                <?php
                foreach ($upsells as $upsell_id) {
                    $upsell_product = wc_get_product($upsell_id);
                    if (!$upsell_product || !$upsell_product->is_purchasable() || !$upsell_product->is_in_stock()) {
                        continue;
                    }

                    if ($upsell_product->is_type('variable')) {
                        $variations = $upsell_product->get_available_variations();
                        if (!empty($variations)) {
                            foreach ($variations as $variation) {
                                $upsell_variation = wc_get_product($variation['variation_id']);
                                $product_url = get_permalink($variation['variation_id']);
                                $product_title = $upsell_variation->get_name();
                                $product_price = $upsell_variation->get_price_html();
                                $product_image = $upsell_variation->get_image('woocommerce_thumbnail');
                ?>
                                <div class="upsell-item">
                                    <div class="upsell-qv">
                                        <span class="close-upsell-qv">&times;</span>
                                        <div class="swiper upsell-images">
                                            <div class="swiper-wrapper">
                                                <?php
                                                $image = $upsell_variation->get_image_id();
                                                $gallery = $upsell_variation->get_gallery_image_ids();
                                                if (!empty($image)) {
                                                ?>
                                                    <div class="swiper-slide upsell-qv-item">
                                                        <img src="<?php echo wp_get_attachment_url($image); ?>">
                                                    </div>
                                                    <?php
                                                }
                                                if (!empty($gallery)) {
                                                    foreach ($gallery as $item) {
                                                    ?>
                                                        <div class="swiper-slide upsell-qv-item">
                                                            <img src="<?php echo wp_get_attachment_url($item); ?>">
                                                        </div>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <div class="swiper-button-next"></div>
                                            <div class="swiper-button-prev"></div>
                                        </div>
                                    </div>
                                    <input type="checkbox" data-id="upsell-<?php echo esc_attr($variation['variation_id']); ?>" value="<?php echo esc_attr($variation['variation_id']); ?>">
                                    <?php echo $product_image; ?>
                                    <a href="<?php echo esc_url($product_url); ?>">
                                        <h4><?php echo esc_html($product_title); ?></h4>
                                    </a>
                                    <span class="price"><?php echo $upsell_variation->get_price() == 0 ? 'Free' :  $product_price; ?></span>
                                </div>
                        <?php
                            }
                        }
                    } else {
                        $product_url = get_permalink($upsell_id);
                        $product_title = $upsell_product->get_name();
                        $product_price = $upsell_product->get_price_html();
                        $product_image = $upsell_product->get_image('woocommerce_thumbnail');
                        ?>
                        <div class="upsell-item">
                            <div class="upsell-qv">
                                <span class="close-upsell-qv">&times;</span>
                                <div class="swiper upsell-images">
                                    <div class="swiper-wrapper">
                                        <?php
                                        $image = $upsell_product->get_image_id();
                                        $gallery = $upsell_product->get_gallery_image_ids();
                                        if (!empty($image)) {
                                        ?>
                                            <div class="swiper-slide upsell-qv-item">
                                                <img src="<?php echo wp_get_attachment_url($image); ?>">
                                            </div>
                                            <?php
                                        }
                                        if (!empty($gallery)) {
                                            foreach ($gallery as $item) {
                                            ?>
                                                <div class="swiper-slide upsell-qv-item">
                                                    <img src="<?php echo wp_get_attachment_url($item); ?>">
                                                </div>
                                        <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                    <div class="swiper-button-next"></div>
                                    <div class="swiper-button-prev"></div>
                                </div>
                            </div>
                            <input type="checkbox" data-id="upsell-<?php echo esc_attr($upsell_id);; ?>" value="<?php echo esc_attr($upsell_id); ?>">
                            <?php echo $product_image; ?>
                            <a href="<?php echo esc_url($product_url); ?>">
                                <h4><?php echo esc_html($product_title); ?></h4>
                            </a>
                            <span class="price"><?php echo $upsell_product->get_price() == 0 ? 'Free' : $product_price; ?></span>
                        </div>
                <?php
                    }
                }

                ?>
            </div>
        </div>
<?php
    }

    public function render_upsell_checkboxes()
    {
        global $product;

        if (! $product || ! $product->exists()) {
            return;
        }

        $upsells = $product->get_upsell_ids();

        if (empty($upsells)) {
            return;
        }
        echo '<div class="upsell-hidden">';
        foreach ($upsells as $upsell_id) {
            $upsell = wc_get_product($upsell_id);
            if (! $upsell || ! $upsell->is_purchasable()) {
                continue;
            }

            if ($upsell->is_type('variable')) {
                echo '<div style="margin:10px 0;padding:8px;border:1px solid #eee;border-radius:5px;">';
                echo '<strong>' . esc_html($upsell->get_name()) . '</strong><br>';

                $variations = $upsell->get_available_variations();
                foreach ($variations as $variation) {
                    $variation_obj = wc_get_product($variation['variation_id']);
                    if (! $variation_obj || ! $variation_obj->is_purchasable()) {
                        continue;
                    }

                    $label = $variation_obj->get_name();
                    $price = $variation_obj->get_price_html();

                    echo '<label style="display:block;margin:5px 0;">';
                    echo '<input type="checkbox" name="upsell_products[]" id="upsell-' . esc_attr($variation['variation_id']) . '" value="' . esc_attr($variation['variation_id']) . '"> ';
                    echo esc_html($label) . ' - ' . wp_kses_post($price);
                    echo '</label>';
                }
                echo '</div>';
            } else {
                // Simple product
                echo '<label style="display:block;margin:5px 0;">';
                echo '<input type="checkbox" name="upsell_products[]" id="upsell-' . esc_attr($upsell_id) . '" value="' . esc_attr($upsell_id) . '"> ';
                echo esc_html($upsell->get_name()) . ' - ' . wp_kses_post($upsell->get_price_html());
                echo '</label>';
            }
        }
        echo '</div>';
    }

    public function add_upsells_to_cart($passed, $product_id, $quantity)
    {
        if (isset($_POST['upsell_products']) && is_array($_POST['upsell_products'])) {
            foreach ($_POST['upsell_products'] as $upsell_id) {
                $upsell_id = absint($upsell_id);
                if ($upsell_id > 0 && wc_get_product($upsell_id)) {
                    WC()->cart->add_to_cart($upsell_id, 1);
                }
            }
        }
        return $passed;
    }
}

new WC_Upsell_Products();
