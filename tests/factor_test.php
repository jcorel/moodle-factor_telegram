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

use ReflectionClass;
use ReflectionException;
use tool_mfa\local\secret_manager;

/**
 * Tests for telegram factor.
 *
 * @covers      \factor_telegram\factor
 * @package     factor_telegram
 * @subpackage  tool_mfa
 * @copyright   2025 Jorge Courel <jgonzcou@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class factor_test extends \advanced_testcase {
    /**
     * Test set up user factor and verification code with a random chat id
     * @covers ::setup_user_factor
     * @covers ::check_verification_code
     * @covers ::revoke_user_factor
     *
     * @throws ReflectionException|
     */
    public function test_check_verification_code(): void {
        global $SESSION;
        $this->resetAfterTest();
        // Create and login a user and set up the chat id.
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        // Generate a fake chat id and save it in session.
        $chatid = '7332872871';
        $SESSION->tool_mfa_telegram_chatid = $chatid;
        $telegramfactor = \tool_mfa\plugininfo\factor::get_factor('telegram');
        $rc = new ReflectionClass($telegramfactor::class);
        $telegramdata = [];
        $factorinstance = $telegramfactor->setup_user_factor((object) $telegramdata);

        // Check if user factor was created successful.
        $this->assertNotEmpty($factorinstance);
        $this->assertCount(1, $telegramfactor->get_active_user_factors($user));

        // Create the secret code.
        $secretmanager = new secret_manager('telegram');
        $secretcode = $secretmanager->create_secret(1800, true);

        // Check verification code.
        $rcm = $rc->getMethod('check_verification_code');
        $this->assertTrue($rcm->invoke($telegramfactor, $secretcode));

        // Test that calling the revoke on the generic type revokes all.
        $telegramfactor->revoke_user_factor($factorinstance->id);
        $this->assertCount(0, $telegramfactor->get_active_user_factors($user));
        unset($SESSION->tool_mfa_telegram_chatid);
    }
}
