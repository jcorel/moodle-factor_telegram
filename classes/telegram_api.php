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
 * Contains the import_backup_helper class.
 *
 * @package     factor_telegram
 * @subpackage  tool_mfa
 * @author      Jorge Courel <jgonzcou@gmail.com>
 * @copyright   2025 Jorge Courel
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace factor_telegram;

use core\di;
use curl;
use dml_exception;
use Exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->libdir}/filelib.php");

/**
 * Class telegram_api
 *
 * @package factor_telegram
 */
class telegram_api {
    /**
     * @var string Default API base URL
     */
    private const API_BASE_URL_DEFAULT = 'https://api.telegram.org';
    /**
     * @var string API base URL
     */
    private string $apibaseurl;
    /**
     * @var string Bot token
     */
    private string $token;
    /**
     * @var curl Curl instance
     */
    private curl $curl;

    /**
     * Constructor.
     *
     * @throws dml_exception
     */
    public function __construct(string $token, ?curl $curl = null) {
        $this->curl = $curl ?? di::get(curl::class);
        $this->apibaseurl = get_config('factor_telegram', 'apibaseurl')
                            ?: self::API_BASE_URL_DEFAULT;
        $this->token = $token;
    }

    /**
     * Make the API URL for the given method.
     */
    private function make_api_url(string $method): string {
        return "{$this->apibaseurl}/bot{$this->token}/{$method}";
    }

    /**
     * Send a message to the user.
     *
     * @throws Exception
     */
    public function send_message(int $userid, string $text): bool {
        $params = ['chat_id' => $userid, 'text' => $text];
        $url = $this->make_api_url('sendMessage');
        try {
            $response = json_decode($this->curl->post($url, $params));
            return ($response instanceof stdClass) && isset($response->ok) && $response->ok;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Get user info from the chat id.
     *
     * @throws Exception
     */
    public function get_user_info(string $chatid): ?stdClass {
        $params = ['chat_id' => $chatid];
        $url = $this->make_api_url('getChat');
        try {
            $response = json_decode($this->curl->post($url, $params));
            if ((!$response instanceof stdClass) || !isset($response->ok) || !$response->ok) {
                return null;
            }
            return $response;
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Validate bot token.
     *
     * @throws Exception
     */
    public function validate_token(): bool {
        $url = $this->make_api_url('getMe');
        try {
            $response = json_decode($this->curl->post($url));
            return !empty($response->ok);
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Validate chat id.
     *
     * @throws Exception
     */
    public function is_valid_chatid(string $chatid): bool {
        $isvalidformat = preg_match('/^\d+$/', $chatid) === 1;
        if (empty($chatid) || !$isvalidformat) {
            return false;
        }
        $userinfo = $this->get_user_info($chatid);
        return ($userinfo instanceof stdClass) && isset($userinfo->ok) && $userinfo->ok;
    }
}
