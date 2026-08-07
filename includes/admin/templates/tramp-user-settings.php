<div class="tramp-user-settings" id="tramp-user-settings-section">
    <h2><?php esc_html_e('Ride-Sharing User Settings', 'rideshare'); ?></h2>
    <p><?php esc_html_e('Additional profile data for users with a Ride-Sharing role.', 'rideshare'); ?></p>

    <input type="hidden" value="<?php echo intval($tramp_location_id ?? 0); ?>" name="tramp_location[id]" id="tramp_location_id" />
    <input type="hidden" value="<?php echo intval($tramp_user_id ?? 0); ?>" name="tramp_user[id]" id="tramp_user_id" />

    <table class="form-table" role="presentation">
        <?php if (! (IS_PROFILE_PAGE && ! $user_can_edit)) : ?>
            <?php if ($user_columns ?? null) : ?>
                <?php foreach ($user_columns as $col => $value) : ?>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row"><?php echo esc_html($labels[$col] ?? $col); ?></th>
                        <td>
                            <input type="<?php echo esc_attr($input_types[$col] ?? 'text'); ?>" name="tramp_user[<?php echo esc_attr($col); ?>]" id="tramp_user_<?php echo esc_attr($col); ?>" value="<?php echo esc_attr($value); ?>" class="regular-text" />
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </table>
    <h3><?php esc_html_e('Ride-Sharing User Address', 'rideshare'); ?></h3>
    <table class="form-table" role="presentation">
        <?php if (! (IS_PROFILE_PAGE && ! $user_can_edit)) : ?>
            <?php if ($location_columns ?? null) : ?>
                <?php foreach ($location_columns as $col => $value) : ?>
                    <tr class="user-rich-editing-wrap">
                        <th scope="row"><?php echo esc_html($labels[$col] ?? $col); ?></th>
                        <td>
                            <input type="text" name="tramp_location[<?php echo esc_attr($col); ?>]" id="tramp_location_<?php echo esc_attr($col); ?>" value="<?php echo esc_attr($value); ?>" class="regular-text" />
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </table>
</div>
