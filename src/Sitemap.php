<?php
declare(strict_types = 1);

namespace SitemapGenerator;

/**
 * @link https://www.sitemaps.org/protocol.html
 */
class Sitemap extends Url {

	/**
	 * Returns a <sitemap> element instead of <url>
	 *
	 * @var bool
	 */
	protected $_isSitemap = true;

}
