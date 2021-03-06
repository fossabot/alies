<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends Vet_Controller {

	# constructor
	public function __construct()
	{
		parent::__construct();
		
		# models
		$this->load->model('Products_model', 'products');
		$this->load->model('Product_type_model', 'prod_type');
		$this->load->model('Stock_model', 'stock');
		$this->load->model('Product_price_model', 'pprice');
		$this->load->model('Procedures_model', 'procedures');
		$this->load->model('Booking_code_model', 'booking');
		$this->load->model('Events_products_model', 'eprod');
	}
	
	public function index()
	{
		$data = array(
						"last_created" 		=> $this->products->fields('id, name, created_at')->limit(5)->order_by("created_at", "desc")->get_all(),
						"last_modified" 	=> $this->products->fields('id, name, updated_at')->limit(5)->order_by("updated_at", "desc")->get_all(),
						//"total_products" 	=> $this->products->count_rows(),
						"search_q"			=> $this->input->post('name'),
						"search"			=> ($this->input->post('submit')) ? $this->products->group_start()->like('name', $this->input->post('name'), 'both')->or_like('short_name', $this->input->post('name'), 'both')->group_end()->limit(25)->get_all() : false,
						"product_types"		=> $this->prod_type->with_products('fields:*count*')->get_all()
						);
						
		$this->_render_page('product_index', $data);
	}
	
	public function product_price($id = false)
	{
		if ($id)
		{
			
			if ($this->input->post('submit'))
			{
				if ($this->input->post('submit') == "edit")
				{
					$this->pprice
							->where(array(
											"id" 	=> $this->input->post('price_id')
									))
							->update(array(
											"price" => $this->input->post('price'),
									));
				}
				else
				{
					$this->pprice->insert(array(
												'volume' 		=> $this->input->post('volume'), 
												'price' 		=> $this->input->post('price'), 
												'product_id' 	=> $id
										));
				}
			}
			$data = array(
							"product" 		=> $this->products
													->with_prices('fields:volume, price, id')
													//->with_type()
													->where(array("sellable" => 1))
													->fields('name, updated_at, unit_sell')
													->get($id)
						);
			$this->_render_page('product_price_edit', $data);
		}
		else
		{
			$data = array(
							"products" 		=> $this->products
													->with_prices('fields:volume, price')
													//->with_type()
													->where(array("sellable" => 1))
													->fields('name, updated_at, unit_sell')
													->get_all()
						);
					
			$this->_render_page('product_price_list', $data);
		}
	}
	
	public function remove_product_price($id)
	{
		$to_remove_price = $this->pprice->get($id);
		$this->pprice->delete($id);
		
		redirect('/products/product_price/' . $to_remove_price['product_id']);
	}
	
	public function product_list($id_or_product = false)
	{		
		// defaults
		$products = false;
		
		if ($id_or_product)
		{
			if ($id_or_product == "other")
			{
				$products = $this->products
									->with_prices('fields:volume, price')
									->with_booking_code()
									->with_type('fields:name')
									->where('type', '0')
									->get_all();
			}
			elseif ($id_or_product)
			{
				$products = $this->products
									->with_prices('fields:volume, price')
									->with_booking_code()
									->with_type('fields:name')
									->where('type', $id_or_product)
									->get_all();
			}
		}
		$data = array(
						"products" 		=> $products, 
						"types" 		=> $this->prod_type->get_all()
					);
			
		$this->_render_page('products_list', $data);
	}	
	
	public function product($id = false)
	{
		$update = false;
		if ($this->input->post('submit'))
		{
			$booking = $this->booking->fields('btw')->get($this->input->post('booking_code'));
			// var_dump($booking);
			
			$input = array (
								"name" 				=> $this->input->post('name'),
								"short_name" 		=> $this->input->post('short_name'),
								"producer" 			=> $this->input->post('producer'),
								"supplier" 			=> $this->input->post('supplier'),
								"posologie" 		=> $this->input->post('posologie'),
								"toedieningsweg" 	=> $this->input->post('toedieningsweg'),
								"type" 				=> $this->input->post('type'),
								"offset"			=> $this->input->post('offset'),
								"buy_volume" 		=> $this->input->post('buy_volume'),
								"sell_volume" 		=> $this->input->post('sell_volume'),
								"buy_price"			=> $this->input->post('buy_price'),
								"unit_buy" 			=> $this->input->post('unit_buy'),
								"unit_sell" 		=> $this->input->post('unit_sell'),
								"input_barcode" 	=> $this->input->post('input_barcode'),
								"btw_buy" 			=> $this->input->post('btw_buy'),
								"btw_sell" 			=> $booking['btw'],
								"vaccin" 			=> (is_null($this->input->post('vaccin')) ? 0 : 1),
								"vaccin_freq" 		=> $this->input->post('vaccin_freq'),
								"booking_code" 		=> $this->input->post('booking_code'),
								"delay" 			=> $this->input->post('delay'),
								"comment" 			=> $this->input->post('comment'),
								"sellable" 			=> (is_null($this->input->post('sellable')) ? 0 : 1),
								"limit_stock" 		=> $this->input->post('limit_stock')
							);
							
			if ($this->input->post('submit') == "add")
			{
				$id = $this->products->insert($input);
				$update = $id;
			}
			elseif ($this->input->post('submit') == "edit")
			{
				$update = $this->products->update($input, $id);
			}
		}
		
		$data = array(
						'product' 	=> ($id) ? $this->products->with_prices('fields:id, volume, price')->get($id) : false,
						'type' 		=> $this->prod_type->get_all(),
						'update'	=> $update,
						'booking'	=> $this->booking->get_all(),
						'history_1m'	=> $this->eprod->fields('volume')->where('created_at > DATE_ADD(NOW(), INTERVAL -30 DAY)', null, null, false, false, true)->where(array("product_id" => $id))->get_all(),
						'history_6m'	=> $this->eprod->fields('volume')->where('created_at > DATE_ADD(NOW(), INTERVAL -180 DAY)', null, null, false, false, true)->where(array("product_id" => $id))->get_all(),
						'history_1y'	=> $this->eprod->fields('volume')->where('created_at > DATE_ADD(NOW(), INTERVAL -365 DAY)', null, null, false, false, true)->where(array("product_id" => $id))->get_all(),
						);
		$this->_render_page('product_detail', $data);
	}
	
	/*
	delete product
	*/
	public function delete_product($id)
	{
		# in order to delete a product, it might be worth it to check wheter we still have stock ?
		$this->products->delete($id);
		redirect('/products/product_list');
	}
	
	# ajax request to return lot nr and eol date (in case there is no lotnr)
	public function get_lot_nr()
	{
		$result = $this->stock
			->fields('lotnr, eol, volume, barcode, location')
			->where(array(
							"product_id" 		=> $this->input->post('pid')
							))
			->get_all();
			
		echo ($result) ? json_encode($result) : json_encode(array());
	}

	# get product by barcode, ajax return
	public function get_product_by_barcode()
	{
		$barcode = $this->input->get('barcode');
		$location = $this->input->get('loc');
		$result = $this->stock
					->fields('eol, barcode, volume')
					->with_products('fields: name, unit_sell, btw_sell, booking_code')
				->where(array(
				'barcode' 	=> $barcode,
				'location' 	=> $location
				))->get();
			
		echo ($result) ? json_encode($result) : json_encode(array());
	}
	
	
	/*
		get_product is used in stock_add
		cause we can only add stock products
	*/
	public function get_product()
	{
		$query = $this->input->get('query');
		
		$return = array();
		
		if (strlen($query) > 1)
		{
			# products
			$result = $this->products
								->fields('id, name, type, buy_volume, unit_buy, sell_volume, unit_sell, buy_price')
								->with_type()
								->where('name','like', $query, true)
								->limit(10)
								->order_by("type", "ASC")
								->get_all();

			# in case no results
			if ($result)
			{				
				foreach ($result as $r)
				{
					
					$return[] = array(
								"value" => $r['name'],
								"data" 	=> array(
													"type" 				=> (isset($r['type']['name']) ? $r['type']['name'] : "other"), 
													"id" 				=> $r['id'],
													"buy_volume"		=> $r['buy_volume'],
													"unit_buy"			=> $r['unit_buy'],
													"sell_volume"		=> $r['sell_volume'],
													"unit_sell"			=> $r['unit_sell'],
													"buy_price"			=> $r['buy_price'],
												)
								);
				}
			}
		}
		echo json_encode(array("query" => $query, "suggestions" => $return));
	}
	
	public function gs1_to_product()
	{
		$gs1 = $this->input->get('gs1');
		
		$result = $this->products
							->fields('id, name')
							->limit(2)
							->where('input_barcode', $gs1)
							->get();
		if ($result) 
		{
			echo json_encode(array("state" => 1, $result));
		}
		else
		{
			echo json_encode(array("state" => 0));
		}
	}
	
	# return an ajax readable object of possible results
	public function get_product_or_procedure()
	{
		$query = $this->input->get('query');
		$location = $this->input->get('loc');
		$return = array();
		
		/*
			Searching for a :
				- product w/ stock
				- procedure
			
		*/
		if (strlen($query) > 1)
		{
			# products
			$result = $this->products
								->fields('id, name, type, unit_sell, btw_sell, booking_code, vaccin, vaccin_freq')
								->with_type()
								->with_prices('fields: volume, price')
								->with_stock('fields: location, eol, lotnr, volume, barcode, state', 'where:`state`=\'1\'')
								->where('name','like', $query, true)
								->where('sellable', '1')
								->limit(10)
								->order_by("type", "ASC")
								->get_all();

			# in case no results
			if ($result)
			{				
				foreach ($result as $r)
				{
					$stock = array();
					$prices = array();
					
					# there is stock
					if (isset($r['stock'])) 
					{
						foreach ($r['stock'] as $s)
						{
							$stock[] = array(
												"location" 	=> $s['location'], 
												"lotnr" 	=> $s['lotnr'], 
												"volume" 	=> $s['volume'], 
												"barcode" 	=> $s['barcode'], 
												"eol" 		=> $s['eol']
												);
						}
					}
					# there are prices
					if ($r['prices']) 
					{
						foreach ($r['prices'] as $s)
						{
							$prices[] = array(
												"volume" 	=> $s['volume'], 
												"price" 	=> $s['price'], 
												);
						}
					}
					$return[] = array(
								"value" => $r['name'],
								"data" 	=> array(
													"type" 		=> (isset($r['type']['name']) ? $r['type']['name'] : "other"), 
													"id" 		=> $r['id'],
													"stock"		=> $stock,
													"prices"	=> $prices,
													"unit"		=> $r['unit_sell'],
													"btw"		=> $r['btw_sell'],
													"booking"	=> $r['booking_code'],
													"vaccin"	=> $r['vaccin'],
													"vaccin_freq"	=> $r['vaccin_freq'],
													"prod"		=> 1
												)
								);
				}
			}
			
			# procedures
			$result = $this->procedures
								->fields('id, name, price, booking_code')
								->where('name','like', $query, true)
								->get_all();
						
			if ($result)
			{				
				foreach ($result as $r)
				{
						$return[] = array(
										"value" => $r['name'],
										"data" 	=> array(
														"type" 		=> "Proc", 
														"id" 		=> $r['id'],
														"price"		=> $r['price'],
														"btw"		=> "21",
														"booking"	=> $r['booking_code'],
														"prod"		=> 0
													)
									);
											
				}			
			}			
		}
	
		echo json_encode(array("query" => $query, "suggestions" => $return));
	}
}
