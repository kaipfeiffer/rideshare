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
                        <?php echo esc_html__('Instance mode', 'rideshare'); ?>
                    </th>
                    <td>
                        <?php
                        $mode_option_name = \KaiPfeiffer\Rideshare\Instance_Controller::get_mode_option_name();
                        $current_mode = \KaiPfeiffer\Rideshare\Instance_Controller::get_mode();
                        ?>
                        <select
                            id="<?php echo esc_attr($mode_option_name); ?>"
                            name="<?php echo esc_attr($mode_option_name); ?>"
                        >
                            <?php foreach (\KaiPfeiffer\Rideshare\Instance_Controller::get_modes() as $mode => $label) : ?>
                                <option value="<?php echo esc_attr($mode); ?>" <?php selected($current_mode, $mode); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (\KaiPfeiffer\Rideshare\Settings::INSTANCE_MODE_STANDARD_COLLECTOR === $current_mode) : ?>
                            <p class="description">
                                <?php echo esc_html__('This instance offers local rides and collects remote rides. Use this only for exceptional setups.', 'rideshare'); ?>
                            </p>
                        <?php endif; ?>
                        <p class="description">
                            <?php echo esc_html(sprintf(__('Instance UUID: %s', 'rideshare'), \KaiPfeiffer\Rideshare\Instance_Controller::get_uuid())); ?>
                        </p>
                    </td>
                </tr>
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
