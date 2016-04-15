# YahooStockQuotes

Yahoo Stock Quotes in PHP

[![MIT License](https://img.shields.io/badge/license-MIT-blue.svg)](https://github.com/aensley/YahooStockQuotes/blob/master/LICENSE) [![Build Status](https://travis-ci.org/aensley/YahooStockQuotes.svg)](https://travis-ci.org/aensley/YahooStockQuotes) [![HHVM Test Status](https://img.shields.io/hhvm/aensley/yahoo-stock-quotes.svg)](http://hhvm.h4cc.de/package/aensley/yahoo-stock-quotes) [![GitHub Issues](https://img.shields.io/github/issues-raw/aensley/YahooStockQuotes.svg)](https://github.com/aensley/YahooStockQuotes/issues) [![GitHub Downloads](https://img.shields.io/github/downloads/aensley/YahooStockQuotes/total.svg)](https://github.com/aensley/YahooStockQuotes/releases) [![Packagist Downloads](https://img.shields.io/packagist/dt/aensley/yahoo-stock-quotes.svg)](https://packagist.org/packages/aensley/yahoo-stock-quotes)

[![Code Climate Grade](https://codeclimate.com/github/aensley/YahooStockQuotes/badges/gpa.svg)](https://codeclimate.com/github/aensley/YahooStockQuotes) [![Code Climate Issues](https://img.shields.io/codeclimate/issues/github/aensley/YahooStockQuotes.svg)](https://codeclimate.com/github/aensley/YahooStockQuotes) [![Codacy Grade](https://api.codacy.com/project/badge/grade/753efb995ff64b9087cf2e4952e91038)](https://www.codacy.com/app/awensley/YahooStockQuotes) [![SensioLabsInsight](https://img.shields.io/sensiolabs/i/bc0dd7ac-b413-44a3-bcb4-55e2ab1808d9.svg)](https://insight.sensiolabs.com/projects/bc0dd7ac-b413-44a3-bcb4-55e2ab1808d9)

[![Code Climate Test Coverage](https://codeclimate.com/github/aensley/YahooStockQuotes/badges/coverage.svg)](https://codeclimate.com/github/aensley/YahooStockQuotes/coverage) [![Codacy Test Coverage](https://api.codacy.com/project/badge/coverage/753efb995ff64b9087cf2e4952e91038)](https://www.codacy.com/app/awensley/YahooStockQuotes) [![Codecov.io Test Coverage](https://codecov.io/github/aensley/YahooStockQuotes/coverage.svg?branch=master)](https://codecov.io/github/aensley/YahooStockQuotes?branch=master) [![Coveralls Test Coverage](https://coveralls.io/repos/github/aensley/YahooStockQuotes/badge.svg?branch=master)](https://coveralls.io/github/aensley/YahooStockQuotes?branch=master)

## What it does

This library makes it simple to access any number of stock prices (and their changes) in your code. It limits itself to one update per day to save your server's (and Yahoo's) resources. It consists of [one code file](https://github.com/aensley/YahooStockQuotes/blob/master/src/Aensley/YahooStockQuotes/YahooStockQuotes.php) and [one cache file](https://github.com/aensley/YahooStockQuotes/blob/master/src/Aensley/YahooStockQuotes/YahooStockQuotes.json). No database necessary.

Merely pass an array of your desired stocks' symbols to the `YahooStockQuotes` constructor and use the [three public functions](#example-usage) where you need them.

Simple.

## Requirements

There must be a `YahooStockQuotes.json` file in the same directory as the `YahooStockQuotes.php` file.

`YahooStockQuotes.json` must be **WRITABLE** by the user who owns the PHP process (apache, www-data, nginx, hhvm, etc.).

If the file does not exist or is not writable, every page view will require a new request to Yahoo's servers, which will slow down all page views drastically and get your server blocked by Yahoo.

## Example usage

```php
<?php

include 'YahooStockQuotes.php';
$stockSymbols = array('YHOO');
$stockQuotes = new \Aensley\YahooStockQuotes\YahooStockQuotes($stockSymbols);

?><html>
	<head>
		<title>Stock Test</title>
	</head>
	<body>
		Price: <?php echo $stockQuotes->getPrice('YHOO'); ?>
		<br/>
		Change: <?php echo $stockQuotes->getChange('YHOO'); ?>
		<br/>
		Last updated: <?php echo $stockQuotes->getUpdatedDate(); ?>
	</body>
</html>
```

----

[![Supercharged by ZenHub.io](https://raw.githubusercontent.com/ZenHubIO/support/master/zenhub-badge.png)](https://zenhub.io)
