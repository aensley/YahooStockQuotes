<?php

use \Aensley\YahooStockQuotes\YahooStockQuotes;

class YahooStockQuotesTest extends \PHPUnit_Framework_TestCase {

	public $symbol = 'YHOO';
	public $stockQuotes;

	protected function setUp() {
		ini_set('allow_url_fopen', 'Off');
		$this->clearCache();
		$this->stockQuotes = new YahooStockQuotes(array($this->symbol));
	}

	public function testInstantiation() {
		$this->assertInstanceOf('\Aensley\YahooStockQuotes\YahooStockQuotes', $this->stockQuotes);
	}

	public function testEmptyInstantiation() {
		$this->assertInstanceOf('\Aensley\YahooStockQuotes\YahooStockQuotes', new YahooStockQuotes);
	}

	public function testUnknownPrice() {
		$this->assertEquals('Unknown', $this->stockQuotes->getPrice('invalid_symbol'));
	}

	public function testUnknownChange() {
		$this->assertEquals('Unknown', $this->stockQuotes->getChange('invalid_symbol'));
	}

	public function testGetPrice() {
		$price = $this->stockQuotes->getPrice($this->symbol);
		$this->assertInternalType('string', $price);
		// Example: $10.31 or $234,563.433333
		$this->assertRegExp('/^\$\d+(?:,\d{3})?\.\d+$/', $price);
	}

	public function testGetChange() {
		$change = $this->stockQuotes->getChange($this->symbol);
		$this->assertInternalType('string', $change);
		// Example: +13.38% or -0.0002%
		$this->assertRegExp('/^[+-]\d+\.\d+%$/', $change);
	}

	public function testGetUpdatedDate() {
		$date = $this->stockQuotes->getUpdatedDate('Y-m-d H:i:s');
		$this->assertInternalType('string', $date);
		// Example: 2016-04-12 07:22:14
		$this->assertRegExp('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date);
	}

	private function clearCache() {
		file_put_contents(__DIR__ . '/../../../src/Aensley/YahooStockQuotes/YahooStockQuotes.json', '');
	}

	protected function tearDown() {
		$this->clearCache();
	}
}
