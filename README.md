# Telegram MFA Plugin for Moodle

This plugin provides Multi-Factor Authentication (MFA) for Moodle using Telegram as an authentication method.

## Installation

1. Download the plugin from the Moodle plugins directory or clone this repository
2. Extract the files to the directory: `moodle/admin/tool/mfa/factor/telegram/`
3. Log in as an administrator and visit the site administration page to complete the installation
4. Configure the plugin in Site Administration > Plugins > Multi-factor authentication > Manage factors

## Configuration

### Administrator Configuration

1. Enable the Telegram factor in the MFA administration page
2. Set up a Telegram bot:
    - Create a new bot using [BotFather](https://t.me/BotFather)
    - Get the bot token and enter it in the plugin settings

### User Setup

Users need to:

1. Visit their MFA preferences page
2. Click "Set up" for the Telegram factor
3. Find their Telegram Chat ID by contacting [@userinfobot](https://t.me/userinfobot) on Telegram
4. Enter their Chat ID in the setup form
5. Receive and enter a verification code sent to their Telegram account to complete the setup

## Usage

After setup, when users log in:

1. They will authenticate with their username and password as usual
2. A 6-digit verification code will be sent to their Telegram account
3. They must enter this code to complete the login process

## License

This plugin is licensed under the [GNU GPL v3 or later](http://www.gnu.org/copyleft/gpl.html).
