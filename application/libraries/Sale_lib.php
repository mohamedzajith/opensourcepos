<?php

class Sale_lib
{
	var $CI;

  	function __construct()
	{
		$this->CI =& get_instance();
	}

	function get_cart()
	{
		if(!$this->CI->session->userdata('cart'))
			$this->set_cart(array());

		return $this->CI->session->userdata('cart');
	}

	function set_cart($cart_data)
	{
		$this->CI->session->set_userdata('cart',$cart_data);
	}

	// Multiple Payments
	function get_payments()
	{
		if( !$this->CI->session->userdata( 'payments' ) )
			$this->set_payments( array( ) );

		return $this->CI->session->userdata('payments');
	}

	// Multiple Payments
	function set_payments($payments_data)
	{
		$this->CI->session->set_userdata('payments',$payments_data);
	}
	
	function get_comment() 
	{
		// avoid returning a null that results in a 0 in the comment if nothing is set/available
		$comment = $this->CI->session->userdata('comment');
    	return empty($comment) ? '' : $comment;
	}

	function set_comment($comment) 
	{
		$this->CI->session->set_userdata('comment', $comment);
	}

	function clear_comment() 	
	{
		$this->CI->session->unset_userdata('comment');
	}
	
	function get_invoice_number()
	{
		return $this->CI->session->userdata('sales_invoice_number');
	}
	
	function set_invoice_number($invoice_number, $keep_custom = FALSE)
	{
		$current_invoice_number = $this->CI->session->userdata('sales_invoice_number');
		if (!$keep_custom || empty($current_invoice_number))
		{
			$this->CI->session->set_userdata('sales_invoice_number', $invoice_number);
		}
	}
	
	function clear_invoice_number()
	{
		$this->CI->session->unset_userdata('sales_invoice_number');
	}
	
	function is_invoice_number_enabled() 
	{
		return $this->CI->session->userdata('sales_invoice_number_enabled') == 'true' ||
			$this->CI->session->userdata('sales_invoice_number_enabled') == '1';
	}
	
	function set_invoice_number_enabled($invoice_number_enabled)
	{
		return $this->CI->session->set_userdata('sales_invoice_number_enabled', $invoice_number_enabled);
	}
	
	function is_print_after_sale() 
	{
		return $this->CI->session->userdata('sales_print_after_sale') == 'true' ||
			$this->CI->session->userdata('sales_print_after_sale') == '1';
	}
	
	function set_print_after_sale($print_after_sale)
	{
		return $this->CI->session->set_userdata('sales_print_after_sale', $print_after_sale);
	}
	
	function get_email_receipt() 
	{
		return $this->CI->session->userdata('email_receipt');
	}

	function set_email_receipt($email_receipt) 
	{
		$this->CI->session->set_userdata('email_receipt', $email_receipt);
	}

	function clear_email_receipt() 	
	{
		$this->CI->session->unset_userdata('email_receipt');
	}

	function add_payment($payment_id, $payment_amount)
	{
		$payments = $this->get_payments();
		if( isset( $payments[$payment_id] ) )
		{
			//payment_method already exists, add to payment_amount
			$payments[$payment_id]['payment_amount'] = bcadd($payments[$payment_id]['payment_amount'], $payment_amount, PRECISION);
		}
		else
		{
			//add to existing array
			$payment = array( $payment_id=>
				array(
					'payment_type' => $payment_id,
					'payment_amount' => $payment_amount
					)
			);
			
			$payments += $payment;
		}

		$this->set_payments($payments);
		
		return true;
	}

	// Multiple Payments
	function edit_payment($payment_id, $payment_amount)
	{
		$payments = $this->get_payments();
		if(isset($payments[$payment_id]))
		{
			$payments[$payment_id]['payment_type'] = $payment_id;
			$payments[$payment_id]['payment_amount'] = $payment_amount;
			$this->set_payments($payments);
		}

		return false;
	}

	// Multiple Payments
	function delete_payment($payment_id)
	{
		$payments = $this->get_payments();
		unset( $payments[urldecode( $payment_id )] );
		$this->set_payments( $payments );
	}

	// Multiple Payments
	function empty_payments()
	{
		$this->CI->session->unset_userdata('payments');
	}

	// Multiple Payments
	function get_payments_total()
	{
		$subtotal = 0;
		foreach($this->get_payments() as $payments)
		{
		    $subtotal = bcadd($payments['payment_amount'], $subtotal, PRECISION);
		}

		return to_currency_no_money($subtotal);
	}

	// Multiple Payments
	function get_amount_due()
	{
		$payment_total = $this->get_payments_total();
		$sales_total = $this->get_total();
		
		return to_currency_no_money(bcsub($sales_total, $payment_total, PRECISION));
	}

	function get_customer()
	{
		if(!$this->CI->session->userdata('customer'))
			$this->set_customer(-1);

		return $this->CI->session->userdata('customer');
	}

	function set_customer($customer_id)
	{
		$this->CI->session->set_userdata('customer',$customer_id);
	}

	function get_mode()
	{
		if(!$this->CI->session->userdata('sale_mode'))
			$this->set_mode('sale');

		return $this->CI->session->userdata('sale_mode');
	}

	function set_mode($mode)
	{
		$this->CI->session->set_userdata('sale_mode',$mode);
	}

    function get_sale_location()
    {
        if(!$this->CI->session->userdata('sale_location'))
        {
             $location_id = $this->CI->Stock_location->get_default_location_id();
             $this->set_sale_location($location_id);
        }

        return $this->CI->session->userdata('sale_location');
    }

    function set_sale_location($location)
    {
        $this->CI->session->set_userdata('sale_location',$location);
    }
    
    function clear_sale_location()
    {
    	$this->CI->session->unset_userdata('sale_location');
    }
    
    function set_giftcard_remainder($value)
    {
    	$this->CI->session->set_userdata('giftcard_remainder',$value);
    }
    
    function get_giftcard_remainder()
    {
    	return $this->CI->session->userdata('giftcard_remainder');
    }
    
    function clear_giftcard_remainder()
    {
    	$this->CI->session->unset_userdata('giftcard_remainder');
    }
    
	function add_item($item_id, $quantity=1, $item_location, $discount=0, $price=null, $description=null, $serialnumber=null)
	{
		//make sure item exists	     
		if($this->validate_item($item_id) == false)
        {
            return false;
        }

		// Serialization and Description

		//Get all items in the cart so far...
		$items = $this->get_cart();

        //We need to loop through all items in the cart.
        //If the item is already there, get it's key($updatekey).
        //We also need to get the next key that we are going to use in case we need to add the
        //item to the cart. Since items can be deleted, we can't use a count. we use the highest key + 1.

        $maxkey=0;                       //Highest key so far
        $itemalreadyinsale=FALSE;        //We did not find the item yet.
		$insertkey=0;                    //Key to use for new entry.
		$updatekey=0;                    //Key to use to update(quantity)
        $item_info=$this->CI->Item->get_info($item_id,$item_location);
		foreach ($items as $item)
		{
            //We primed the loop so maxkey is 0 the first time.
            //Also, we have stored the key in the element itself so we can compare.

			if($maxkey <= $item['line'])
			{
				$maxkey = $item['line'];
			}

			if($item['item_id']==$item_id && $item['item_location']==$item_location)
			{
				$itemalreadyinsale=TRUE;
				$updatekey = $item['line'];
                if (!$item_info->is_serialized)
                {
                    $quantity += $items[$updatekey]['quantity'];
                }
			}
		}

		$insertkey=$maxkey+1;
		//array/cart records are identified by $insertkey and item_id is just another field.
		$price=$price!=null?$price:$item_info->unit_price;
		$total=$this->get_item_total($quantity, $price, $discount);
        $discounted_total=$this->get_item_total($quantity, $price, $discount, TRUE);
		//Item already exists and is not serialized, add to quantity
		if(!$itemalreadyinsale || $item_info->is_serialized)
		{
            $item = array(($insertkey)=>
                array(
                    'item_id'=>$item_id,
                    'item_location'=>$item_location,
                    'stock_name'=>$this->CI->Stock_location->get_location_name($item_location),
                    'line'=>$insertkey,
                    'name'=>$item_info->name,
                    'item_number'=>$item_info->item_number,
                    'description'=>$description!=null ? $description: $item_info->description,
                    'serialnumber'=>$serialnumber!=null ? $serialnumber: '',
                    'allow_alt_description'=>$item_info->allow_alt_description,
                    'is_serialized'=>$item_info->is_serialized,
                    'quantity'=>$quantity,
                    'discount'=>$discount,
                    'in_stock'=>$this->CI->Item_quantity->get_item_quantity($item_id, $item_location)->quantity,
                    'price'=>$price,
                    'total'=>$total,
                    'discounted_total'=>$discounted_total,
                )
            );
			//add to existing array
			$items+=$item;
		}
        else
        {
            $line = &$items[$updatekey];
            $line['quantity'] = $quantity;
            $line['total'] = $total;
            $line['discounted_total'] = $discounted_total;
        }

		$this->set_cart($items);
		
		return true;
	}
	
	function out_of_stock($item_id, $item_location)
	{
		//make sure item exists
		if($this->validate_item($item_id) == false)
        {
            return false;
        }

		$item_info = $this->CI->Item->get_info($item_id);
		//$item = $this->CI->Item->get_info($item_id);
		$item_quantity = $this->CI->Item_quantity->get_item_quantity($item_id,$item_location)->quantity;
		$quantity_added = $this->get_quantity_already_added($item_id,$item_location);

		if ($item_quantity - $quantity_added < 0)
		{
			return $this->CI->lang->line('sales_quantity_less_than_zero');
		}
		else if ($item_quantity - $quantity_added < $item_info->reorder_level)
		{
			return $this->CI->lang->line('sales_quantity_less_than_reorder_level');
		}

		return false;
	}
	
	function get_quantity_already_added($item_id, $item_location)
	{
		$items = $this->get_cart();
		$quanity_already_added = 0;
		foreach ($items as $item)
		{
			if($item['item_id']==$item_id && $item['item_location']==$item_location)
			{
				$quanity_already_added+=$item['quantity'];
			}
		}
		
		return $quanity_already_added;
	}
	
	function get_item_id($line_to_get)
	{
		$items = $this->get_cart();

		foreach ($items as $line=>$item)
		{
			if($line==$line_to_get)
			{
				return $item['item_id'];
			}
		}
		
		return -1;
	}

	function edit_item($line, $description, $serialnumber, $quantity, $discount, $price)
	{
		$items = $this->get_cart();
		if(isset($items[$line]))	
		{
			$line = &$items[$line];
			$line['description'] = $description;
			$line['serialnumber'] = $serialnumber;
			$line['quantity'] = $quantity;
			$line['discount'] = $discount;
			$line['price'] = $price;
			$line['total'] = $this->get_item_total($quantity, $price, $discount);
			$line['discounted_total'] = $this->get_item_total($quantity, $price, $discount, TRUE);
			$this->set_cart($items);
		}

		return false;
	}

	function is_valid_receipt(&$receipt_sale_id)
	{
		//POS #
		$pieces = explode(' ',$receipt_sale_id);

		if(count($pieces)==2 && strtolower($pieces[0]) == 'pos')
		{
			return $this->CI->Sale->exists($pieces[1]);
		}
		else 
		{
			$sale_info = $this->CI->Sale->get_sale_by_invoice_number($receipt_sale_id);
			if ($sale_info->num_rows() > 0)
			{
				$receipt_sale_id = 'POS ' . $sale_info->row()->sale_id;
				return true;
			}
		}

		return false;
	}
	
	function is_valid_item_kit($item_kit_id)
	{
		//KIT #
		$pieces = explode(' ',$item_kit_id);

		if(count($pieces)==2)
		{
			return $this->CI->Item_kit->exists($pieces[1]);
		}

		return false;
	}

	function return_entire_sale($receipt_sale_id)
	{
		//POS #
		$pieces = explode(' ',$receipt_sale_id);
		$sale_id = $pieces[1];

		$this->empty_cart();
		$this->remove_customer();

		foreach($this->CI->Sale->get_sale_items($sale_id)->result() as $row)
		{
			$this->add_item($row->item_id,-$row->quantity_purchased,$row->item_location,$row->discount_percent,$row->item_unit_price,$row->description,$row->serialnumber);
		}
		$this->set_customer($this->CI->Sale->get_customer($sale_id)->person_id);
	}
	
	function add_item_kit($external_item_kit_id,$item_location)
	{
		//KIT #
		$pieces = explode(' ',$external_item_kit_id);
		$item_kit_id = $pieces[1];
		
		foreach ($this->CI->Item_kit_items->get_info($item_kit_id) as $item_kit_item)
		{
			$this->add_item($item_kit_item['item_id'],$item_kit_item['quantity'],$item_location);
		}
	}

	function copy_entire_sale($sale_id)
	{
		$this->empty_cart();
		$this->remove_customer();

		foreach($this->CI->Sale->get_sale_items($sale_id)->result() as $row)
		{
			$this->add_item($row->item_id,$row->quantity_purchased,$row->item_location,$row->discount_percent,$row->item_unit_price,$row->description,$row->serialnumber);
		}
		foreach($this->CI->Sale->get_sale_payments($sale_id)->result() as $row)
		{
			$this->add_payment($row->payment_type,$row->payment_amount);
		}
		$this->set_customer($this->CI->Sale->get_customer($sale_id)->person_id);
	}
	
	function copy_entire_suspended_sale($sale_id)
	{
		$this->empty_cart();
		$this->remove_customer();

		foreach($this->CI->Sale_suspended->get_sale_items($sale_id)->result() as $row)
		{
			$this->add_item($row->item_id,$row->quantity_purchased,$row->item_location,$row->discount_percent,$row->item_unit_price,$row->description,$row->serialnumber);
		}
		foreach($this->CI->Sale_suspended->get_sale_payments($sale_id)->result() as $row)
		{
			$this->add_payment($row->payment_type,$row->payment_amount);
		}
		$suspended_sale_info=$this->CI->Sale_suspended->get_info($sale_id)->row();
		$this->set_customer($suspended_sale_info->person_id);
		$this->set_comment($suspended_sale_info->comment);
		$this->set_invoice_number($suspended_sale_info->invoice_number);
	}

	function delete_item($line)
	{
		$items=$this->get_cart();
		unset($items[$line]);
		$this->set_cart($items);
	}

	function empty_cart()
	{
		$this->CI->session->unset_userdata('cart');
	}

	function remove_customer()
	{
		$this->CI->session->unset_userdata('customer');
	}

	function clear_mode()
	{
		$this->CI->session->unset_userdata('sale_mode');
	}

	function clear_all()
	{
		$this->clear_mode();
		$this->empty_cart();
		$this->clear_comment();
		$this->clear_email_receipt();
		$this->clear_invoice_number();
		$this->clear_giftcard_remainder();
		$this->empty_payments();
		$this->remove_customer();
	}
	
	function is_customer_taxable()
	{
		$customer_id = $this->get_customer();
		$customer = $this->CI->Customer->get_info($customer_id);
		
		//Do not charge sales tax if we have a customer that is not taxable
		return $customer->taxable or $customer_id==-1;
	}

	function get_taxes()
	{
		//Do not charge sales tax if we have a customer that is not taxable
		if (!$this->is_customer_taxable())
		{
		   return array();
		}
		
		$taxes = array();
		foreach($this->get_cart() as $line=>$item)
		{
			$tax_info = $this->CI->Item_taxes->get_info($item['item_id']);

			foreach($tax_info as $tax)
			{
				$name = to_tax_decimals($tax['percent']) . '% ' . $tax['name'];
				$tax_amount = $this->get_item_tax($item['quantity'], $item['price'], $item['discount'], $tax['percent']);

				if (!isset($taxes[$name]))
				{
					$taxes[$name] = 0;
				}

				$taxes[$name] = bcadd($taxes[$name], $tax_amount, PRECISION);
			}
		}

		return $taxes;
	}
	
	function get_discount()
	{
		$discount = 0;
		foreach($this->get_cart() as $line=>$item)
		{
			if ($item['discount'] > 0)
			{
				$item_discount = $this->get_item_discount($item['quantity'], $item['price'], $item['discount']);
				$discount = bcadd($discount, $item_discount, PRECISION); 
			}
		}

		return $discount;
	}

	function get_subtotal($include_discount=FALSE, $exclude_tax=FALSE)
	{
		$subtotal = $this->calculate_subtotal($include_discount, $exclude_tax);		
		return to_currency_no_money($subtotal);
	}
	
	function get_item_total_tax_exclusive($item_id, $quantity, $price, $discount_percentage, $include_discount=FALSE) 
	{
		$tax_info = $this->CI->Item_taxes->get_info($item_id);
		$item_price = $this->get_item_total($quantity, $price, $discount_percentage, $include_discount);
		// only additive tax here
		foreach($tax_info as $tax)
		{
			$tax_percentage = $tax['percent'];
			$item_price = bcsub($item_price, $this->get_item_tax($quantity, $price, $discount_percentage, $tax_percentage), PRECISION);
		}
		
		return $item_price;
	}
	
	function get_item_total($quantity, $price, $discount_percentage, $include_discount=FALSE)  
	{
		$total = bcmul($quantity, $price, PRECISION);
		if ($include_discount)
		{
			$discount_amount = $this->get_item_discount($quantity, $price, $discount_percentage);

			return bcsub($total, $discount_amount, PRECISION);
		}

		return $total;
	}
	
	function get_item_discount($quantity, $price, $discount_percentage)
	{
		$total = bcmul($quantity, $price, PRECISION);
		$discount_fraction = bcdiv($discount_percentage, 100, PRECISION);

		return bcmul($total, $discount_fraction, PRECISION);
	}
	
	function get_item_tax($quantity, $price, $discount_percentage, $tax_percentage) 
	{
		$price = $this->get_item_total($quantity, $price, $discount_percentage, TRUE);

		if ($this->CI->config->config['tax_included'])
		{
			$tax_fraction = bcadd(100, $tax_percentage, PRECISION);
			$tax_fraction = bcdiv($tax_fraction, 100, PRECISION);
			$price_tax_excl = bcdiv($price, $tax_fraction, PRECISION);
			return bcsub($price, $price_tax_excl, PRECISION);
		}
		$tax_fraction = bcdiv($tax_percentage, 100, PRECISION);

		return bcmul($price, $tax_fraction, PRECISION);
	}

	function calculate_subtotal($include_discount=FALSE, $exclude_tax=FALSE) 
	{
		$subtotal = 0;
		foreach($this->get_cart() as $item)
		{
			if ($exclude_tax && $this->CI->config->config['tax_included'])
			{
				$subtotal = bcadd($subtotal, $this->get_item_total_tax_exclusive($item['item_id'], $item['quantity'], $item['price'], $item['discount'], $include_discount), PRECISION);
			}
			else 
			{
				$subtotal = bcadd($subtotal, $this->get_item_total($item['quantity'], $item['price'], $item['discount'], $include_discount), PRECISION);
			}
		}

		return $subtotal;
	}

	function get_total()
	{
		$total = $this->calculate_subtotal(TRUE);		
		if (!$this->CI->config->config['tax_included'])
		{
			foreach($this->get_taxes() as $tax)
			{
				$total = bcadd($total, $tax, PRECISION);
			}
		}

		return to_currency_no_money($total);
	}
    
    function validate_item(&$item_id)
    {
        //make sure item exists
        if(!$this->CI->Item->exists($item_id))
        {
            //try to get item id given an item_number
            $mode = $this->get_mode();
            $item_id = $this->CI->Item->get_item_id($item_id);

            if(!$item_id)
                return false;
        }

        return true;
    }
}
?>
