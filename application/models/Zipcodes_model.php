<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Zipcodes_model extends MY_Model
{
    public $table = 'zipcodes';
    public $primary_key = 'id';
	
	public function __construct()
	{
		parent::__construct();
	}
}