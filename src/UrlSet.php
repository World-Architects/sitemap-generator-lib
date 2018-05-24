<?php
declare(strict_types = 1);

namespace SitemapGenerator;

use DOMDocument;
use InvalidArgumentException;

/**
 * URL Set
 *
 * @link https://www.sitemaps.org/protocol.html
 */
class UrlSet {

	/**
	 * Maximum file size of a sitemap file in bytes
	 *
	 * @var int
	 */
	protected $maxFileSizeInBytes = 52428800;

	/**
	 * Maximum allowed URLs per file
	 *
	 * @var int
	 */
	protected $maxUrlCountPerFile = 50000;

	/**
	 * Internal URL counter
	 *
	 * @var int
	 */
	protected $urlCount = 0;

	/**
	 * Internal byte count
	 *
	 * @var int
	 */
	protected $byteCount = 260093;

	/**
	 * XML data as string
	 *
	 * @var string
	 */
	protected $data = '';

	/**
	 * Check if gzip compression is enabled
	 *
	 * @var bool
	 */
	protected $gzipEnabled = false;

	/**
	 * Number of files written in the case the URL count or file size is exceeded
	 *
	 * @var int
	 */
	protected $filesWritten = 0;

	/**
	 * @var bool
	 */
	protected $isSitemapIndex = false;

	/**
	 * Filename
	 *
	 * @var string|null
	 */
	protected $filename;

	/**
	 * Output folder
	 *
	 * @var string
	 */
	protected $outputFolder = '.' . DIRECTORY_SEPARATOR;

	/**
	 * Constructor
	 *
	 * @param string $filename Filename - without extension!
	 */
	public function __construct($filename = 'sitemap') {
		$this->filename = $filename;
	}

	/**
	 * Set the output folder
	 *
	 * @param string $folder Folder
	 * @return $this
	 */
	public function setOutputFolder($folder) {
		$this->outputFolder = $folder;
		if (!is_dir($folder)) {
			mkdir($folder);
		}

		return $this;
	}

	/**
	 * Enables gzip compression
	 *
	 * @return $this
	 */
	public function enableCompression() {
		$this->gzipEnabled = true;

		return $this;
	}

	/**
	 * Disabled gzip compression
	 *
	 * @return $this
	 */
	public function disableCompression() {
		$this->gzipEnabled = false;

		return $this;
	}

	/**
	 * Turns the object into XML
	 *
	 * @return string XML data
	 */
	public function toXml() {
		$tag = 'urlset';
		if ($this->isSitemapIndex) {
			$tag = 'sitemapindex';
		}

		$document = new DOMDocument();
		$document->formatOutput = true;
		$document->loadXML('<' . $tag . '>' . $this->data . '</' . $tag . '>');
		$document->documentElement->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

		return $document->saveXML();
	}

	/**
	 * Resets internal counters
	 *
	 * @return void
	 */
	public function resetCounters() {
		$this->urlCount = 0;
		$this->byteCount = 260093;
		$this->data = '';
	}

	/**
	 * Writes a sitemap file
	 *
	 * @return void
	 */
	public function writeFile() {
		$output = $this->toXml();

		$extension = 'xml';
		if ($this->filesWritten > 0) {
			$filename = $this->filename . $this->filesWritten;
		} else {
			$filename = $this->filename;
		}

		if ($this->gzipEnabled) {
			$output = gzencode($output);
			$extension = 'gz';
		}

		file_put_contents($this->outputFolder . $filename . '.' . $extension, $output);

		$this->filesWritten++;
		$this->resetCounters();
	}

	/**
	 * Get the current URL count
	 *
	 * @return int
	 */
	public function getUrlCount() {
		return $this->urlCount;
	}

	/**
	 * Get the current files size in bytes
	 *
	 * @return int
	 */
	public function getBytes() {
		// The 260093 bytes are the overhead caused by DOMDocuments formatting
		// (spaces and line breaks) when writing out the file, we need to add this.
		return mb_strlen($this->data, '8bit') + 260093;
	}

	/**
	 * Add an URL via string or as URL object
	 *
	 * @param string|\App\Sitemap\Url $url
	 * @return $this
	 */
	public function addUrl($url) {
		if (is_string($url)) {
			$url = new Url($url);
		}

		if (!$url instanceof Url) {
			throw new InvalidArgumentException(sprintf(
				'%s is not an instance of %s',
				gettype($url),
				Url::class
			));
		}

		$xml = $url->toString();
		$bytes = mb_strlen($xml, '8bit');

		if ($this->byteCount + $bytes > $this->maxFileSizeInBytes
			|| $this->urlCount === $this->maxUrlCountPerFile
		) {
			$this->writeFile();
		}

		$this->urlCount++;
		$this->data .= $xml;

		return $this;
	}

	/**
	 * Finishes the sitemap generation
	 *
	 * @return void
	 */
	public function finish() {
		$this->writeFile();
		$this->filesWritten = 0;
	}

}
