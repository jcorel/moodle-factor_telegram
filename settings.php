<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Settings for Telegram MFA factor.
 *
 * @package     factor_telegram
 * @subpackage  tool_mfa
 * @author      Jorge Courel <jgonzcou@gmail.com>
 * @copyright   2025 Jorge Courel
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use factor_telegram\telegram_api;
use tool_mfa\manager;

defined('MOODLE_INTERNAL') || die();

global $CFG;

$settings->add(
        new admin_setting_heading(
                'factor_telegram/heading',
                '',
                new lang_string('settings:heading', 'factor_telegram')));

$enabled = new admin_setting_configcheckbox(
        'factor_telegram/enabled',
        new lang_string('settings:enablefactor', 'tool_mfa'),
        new lang_string('settings:enablefactor_help', 'tool_mfa'),
        0,
);
$enabled->set_updatedcallback(function() {
    manager::do_factor_action(
            'telegram',
            get_config('factor_telegram', 'enabled') ? 'enable' : 'disable',
    );
});
$settings->add($enabled);

$settings->add(new admin_setting_configtext(
        'factor_telegram/apibaseurl',
        new lang_string('settings:apibaseurl', 'factor_telegram'),
        new lang_string('settings:apibaseurl_help', 'factor_telegram'),
        'https://api.telegram.org',
        PARAM_TEXT
));

$token = get_config('factor_telegram', 'bottoken');
$isvalidtoken = false;

if (!empty($token)) {
    $telegramapi = new telegram_api($token);
    $isvalidtoken = $telegramapi->validate_token();
}

$tokenstatusmsg = $isvalidtoken
        ? get_string('settings:bottokenvalid', 'factor_telegram')
        : get_string('settings:bottokeninvalid', 'factor_telegram');

$tokenhelp = get_string('settings:bottoken_help', 'factor_telegram');
$tokendesc = !empty($token) ? $tokenstatusmsg . '<br>' . $tokenhelp : $tokenhelp;

$settings->add(
        new admin_setting_configtext(
                'factor_telegram/bottoken',
                new lang_string('settings:bottoken', 'factor_telegram'),
                $tokendesc,
                '',
                PARAM_TEXT
        )
);

$settings->add(
        new admin_setting_configtext(
                'factor_telegram/weight',
                new lang_string('settings:weight', 'tool_mfa'),
                new lang_string('settings:weight_help', 'tool_mfa'),
                100,
                PARAM_INT,
        ),
);
$settings->hide_if('factor_telegram/weight', 'factor_telegram/enabled');

$settings->add(
        new admin_setting_configduration(
                'factor_telegram/duration',
                new lang_string('settings:duration', 'tool_mfa'),
                new lang_string('settings:duration_help', 'tool_mfa'),
                30 * MINSECS,
                MINSECS,
        ),
);
$settings->hide_if('factor_telegram/duration', 'factor_telegram/enabled');
