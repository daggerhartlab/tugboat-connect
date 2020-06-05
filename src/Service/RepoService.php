<?php

namespace TugboatConnect\Service;

interface RepoService {

	/**
	 * @return string
	 */
	public function getPrTitle();

	/**
	 * @return string
	 */
	public function getPrId();

	/**
	 * @return string
	 */
	public function getPrUrl();

	/**
	 * @param $string
	 */
	public function addComment( $string );

	/**
	 * @param $string
	 */
	public function updateIssueBody( $string );

}
