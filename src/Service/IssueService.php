<?php

namespace TugboatConnect\Service;

interface IssueService {

	/**
	 * @return string
	 */
	public function getHost();

	/**
	 * @param string $string
	 */
	public function addComment( $string );

	/**
	 * @param $title
	 * @param $url
	 */
	public function addRemoteLink( $title, $url );

	/**
	 * @param $url
	 *
	 * @return bool
	 */
	public function remoteLinkExists( $url );

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public function formatComment( $string );
}
