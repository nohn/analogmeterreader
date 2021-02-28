<?php declare(strict_types=1);

/**
 * Analog Meter Reader
 *
 * Library for reading analog meters
 *
 * PHP version 7.4
 *
 * LICENCE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Sebastian Nohn <sebastian@nohn.net>
 * @copyright 2021 Sebastian Nohn
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License version 3.0
 */

use nohn\AnalogMeterReader\AnalogMeter;
use PHPUnit\Framework\TestCase;

final class NumberFromFileTest extends TestCase
{
    public function testReadFromFile(): void
    {
        $file = __DIR__ . '/resources/images/1/nohn1.png';
        $amr = new AnalogMeter($file, 'r');
        $this->assertEquals(1, $amr->getValue(), "Expected $file to be 1");
    }
}