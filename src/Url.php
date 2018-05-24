<?php
declare(strict_types = 1);

namespace SitemapGenerator;

use DateTimeInterface;
use DOMDocument;
use InvalidArgumentException;

/**
 * @link https://www.sitemaps.org/protocol.html
 */
class Url {

	const CHANGE_ALWAYS = 'always';
	const CHANGE_HOURLY = 'hourly';
	const CHANGE_DAILY = 'daily';
	const CHANGE_WEEKLY = 'weekly';
	const CHANGE_MONTHLY = 'monthly';
	const CHANGE_YEARLY = 'yearly';
	const CHANGE_NEVER = 'never';

	/**
	 * Url
	 *
	 * @var string
	 */
	protected $_url = '';

	/**
	 * Priority
	 *
	 * @var float
	 */
	protected $_priority = 0.0;

	/**
	 * Last Modified Date
	 *
	 * @var string|null
	 */
	protected $_lastModified = null;

	/**
	 * Frequency
	 *
	 * @var string|null
	 */
	protected $_frequency = null;

	/**
	 * Is Site Map
	 *
	 * @var bool
	 */
	protected $_isSitemap = false;

	/**
	 * Constructor
	 *
	 * @param string $url URL
	 */
	public function __construct($url) {
		$this->_url = $url;
	}

	/**
	 * Sets the frequency
	 *
	 * @param string $frequency Frequency
	 * @return $this
	 */
	public function frequency($frequency) {
		if (!in_array($frequency, [
			static::CHANGE_ALWAYS,
			static::CHANGE_HOURLY,
			static::CHANGE_DAILY,
			static::CHANGE_WEEKLY,
			static::CHANGE_MONTHLY,
			static::CHANGE_YEARLY,
			static::CHANGE_NEVER
		])) {
			throw new InvalidArgumentException(sprintf(
				'Invalid frequency `%s`',
				$frequency
			));
		}

		$this->_frequency = $frequency;

		return $this;
	}

	/**
	 * Wraps the URL into a <sitemap> tag instead of an <url> tag.
	 *
	 * @return $this
	 */
	public function sitemap() {
		$this->_isSitemap = true;

		return $this;
	}

	/**
	 * Sets the last modified date
	 *
	 * @param \DateTimeInterface $date
	 * @param string $format Format
	 * @return $this
	 */
	public function lastModified(DateTimeInterface $date, $format = 'c') {
		$this->_lastModified = $date->format($format);

		return $this;
	}

	/**
	 * Priority
	 *
	 * @param float $priority Priority
	 * @return $this
	 */
	public function priority(float $priority) {
		$this->_priority = $priority;

		return $this;
	}

	/**
	 * To string
	 *
	 * @return string
	 */
	public function toString() {
		$document = new DOMDocument();

		$tag = 'url';
		if ($this->_isSitemap) {
			$tag = 'sitemap';
		}

		$urlNode = $document->createElement($tag);

		$urlNode->appendChild($document->createElement('loc', $this->_url));

		if (!empty($this->_lastModified)) {
			$urlNode->appendChild($document->createElement('lastmod', $this->_lastModified));
		}

		if (!empty($this->_frequency)) {
			$urlNode->appendChild($document->createElement('changefreq', $this->_frequency));
		}

		if (!empty($this->_priority)) {
			$urlNode->appendChild($document->createElement('priority', (string)$this->_priority));
		}

		$document->appendChild($urlNode);

		return $document->saveXML($document->documentElement);
	}

}
