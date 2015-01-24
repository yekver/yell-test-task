<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class LinkParser extends Model
{
	public $parsedSite;
	public $site;

	/**
	 * @return array the validation rules.
	 */
	public function rules()
	{
		return [
			['site', 'required'],
			['site', 'validateSiteUrl']
		];
	}

	public function validateSiteUrl($attribute) {
		if (!$this->hasErrors()) {
			$this->parsedSite = parse_url($this->site);

			if (!isset($this->parsedSite['scheme']) || !isset($this->parsedSite['host'])) {
				$this->addError($attribute, 'Incorrect site format. (Example: http://example.com)');
				return;
			}

			//Normalization (we parse only the main page of the site)
			$this->site = $this->parsedSite['scheme'] . '://' . $this->parsedSite['host'] . '/';
		}
	}

	private function loadUrlData()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->site);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		curl_setopt($ch, CURLOPT_ENCODING, '');
		curl_setopt($ch, CURLOPT_USERAGENT, 'Opera/9.80 (; U; ru) Presto/2.5.24 Version/10.53');

		$html = curl_exec($ch);
		if (curl_exec($ch) === false) {
			throw new \Exception(curl_errno($ch) . ': ' . curl_error($ch));
		}
		curl_close($ch);

		return $html;
	}

	private function validateLink($link) {
		if(empty($link) || $link[0] === '#' || strpos($link, 'javascript') === 0) {
			return false;
		}

		return $link;
	}

	private function normalizeLink($link) {
		$parsedLink = parse_url($link);

		if (!isset($parsedLink['scheme'])) {
			$parsedLink['scheme'] = $this->parsedSite['scheme'];
		}

		if (!isset($parsedLink['host'])) {
			$parsedLink['host'] = $this->parsedSite['host'];
		}

		$normalizedLink = $parsedLink['scheme'] . '://' . $parsedLink['host'];

		if (isset($parsedLink['path'])) {
			$normalizedLink .= $parsedLink['path'];
		}

		if (isset($parsedLink['query'])) {
			$normalizedLink .= '?' . $parsedLink['query'];
		}

		return $normalizedLink;
	}

	private function getDomLinks($dom) {
		$linksList = array();
		foreach ($dom->getElementsByTagName('a') as $node)
		{
			$link = $node->getAttribute('href');

			if ($this->validateLink($link)) {
				$linksList[] = $this->normalizeLink($link);
			}
		}

		asort($linksList);

		return array_unique($linksList);
	}

	public function getIndexPageLinks() {
		$dom = new \DOMDocument();

		libxml_use_internal_errors(true);
		$dom->loadHTML($this->loadUrlData());
		libxml_clear_errors();

		return $this->getDomLinks($dom);
	}

	public function saveLinks($linksList) {
		$basePath = realpath(Yii::$app->basePath);
		file_put_contents("{$basePath}/uploads/{$this->parsedSite['host']}.txt", print_r($linksList, true), LOCK_EX);
	}
}