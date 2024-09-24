<?php

namespace Bitrix\Sign\Type;

use Bitrix\Sign\Helper\StringHelper;

/**
 * @see \Bitrix\Sign\Callback\Messages\Member\InviteToSign::getProvider()
 * @todo sync with enum
 */
final class ProviderCode
{
	public const GOS_KEY = 'GOS_KEY';
	public const TAXCOM = 'TAXCOM';
	public const SES_RU = 'SES_RU';
	public const SES_COM = 'SES_COM';

	/**
	 * @return array<self::*>
	 */
	public static function getAll(): array
	{
		return [
			self::GOS_KEY,
			self::TAXCOM,
			self::SES_RU,
			self::SES_COM,
		];
	}

	public static function isValid(string $providerCode): bool
	{
		return in_array($providerCode, self::getAll(), true);
	}

	/**
	 * @param string $providerLikeString
	 *
	 * @return self::*|null
	 */
	public static function createFromProviderLikeString(string $providerLikeString): ?string
	{
		if (self::isValid($providerLikeString))
		{
			return $providerLikeString;
		}

		$convertedProviderCode = StringHelper::convertKebabCaseToScreamingSnakeCase($providerLikeString);
		if ($providerLikeString === 'goskey')
		{
			$convertedProviderCode = self::GOS_KEY;
		}

		return self::isValid($convertedProviderCode) ? $convertedProviderCode : null;
	}
}
