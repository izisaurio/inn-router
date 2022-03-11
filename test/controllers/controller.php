<?php

namespace controllers;

class controller {

	public function users(array $params) {
		echo 'id: ' . $params['id'];
	}

	public function action($params) {
		echo 'good bye';
	}

	public function get($params) {
		echo 'method ' . $params['method'];
	}
}