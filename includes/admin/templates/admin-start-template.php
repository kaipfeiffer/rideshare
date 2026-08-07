<div>
    <h2>
        <?php echo __('Manage data', 'rideshare'); ?>
    </h2>
    <p>
        <?php echo __('Choose the tabs to manage the related data.', 'rideshare'); ?>
    </p>

    <?php settings_errors('rideshare_settings'); ?>

    <form method="post">
        <?php wp_nonce_field(\KaiPfeiffer\Rideshare\Admin::SETTINGS_ACTION, \KaiPfeiffer\Rideshare\Admin::SETTINGS_NONCE_FIELD); ?>
        <input type="hidden" name="rideshare_settings_action" value="<?php echo esc_attr(\KaiPfeiffer\Rideshare\Admin::SETTINGS_ACTION); ?>">

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <?php echo esc_html__('REST API', 'rideshare'); ?>
                    </th>
                    <td>
                        <label for="<?php echo esc_attr(\KaiPfeiffer\Rideshare\RidesharePlugin::get_rest_user_filter_option_name()); ?>">
                            <input
                                type="checkbox"
                                id="<?php echo esc_attr(\KaiPfeiffer\Rideshare\RidesharePlugin::get_rest_user_filter_option_name()); ?>"
                                name="<?php echo esc_attr(\KaiPfeiffer\Rideshare\RidesharePlugin::get_rest_user_filter_option_name()); ?>"
                                value="1"
                                <?php checked(\KaiPfeiffer\Rideshare\RidesharePlugin::rideshare_rest_user_filter_enabled()); ?>
                            >
                            <?php echo esc_html__('Remove rideshare users in the WordPress REST API', 'rideshare'); ?>
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button(__('Save settings', 'rideshare')); ?>
    </form>
</div>
