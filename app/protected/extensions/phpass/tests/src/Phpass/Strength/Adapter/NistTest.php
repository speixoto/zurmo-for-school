<?php
/**
 * PHP Password Library
 *
 * @package PHPass\Tests
 * @category Cryptography
 * @author Ryan Chouinard <rchouinard at gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @link https://github.com/rchouinard/phpass Project at GitHub
 */

namespace Phpass\Strength\Adapter;

use \PHPUnit_Framework_TestCase as TestCase;

/**
 * PHP Password Library
 *
 * @package PHPass\Tests
 * @category Cryptography
 * @author Ryan Chouinard <rchouinard at gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @link https://github.com/rchouinard/phpass Project at GitHub
 */
class NistTest extends TestCase
{

    /**
     * @return array
     */
    public function passwordScoreProvider()
    {
        return array (
            array ('', 0),
            array ('M', 4),
            array ('My', 6),
            array ('MySuperS', 18),
            array ('MySuperSecretPasswor', 36),
            array ('MySuperSecretPassword', 37),
            array ('Super!Secret*Password', 43)
        );
    }

    /**
     * @test
     * @dataProvider passwordScoreProvider
     */
    public function checkMethodCalculatesExpectedResult($password, $expectedScore)
    {
        $adapter = new Nist;
        $this->assertEquals($expectedScore, $adapter->check($password));
    }

}
