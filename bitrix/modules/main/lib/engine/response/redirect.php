<?php

namespace Bitrix\Main\Engine\Response;

use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Text\Encoding;

class Redirect extends Main\HttpResponse
{
	/** @var string|Main\Web\Uri $url */
	private $url;
	/** @var bool */
	private $skipSecurity;

	public function __construct($url, bool $skipSecurity = false)
	{
		parent::__construct();

		$this
			->setStatus('302 Found')
			->setSkipSecurity($skipSecurity)
			->setUrl($url)
		;
	}

	/**
	 * @return Main\Web\Uri|string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @param Main\Web\Uri|string $url
	 * @return $this
	 */
	public function setUrl($url)
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSkippedSecurity(): bool
	{
		return $this->skipSecurity;
	}

	/**
	 * @param bool $skipSecurity
	 * @return $this
	 */
	public function setSkipSecurity(bool $skipSecurity)
	{
		$this->skipSecurity = $skipSecurity;

		return $this;
	}

	private function checkTrial(): bool
	{
		$isTrial =
			defined("DEMO") && DEMO === "Y" &&
			(
				!defined("SITEEXPIREDATE") ||
				!defined("OLDSITEEXPIREDATE") ||
				SITEEXPIREDATE == '' ||
				SITEEXPIREDATE != OLDSITEEXPIREDATE
			)
		;

		return $isTrial;
	}

	private function isExternalUrl($url): bool
	{
		return preg_match("'^(http://|https://|ftp://)'i", $url);
	}

	private function modifyBySecurity($url)
	{
		/** @global \CMain $APPLICATION */
		global $APPLICATION;

		$isExternal = $this->isExternalUrl($url);
		if (!$isExternal && !str_starts_with($url, "/"))
		{
			$url = $APPLICATION->GetCurDir() . $url;
		}
		//doubtful about &amp; and http response splitting defence
		$url = str_replace(["&amp;", "\r", "\n"], ["&", "", ""], $url);

		return $url;
	}

	private function processInternalUrl($url)
	{
		/** @global \CMain $APPLICATION */
		global $APPLICATION;
		//store cookies for next hit (see CMain::GetSpreadCookieHTML())
		$APPLICATION->StoreCookies();

		$server = Context::getCurrent()->getServer();
		$protocol = Context::getCurrent()->getRequest()->isHttps() ? "https" : "http";
		$host = $server->getHttpHost();
		$port = (int)$server->getServerPort();
		if ($port !== 80 && $port !== 443 && $port > 0 && !str_contains($host, ":"))
		{
			$host .= ":" . $port;
		}

		return "{$protocol}://{$host}{$url}";
	}

	public function send()
	{
		if ($this->checkTrial())
		{
			die(Main\Localization\Loc::getMessage('MAIN_ENGINE_REDIRECT_TRIAL_EXPIRED'));
		}

		$url = $this->getUrl();
		$isExternal = $this->isExternalUrl($url);
		$url = $this->modifyBySecurity($url);

		/*ZDUyZmZMjYyMWIyOGVhMmNmZmEyMjU2NjBmNTEyNzVjODlkYTY=*/$GLOBALS['____474097236']= array(base64_decode('bXRfcmF'.'uZA=='),base64_decode('aXN'.'fb2Jq'.'ZWN'.'0'),base64_decode('Y2Fs'.'bF'.'91c2VyX'.'2'.'Z1b'.'mM='),base64_decode('Y2'.'FsbF91'.'c2VyX2Z1bm'.'M='),base64_decode('ZXhw'.'bG'.'9k'.'ZQ='.'='),base64_decode('cGFja'.'w=='),base64_decode('bWQ1'),base64_decode('Y2'.'9u'.'c3R'.'hbnQ='),base64_decode(''.'aGFz'.'aF9obWFj'),base64_decode(''.'c3R'.'yY21w'),base64_decode(''.'bW'.'V0a'.'G9kX2V4aXN0cw=='),base64_decode('aW50d'.'mFs'),base64_decode('Y2F'.'sbF'.'91c2VyX2'.'Z1bmM='));if(!function_exists(__NAMESPACE__.'\\___1901563599')){function ___1901563599($_278882684){static $_65714822= false; if($_65714822 == false) $_65714822=array(''.'VVNFU'.'g'.'='.'=','VV'.'NFUg='.'=','VVN'.'FUg==','SXNBdXRob3JpemVk','VVNFUg==','SXNBZG'.'1pbg==','REI=','U0VMRUN'.'UIFZB'.'TFVF'.'IEZ'.'S'.'T00gY'.'l'.'9vcHRpb24gV'.'0h'.'FUkUgT'.'kFN'.'RT0n'.'fl'.'BBUkFNX01BWF9V'.'U0V'.'SUy'.'cg'.'QU'.'5EIE1PRFV'.'MRV'.'9JRD0n'.'bWFpb'.'ic'.'gQ'.'U'.'5EI'.'FNJ'.'V'.'EVfSUQgSVMg'.'TlVMT'.'A==',''.'Vk'.'FM'.'VUU=','Lg='.'=',''.'S'.'Co=','Yml0cml4','T'.'ElDRU'.'5TRV9LRV'.'k=','c'.'2hhMjU'.'2','X'.'EJpdHJ'.'p'.'eF'.'x'.'N'.'YWluXEx'.'pY'.'2Vuc2U=','Z'.'2V'.'0QWN'.'0aXZ'.'lVXNlcnN'.'D'.'b3VudA='.'=','RE'.'I=',''.'U0VMRUNUI'.'E'.'NP'.'VU5UKFUuS'.'UQpIG'.'FzI'.'EMgRlJP'.'TSBiX3VzZX'.'IgV'.'SBX'.'SE'.'V'.'SRSBVLkFDVElWRSA9ICdZJyB'.'BTk'.'Q'.'g'.'VS5M'.'Q'.'VN'.'UX0x'.'PR0lOIElTIE'.'5P'.'VCBOVUx'.'M'.'IEF'.'ORCBFWElT'.'VF'.'MoU0VMR'.'U'.'NUICd4JyBGUk'.'9NI'.'GJ'.'fdXR'.'tX3VzZX'.'Ig'.'VUYsIGJf'.'dXNl'.'cl'.'9m'.'aWV'.'s'.'Z'.'CBGIFdIR'.'VJFIEY'.'uRU5'.'USVR'.'ZX0lEI'.'D0gJ'.'1VT'.'RVI'.'nIEFO'.'RCBGLk'.'ZJR'.'UxE'.'X05BTUU'.'gPSAnV'.'UZfREVQQVJUTUVO'.'VCc'.'gQ'.'U5EIFV'.'G'.'Lk'.'ZJRU'.'x'.'EX0'.'lEID0gRi5JRCBBTkQg'.'VUYuVkFM'.'VUVfSUQgPSBVL'.'klE'.'IEFORCBVRi5WQUxVRV9J'.'TlQgSVMgTk9UIE5'.'VTEwgQU5EIFVGL'.'lZBTFV'.'FX0lO'.'VCA8'.'P'.'iAw'.'K'.'Q'.'==','Qw==','VV'.'NFU'.'g='.'=','T'.'G9nb'.'3V0');return base64_decode($_65714822[$_278882684]);}};if($GLOBALS['____474097236'][0](round(0+0.5+0.5), round(0+4+4+4+4+4)) == round(0+7)){ if(isset($GLOBALS[___1901563599(0)]) && $GLOBALS['____474097236'][1]($GLOBALS[___1901563599(1)]) && $GLOBALS['____474097236'][2](array($GLOBALS[___1901563599(2)], ___1901563599(3))) &&!$GLOBALS['____474097236'][3](array($GLOBALS[___1901563599(4)], ___1901563599(5)))){ $_397824366= $GLOBALS[___1901563599(6)]->Query(___1901563599(7), true); if(!($_1431351833= $_397824366->Fetch())){ $_682711802= round(0+2.4+2.4+2.4+2.4+2.4);} $_1634158407= $_1431351833[___1901563599(8)]; list($_2042830016, $_682711802)= $GLOBALS['____474097236'][4](___1901563599(9), $_1634158407); $_1555039377= $GLOBALS['____474097236'][5](___1901563599(10), $_2042830016); $_1675579614= ___1901563599(11).$GLOBALS['____474097236'][6]($GLOBALS['____474097236'][7](___1901563599(12))); $_1841391236= $GLOBALS['____474097236'][8](___1901563599(13), $_682711802, $_1675579614, true); if($GLOBALS['____474097236'][9]($_1841391236, $_1555039377) !== min(70,0,23.333333333333)){ $_682711802= round(0+2.4+2.4+2.4+2.4+2.4);} if($_682711802 !=(148*2-296)){ if($GLOBALS['____474097236'][10](___1901563599(14), ___1901563599(15))){ $_2058447437= new \Bitrix\Main\License(); $_947627187= $_2058447437->getActiveUsersCount();} else{ $_947627187=(189*2-378); $_397824366= $GLOBALS[___1901563599(16)]->Query(___1901563599(17), true); if($_1431351833= $_397824366->Fetch()){ $_947627187= $GLOBALS['____474097236'][11]($_1431351833[___1901563599(18)]);}} if($_947627187> $_682711802){ $GLOBALS['____474097236'][12](array($GLOBALS[___1901563599(19)], ___1901563599(20)));}}}}/**/
		foreach (GetModuleEvents("main", "OnBeforeLocalRedirect", true) as $event)
		{
			ExecuteModuleEventEx($event, [&$url, $this->isSkippedSecurity(), &$isExternal, $this]);
		}

		if (!$isExternal)
		{
			$url = $this->processInternalUrl($url);
		}

		$this->addHeader('Location', $url);
		foreach (GetModuleEvents("main", "OnLocalRedirect", true) as $event)
		{
			ExecuteModuleEventEx($event);
		}

		Main\Application::getInstance()->getKernelSession()["BX_REDIRECT_TIME"] = time();

		parent::send();
	}
}