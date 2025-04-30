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

namespace factor_telegram;

use coding_exception;
use core\exception\moodle_exception;
use core\output\notification;
use dml_exception;
use html_writer;
use moodle_url;
use MoodleQuickForm;
use stdClass;
use tool_mfa\local\factor\object_factor_base;
use tool_mfa\local\form\verification_field;
use tool_mfa\local\secret_manager;

/**
 * Telegram Factor implementation.
 *
 * @package     factor_telegram
 * @subpackage  tool_mfa
 * @author      Jorge Courel <jgonzcou@gmail.com>
 * @copyright   2025 Jorge Courel
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class factor extends object_factor_base {
    /** @var string Factor icon */
    protected $icon = 'fa-telegram';
    /** @var telegram_api Telegram api */
    private telegram_api $telegramapi;
    /**
     * Class constructor
     *
     * @param string $name factor name
     * @throws dml_exception
     */
    public function __construct($name) {
        $bottoken = get_config('factor_telegram', 'bottoken');
        $this->telegramapi = new telegram_api($bottoken);
        parent::__construct($name);
    }
    /**
     * Defines login form definition page for Telegram Factor.
     *
     * @param MoodleQuickForm $mform
     * @return MoodleQuickForm $mform
     */
    public function login_form_definition(MoodleQuickForm $mform): MoodleQuickForm {
        $mform->addElement(new verification_field());
        $mform->setType('verificationcode', PARAM_ALPHANUM);
        return $mform;
    }
    /**
     * Defines login form definition page after form data has been set.
     *
     * @param MoodleQuickForm $mform Form to inject global elements into.
     * @return MoodleQuickForm $mform
     * @throws moodle_exception
     */
    public function login_form_definition_after_data(MoodleQuickForm $mform): MoodleQuickForm {
        $this->generate_and_send_code();
        // Disable the form check prompt.
        $mform->disable_form_change_checker();
        return $mform;
    }
    /**
     * Implements login form validation for Telegram Factor.
     *
     * @param array $data
     * @return array
     * @throws coding_exception
     */
    public function login_form_validation(array $data): array {
        $return = [];
        if (!$this->check_verification_code($data['verificationcode'])) {
            $return['verificationcode'] = get_string('error:wrongverification', 'factor_telegram');
        }
        return $return;
    }
    /**
     * Gets the string for setup button on preferences page.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_setup_string(): string {
        return get_string('setupfactorbutton', 'factor_telegram');
    }
    /**
     * Gets the string for manage button on preferences page.
     *
     * @return string
     * @throws coding_exception
     */
    public function get_manage_string(): string {
        return get_string('managefactorbutton', 'factor_telegram');
    }
    /**
     * Defines setup_factor form definition page for Telegram Factor.
     *
     * @param MoodleQuickForm $mform
     * @return MoodleQuickForm $mform
     * @throws moodle_exception
     */
    public function setup_factor_form_definition(MoodleQuickForm $mform): MoodleQuickForm {
        global $OUTPUT, $USER, $DB;
        if (!empty($chatid =
            $DB->get_field('tool_mfa', 'label', ['factor' => $this->name, 'userid' => $USER->id, 'revoked' => 0]))) {
            redirect(new moodle_url('/admin/tool/mfa/user_preferences.php'), get_string('factorsetup', 'tool_mfa', $chatid), null,
                notification::NOTIFY_SUCCESS);
        }
        $mform->addElement('html', $OUTPUT->heading(get_string('setupfactor', 'factor_telegram'), 2));
        if (empty($this->get_chatid())) {
            $message = \html_writer::tag('div', '', ['class' => 'col-md-3']);
            $message .= \html_writer::tag('div', \html_writer::tag('p', get_string('chatiddescription', 'factor_telegram')),
                ['class' => 'col-md-9']);
            $mform->addElement('html', \html_writer::tag('div', $message, ['class' => 'row']));
            $mform->addElement('hidden', 'verificationcode', 0);
            $mform->setType('verificationcode', PARAM_ALPHANUM);
            $mform->addElement('text', 'chatid', get_string('addchatid', 'factor_telegram'),
                ['autocomplete' => 'tel', 'inputmode' => 'tel']);
            $mform->addHelpButton('chatid', 'chatid', 'factor_telegram');
            $mform->setType('chatid', PARAM_TEXT);
        }
        return $mform;
    }
    /**
     * Defines setup_factor form definition page after form data has been set.
     *
     * @param MoodleQuickForm $mform
     * @return MoodleQuickForm $mform
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function setup_factor_form_definition_after_data(MoodleQuickForm $mform): MoodleQuickForm {
        global $OUTPUT;
        $chatid = $this->get_chatid();
        if (empty($chatid)) {
            return $mform;
        }
        $duration = get_config('factor_telegram', 'duration');
        $code = $this->secretmanager->create_secret($duration, true);
        if (!empty($code)) {
            $this->telegram_verification_code($code, $chatid);
        }
        $message = get_string('logindesc', 'factor_telegram', '<b>' . $chatid . '</b><br/>');
        $message .= get_string('editchatidinfo', 'factor_telegram');
        $mform->addElement('html', html_writer::tag('p', $OUTPUT->notification($message, 'success')));
        $mform->addElement(new verification_field());
        $mform->setType('verificationcode', PARAM_ALPHANUM);
        $editchatid =
            html_writer::link(new moodle_url('/admin/tool/mfa/factor/telegram/editchatid.php', ['sesskey' => sesskey()]),
                get_string('editchatid', 'factor_telegram'), ['class' => 'btn btn-secondary', 'type' => 'button']);
        $mform->addElement('html', html_writer::tag('div', $editchatid, ['class' => 'float-sm-start col-md-4']));
        // Disable the form check prompt.
        $mform->disable_form_change_checker();
        return $mform;
    }
    /**
     * Returns the chat id from the current session or from the user profile data.
     *
     * @return string|null
     * @throws dml_exception
     */
    private function get_chatid(): ?string {
        global $SESSION, $USER, $DB;
        if (!empty($SESSION->tool_mfa_telegram_chatid)) {
            return $SESSION->tool_mfa_telegram_chatid;
        }
        $chatid = $DB->get_field('tool_mfa', 'label', ['factor' => $this->name, 'userid' => $USER->id, 'revoked' => 0]);
        if (!empty($chatid)) {
            return $chatid;
        }
        return null;
    }
    /**
     * Returns an array of errors, where array key = field id and array value = error text.
     *
     * @param array $data
     * @return array
     * @throws coding_exception
     * @throws dml_exception
     */
    public function setup_factor_form_validation(array $data): array {
        $errors = [];
        // Chat ID validation.
        if (!empty($data["chatid"]) && !$this->telegramapi->is_valid_chatid($data["chatid"])) {
            $errors['chatid'] = get_string('error:wrongchatid', 'factor_telegram');
        } else if (!empty($this->get_chatid())) {
            // Code validation.
            if (empty($data["verificationcode"])) {
                $errors['verificationcode'] = get_string('error:emptyverification', 'factor_telegram');
            } else if ($this->secretmanager->validate_secret($data['verificationcode']) !== $this->secretmanager::VALID) {
                $errors['verificationcode'] = get_string('error:wrongverification', 'factor_telegram');
            }
        }
        return $errors;
    }
    /**
     * Reset values of the session data of the given factor.
     *
     * @param int $factorid
     * @return void
     */
    public function setup_factor_form_is_cancelled(int $factorid): void {
        global $SESSION;
        if (!empty($SESSION->tool_mfa_telegram_chatid)) {
            unset($SESSION->tool_mfa_telegram_chatid);
        }
        // Clean temp secrets code.
        $secretmanager = new secret_manager('telegram');
        $secretmanager->cleanup_temp_secrets();
    }
    /**
     * Setup submit button string in given factor
     *
     * @return string|null
     * @throws coding_exception
     */
    public function setup_factor_form_submit_button_string(): ?string {
        global $SESSION;
        if (!empty($SESSION->tool_mfa_telegram_chatid)) {
            return get_string('setupsubmitcode', 'factor_telegram');
        }
        return get_string('setupsubmitchatidhelp', 'factor_telegram');
    }
    /**
     * Adds an instance of the factor for a user, from form data.
     *
     * @param stdClass $data
     * @return stdClass|null the factor record, or null.
     * @throws \moodle_exception
     */
    public function setup_user_factor(stdClass $data): ?stdClass {
        global $DB, $SESSION, $USER;
        // Handle chat id submission.
        if (empty($SESSION->tool_mfa_telegram_chatid)) {
            $SESSION->tool_mfa_telegram_chatid = !empty($data->chatid) ? $data->chatid : '';
            $addurl = new moodle_url('/admin/tool/mfa/action.php', ['action' => 'setup', 'factor' => 'telegram']);
            redirect($addurl);
        }
        // If the user somehow gets here through form resubmission.
        // We dont want two chat ids active.
        if ($DB->record_exists('tool_mfa', ['userid' => $USER->id, 'factor' => $this->name, 'revoked' => 0])) {
            return null;
        }
        $time = time();
        $label = $this->get_chatid();
        $row = new \stdClass();
        $row->userid = $USER->id;
        $row->factor = $this->name;
        $row->secret = '';
        $row->label = $label;
        $row->timecreated = $time;
        $row->createdfromip = $USER->lastip;
        $row->timemodified = $time;
        $row->lastverified = $time;
        $row->revoked = 0;
        $id = $DB->insert_record('tool_mfa', $row);
        $record = $DB->get_record('tool_mfa', ['id' => $id]);
        $this->create_event_after_factor_setup($USER);
        // Remove session chat id.
        unset($SESSION->tool_mfa_telegram_chatid);
        return $record;
    }
    /**
     * Returns an array of all user factors of given type.
     *
     * @param stdClass $user the user to check against.
     * @return array
     * @throws dml_exception
     */
    public function get_all_user_factors(stdClass $user): array {
        global $DB;
        $sql = 'SELECT *
                  FROM {tool_mfa}
                 WHERE userid = ?
                   AND factor = ?
                   AND label IS NOT NULL
                   AND revoked = 0';
        return $DB->get_records_sql($sql, [$user->id, $this->name]);
    }
    /**
     * Decides if a factor requires input from the user to verify.
     *
     * @return bool
     */
    public function has_input(): bool {
        return true;
    }
    /**
     * Decides if factor needs to be setup by user and has setup_form.
     *
     * @return bool
     */
    public function has_setup(): bool {
        return true;
    }
    /**
     * Decides if the setup buttons should be shown on the preferences page.
     *
     * @return bool
     */
    public function show_setup_buttons(): bool {
        return true;
    }
    /**
     * Returns true if factor class has factor records that might be revoked.
     * It means that user can revoke factor record from their profile.
     *
     * @return bool
     */
    public function has_revoke(): bool {
        return true;
    }
    /**
     * Generates and send the code for login to the user, stores codes in DB.
     *
     * @return int|null the instance ID being used.
     * @throws moodle_exception
     */
    private function generate_and_send_code(): ?int {
        global $DB, $USER;
        $duration = get_config('factor_telegram', 'duration');
        $instance = $DB->get_record('tool_mfa', ['factor' => $this->name, 'userid' => $USER->id, 'revoked' => 0]);
        if (empty($instance)) {
            return null;
        }
        $secret = $this->secretmanager->create_secret($duration, false);
        if (!empty($secret)) {
            $this->telegram_verification_code($secret, $instance->label);
        }
        return $instance->id;
    }
    /**
     * This function sends an Telegram code to the user based on the chatid provided.
     *
     * @param int $secretcode the secret to send.
     * @param string|null $chatid the chatid to send the verification code to.
     * @return void
     * @throws moodle_exception
     */
    private function telegram_verification_code(int $secretcode, ?string $chatid): void {
        global $CFG, $SITE;
        $url = new moodle_url($CFG->wwwroot);
        $content = ['fullname' => $SITE->fullname, 'url' => $url->get_host(), 'code' => $secretcode];
        $message = get_string('telegramstring', 'factor_telegram', $content);
        $this->telegramapi->send_message($chatid, $message);
    }
    /**
     * Verifies entered code against stored DB record.
     *
     * @param string $enteredcode
     * @return bool
     */
    private function check_verification_code(string $enteredcode): bool {
        return $this->secretmanager->validate_secret($enteredcode) === secret_manager::VALID;
    }
    /**
     * Returns all possible states for a user.
     *
     * @param \stdClass $user
     * @return array
     */
    public function possible_states(\stdClass $user): array {
        return [\tool_mfa\plugininfo\factor::STATE_PASS, \tool_mfa\plugininfo\factor::STATE_NEUTRAL,
            \tool_mfa\plugininfo\factor::STATE_FAIL, \tool_mfa\plugininfo\factor::STATE_UNKNOWN];
    }
    /**
     * Get the login description associated with this factor.
     * Override for factors that have a user input.
     *
     * @return string The login option.
     * @throws dml_exception|coding_exception
     */
    public function get_login_desc(): string {
        $chatid = $this->get_chatid();
        if (empty($chatid)) {
            return get_string('errortelegramsent', 'factor_telegram');
        }
        return get_string('logindesc', 'factor_' . $this->name, $chatid);
    }
}
