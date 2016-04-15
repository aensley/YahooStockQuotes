<?php

namespace Aensley\YahooStockQuotes;

/**
 * Yahoo Stock Quotes class to retrieve quote data from Yahoo's API.
 *
 * @package	Aensley/YahooStockQuotes
 * @author	 Andrew Ensley
 */
class YahooStockQuotes
{

	/**
	 * The array of stock symbols to retrieve.
	 *
	 * @var array
	 */
	private $stockSymbols = array();

	/**
	 * Name of file where stock data is cached.
	 *
	 * @var string
	 */
	private $fileName = 'YahooStockQuotes.json';

	/**
	 * Associative array containing stock data.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * The most recent 'updated' date among all stock information stored as a timestamp.
	 *
	 * @var int
	 */
	private $timestamp;

	/**
	 * The base of the URL to fetch stock data from.
	 *
	 * @var string
	 */
	private $urlBase = 'https://query.yahooapis.com/v1/public/yql?format=json&env=%ENV%&q=%QUERY%';

	/**
	 * The fields to query from Yahoo.
	 *
	 * @var array
	 */
	private $queryFields = array('symbol', 'LastTradePriceOnly', 'ChangeinPercent', 'LastTradeDate', 'LastTradeTime');

	/**
	 * The query to pass to Yahoo's API for stock data.
	 *
	 * @var string
	 */
	private $urlQuery = 'SELECT %FIELDS% FROM yahoo.finance.quotes WHERE symbol IN (%STOCKS%)';

	/**
	 * The environment for the Yahoo API request.
	 *
	 * @var string
	 */
	private $urlEnv = 'store://datatables.org/alltableswithkeys';


	/**
	 * Creates the StockQuote object.
	 *
	 * @param array $symbols An array of desired stock symbols to retrieve.
	 */
	public function __construct($symbols = array())
	{
		if (empty($symbols) || !is_array($symbols)) {
			return;
		}

		$this->stockSymbols = $symbols;
		$this->fileName = dirname(realpath(__FILE__)) . '/' . $this->fileName;
		$this->getCachedData();
		if (!$this->isCurrent()) {
			$this->fetchNew();
		}
	}


	/**
	 * Retrieves cached data to save a request to Yahoo's API.
	 */
	private function getCachedData()
	{
		if (is_readable($this->fileName)) {
			$this->data = json_decode(file_get_contents($this->fileName), true);
		}
	}


	/**
	 * Checks whether the cached data is current enough.
	 * This essentially triggers a new update on the first request after 12am every day.
	 *
	 * @return bool True if current. False if not.
	 */
	private function isCurrent()
	{
		return (!empty($this->data) && !empty($this->data['date']) && date('Y-m-d') === $this->data['date']);
	}


	/**
	 * Retrieves new quote data from Yahoo.
	 */
	private function fetchNew()
	{
		$this->data['stocks'] = array();
		// Replace %ENV% in $urlBase with encoded $urlEnv
		$url = str_replace('%ENV%', urlencode($this->urlEnv), $this->urlBase);
		// Replace %FIELDS% in $urlQuery with query-formatted stock symbol list.
		$query = str_replace('%FIELDS%', implode(',', $this->queryFields), $this->urlQuery);
		// Replace %STOCKS% in $query with query-formatted stock symbol list.
		$query = str_replace('%STOCKS%', '"' . implode('","', $this->stockSymbols) . '"', $query);
		// Replace %QUERY% in $url ($urlBase with $urlEnv from above)
		// with encoded $query ($urlQuery with query-formatted stock symbols from above).
		$url = str_replace('%QUERY%', urlencode($query), $url);
		$data = @file_get_contents($url);
		if (!$data && function_exists('curl_init')) {
			$curlResource = curl_init();
			curl_setopt_array(
				$curlResource,
				array(
					CURLOPT_URL => $url,
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_CONNECTTIMEOUT => 5,
				)
			);
			$data = curl_exec($curlResource);
			curl_close($curlResource);
		}

		if ($data) {
			$data = json_decode($data, true);
			if (!empty($data) && !empty($data['query']['results']['quote'])) {
				$quotes = $data['query']['results']['quote'];
				// Is this an associative array?
				if (array_keys($quotes) !== range(0, count($quotes) - 1)) {
					// Yes, we were given a single quote not contained in a 0-indexed array. Make it one.
					$quotes = array($quotes);
				}

				foreach ($quotes as $quote) {
					$this->setSingleStock(
						$quote['symbol'],
						$quote['LastTradePriceOnly'],
						$quote['ChangeinPercent'],
						$quote['LastTradeDate'],
						$quote['LastTradeTime']
					);
				}

				$this->save();
			}
		}
	}


	/**
	 * Sets a single stock's properties.
	 *
	 * @param string $symbol The symbol for the stock.
	 * @param string $price  The stock's price.
	 * @param string $change The stock's change.
	 * @param string $date   The stock's last trade date.
	 * @param string $time   The stock's last trade time.
	 */
	private function setSingleStock($symbol = '', $price = '', $change = '', $date = '', $time = '')
	{
		if (!empty($symbol)) {
			$this->data['stocks'][$symbol] = array(
				'price' => '$' . $price,
				'change' => $change,
				'date' => $date,
				'time' => $time
			);
			$this->setNewDate($date, $time);
		}
	}


	/**
	 * Sets the new most recent 'updated' date among all stock information stored as a timestamp.
	 *
	 * @param string $date The date as a string.
	 * @param string $time The time as a string.
	 */
	private function setNewDate($date = '', $time = '')
	{
		if (!empty($date) && !empty($time)) {
			$newTimestamp = strtotime($date . ' ' . $time);
			if (empty($this->timestamp) || $this->timestamp < $newTimestamp) {
				$this->timestamp = $newTimestamp;
			}
		}
	}


	/**
	 * Saves stock data to the cached data file.
	 */
	private function save()
	{
		$this->data['date'] = date('Y-m-d');
		$this->data['timestamp'] = $this->timestamp;
		// If the file exists, it must be writable. If it doesn't exist, the directory must be writable.
		if (is_writable($this->fileName) || (!file_exists($this->fileName) && is_writable(dirname($this->fileName)))) {
			file_put_contents($this->fileName, json_encode($this->data));
		}
	}


	/**
	 * Gets the stock's properties.
	 *
	 * @param string $symbol The stock symbol.
	 *
	 * @return array An array of stock data with the structure: array('price' => '$10.00', 'change' => '+0.42%')
	 */
	private function getStock($symbol = '')
	{
		if (empty($symbol) || empty($this->data['stocks'][$symbol]) || !$this->isCurrent()) {
			return array(
				'price' => 'Unknown',
				'change' => 'Unknown'
			);
		}

		return $this->data['stocks'][$symbol];
	}


	/**
	 * Gets the price for the given stock symbol.
	 *
	 * @param string $symbol The stock symbol.
	 *
	 * @return string The price of the stock symbol.
	 */
	public function getPrice($symbol = '')
	{
		$stock = $this->getStock($symbol);
		return $stock['price'];
	}


	/**
	 * Gets the change for the given stock symbol.
	 *
	 * @param string $symbol The stock symbol.
	 *
	 * @return string The change of the stock symbol.
	 */
	public function getChange($symbol = '')
	{
		$stock = $this->getStock($symbol);
		return $stock['change'];
	}


	/**
	 * Gets the date the stock data was last updated.
	 *
	 * @param string $format The format to return the date in. Defaults to 'n/j/y g:ia'.
	 *
	 * @return string The date the stock data was last updated.
	 */
	public function getUpdatedDate($format = 'n/j/y g:ia')
	{
		return (empty($this->data['timestamp']) ? 'Unknown' : date($format, $this->data['timestamp']));
	}
}
