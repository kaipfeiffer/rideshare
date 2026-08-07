<?php
/**
 * Dynamic frontend template for the rideshare block.
 *
 * @var array $initial_data
 * @var array $riding_items
 * @var bool $can_use
 * @var string $field_id_prefix
 */

if (!defined('ABSPATH')) {
    exit;
}

$labels = $initial_data['labels'];
?>
<div <?php echo get_block_wrapper_attributes(array('class' => 'rideshare-riding-widget')); ?> data-rideshare-riding-widget>
    <div class="rideshare-riding-widget__server-content">
        <h2><?php echo esc_html($labels['title']); ?></h2>

        <?php if (!$can_use) : ?>
            <div class="rideshare-riding-widget__notice rideshare-riding-widget__notice--info rideshare-riding-widget__login-prompt">
                <p><?php echo esc_html($labels['login_required']); ?></p>
                <a class="rideshare-riding-widget__secondary-button" href="<?php echo esc_url($initial_data['login_url']); ?>">
                    <?php echo esc_html($labels['login']); ?>
                </a>
            </div>
        <?php endif; ?>

        <section class="rideshare-riding-widget__offers" aria-labelledby="<?php echo esc_attr($field_id_prefix); ?>offers-title">
            <h3 id="<?php echo esc_attr($field_id_prefix); ?>offers-title"><?php echo esc_html($labels['rides']); ?></h3>

            <?php if (!$riding_items) : ?>
                <p class="rideshare-riding-widget__empty"><?php echo esc_html($labels['no_items']); ?></p>
            <?php else : ?>
                <ol class="rideshare-riding-widget__offer-list">
                    <?php foreach ($riding_items as $item) : ?>
                        <li>
                            <details class="rideshare-riding-widget__ride">
                                <summary class="rideshare-riding-widget__ride-summary">
                                    <span class="rideshare-riding-widget__type-icon rideshare-riding-widget__type-icon--<?php echo esc_attr($item['type']); ?>" role="img" aria-label="<?php echo esc_attr($item['type_label']); ?>" title="<?php echo esc_attr($item['type_label']); ?>">
                                        <?php echo 'offer' === $item['type'] ? '+' : '?'; ?>
                                    </span>
                                    <span class="rideshare-riding-widget__summary-text">
                                        <span class="rideshare-riding-widget__route">
                                            <?php echo esc_html($item['origin_label']); ?> &rarr; <?php echo esc_html($item['destination_label']); ?>
                                        </span>
                                        <span class="rideshare-riding-widget__period"><?php echo esc_html($item['period_label']); ?></span>
                                    </span>
                                </summary>
                                <div class="rideshare-riding-widget__ride-details">
                                    <dl>
                                        <div>
                                            <dt><?php echo esc_html__('Type', 'rideshare'); ?></dt>
                                            <dd><?php echo esc_html($item['type_label']); ?></dd>
                                        </div>
                                        <?php if (!empty($item['passengers'])) : ?>
                                            <div>
                                                <dt><?php echo esc_html($item['passengers_label']); ?></dt>
                                                <dd><?php echo esc_html($item['passengers']); ?></dd>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($item['description'])) : ?>
                                            <div>
                                                <dt><?php echo esc_html__('Note', 'rideshare'); ?></dt>
                                                <dd><?php echo esc_html($item['description']); ?></dd>
                                            </div>
                                        <?php endif; ?>
                                    </dl>
                                    <?php if ($can_use && 'offer' === $item['type']) : ?>
                                        <div class="rideshare-riding-widget__booking-actions">
                                            <button
                                                class="rideshare-riding-widget__secondary-button"
                                                type="button"
                                                data-rideshare-book-riding="<?php echo esc_attr($item['id']); ?>"
                                                <?php disabled(empty($item['can_book'])); ?>
                                            >
                                                <?php echo esc_html($item['booking_status_label'] ?: $labels['book']); ?>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </details>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </section>

        <?php if ($can_use) : ?>
            <div class="rideshare-riding-widget__actions">
                <button class="rideshare-riding-widget__submit" type="button" data-rideshare-open-request="offer">
                    <?php echo esc_html($labels['create_offer']); ?>
                </button>
                <button class="rideshare-riding-widget__secondary-button" type="button" data-rideshare-open-request="search">
                    <?php echo esc_html($labels['create_request']); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <script type="application/json" class="rideshare-riding-widget__data">
        <?php echo wp_json_encode($initial_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>
    </script>
</div>
