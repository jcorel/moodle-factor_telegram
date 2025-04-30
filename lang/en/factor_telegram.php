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
 * Language strings.
 *
 * @package     factor_telegram
 * @subpackage  tool_mfa
 * @author      Jorge Courel <jgonzcou@gmail.com>
 * @copyright   2025 Jorge Courel
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['action:manage'] = 'Manage chat ID';
$string['action:revoke'] = 'Remove chat ID';
$string['addchatid'] = 'Chat ID';
$string['chatid'] = 'Chat ID';
$string['chatid_help'] = '<p class="line-height-4">To set up this factor, you need to provide your <strong>Telegram Chat ID</strong> (<i>it\'s not your username</i> but a numeric identifier).</p><p>You can find your Chat ID by sending a message to <a href="https://t.me/userinfobot" target="_blank" class="btn text-white telegram-bg"><i class="icon fa fa-telegram"></i>@userinfobot</a>. The bot will reply with your Chat ID.</p>';
$string['chatiddescription'] = '<p>Please enter your Telegram Chat ID to receive the <strong>verification code</strong>.</p>';
$string['chatidnotfound'] = 'Chat ID not found. Please check your Telegram account.';
$string['editchatid'] = 'Edit Chat ID';
$string['editchatidinfo'] = 'If you didn\'t receive the code or entered the wrong number, please edit the Chat ID and try again.';
$string['error:emptyverification'] = 'Empty code. Try again.';
$string['error:wrongchatid'] = 'The Chat ID you provided is not valid.';
$string['error:wrongverification'] = 'Wrong code. Try again.';
$string['errortelegramsent'] = 'Error sending a Telegram message containing your verification code.';
$string['event:telegramsent'] = 'Telegram message sent.';
$string['event:telegramsentdescription'] = 'The user with ID {$a->userid} was sent a verification code via Telegram message. Information: {$a->debuginfo}';
$string['info'] = 'Have a verification code sent to the Telegram user you choose.';
$string['logindesc'] = 'Telegram message containing a 6-digit code sent to Telegram with Chat ID {$a}';
$string['loginoption'] = 'Have a code sent to your Telegram';
$string['loginskip'] = "I didn't receive a code";
$string['loginsubmit'] = 'Continue';
$string['logintitle'] = 'Enter the verification code sent to your Telegram';
$string['managefactor'] = 'Manage Telegram';
$string['managefactorbutton'] = 'Manage';
$string['manageinfo'] = 'You are using \'{$a}\' to authenticate.';
$string['pluginname'] = 'Telegram';
$string['privacy:metadata'] = 'The Telegram factor plugin does not store any personal data.';
$string['revokefactorconfirmation'] = 'Remove \'{$a}\' Telegram?';
$string['settings:apibaseurl'] = 'Telegram API base URL';
$string['settings:apibaseurl_help'] = 'The base URL of the Telegram API. This is usually https://api.telegram.org';
$string['settings:bottoken'] = 'Telegram bot token';
$string['settings:bottoken_help'] = 'The token of the Telegram bot that will send the messages. You can create a new bot using the <a href="https://telegram.me/BotFather" target="_blank">BotFather</a>. For more information, please refer to the <a href="https://core.telegram.org/bots#botfather" target="_blank">Telegram Bot API documentation</a>. The token should be in the following format: 123456789:ABCdefGhIJKlmnoPQRstuVWXyzABCD1234.';
$string['settings:bottokeninvalid'] = '<span class="text-danger"<i class="fa fa-times"></i> The bot token you provided is invalid. You need to provide a valid token to use this factor.</span>';
$string['settings:bottokenvalid'] = '<span class="text-success"><i class="fa fa-check"></i> The bot token you provided is valid.</span>';
$string['settings:duration'] = 'Validity duration';
$string['settings:duration_help'] = 'The period of time that the code is valid.';
$string['settings:heading'] = 'Users will receive a Telegram message with 6-digit code during login, which they must enter to complete the login process.';
$string['settings:heading_help'] = 'This factor uses the Telegram API to send a message with a 6-digit code to the user\'s Telegram account. The user must enter this code to complete the login process.

Users will need to register their Telegram Chat ID first.';
$string['setupfactor'] = 'Set up Telegram';
$string['setupfactorbutton'] = 'Set up';
$string['setupsubmitchatidhelp'] = 'Send code';
$string['setupsubmitcode'] = 'Save';
$string['summarycondition'] = 'Using a Telegram message one-time security code';
$string['telegramstring'] = '{$a->code} is your {$a->fullname} one-time security code.';
