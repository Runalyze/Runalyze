<?php

namespace Runalyze\Profile\Weather\Source;

use Symfony\Component\Translation\TranslatorInterface;

interface SourceInterface
{
	/**
	 * @return int
	 */
	public function getInternalProfileEnum();

    /**
     * @return bool
     */
	public function requiresAttribution();

    /**
     * @param TranslatorInterface $translator
     * @param bool $withLink
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
	public function getAttribution(TranslatorInterface $translator, $withLink = true);

    /**
     * @param TranslatorInterface $translator
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
	public function getAttributionLabel(TranslatorInterface $translator);

    /**
     * @return string|null
     */
	public function getAttributionUrl();

    /**
     * @return bool
     */
	public function hasAttributionUrl();
}
