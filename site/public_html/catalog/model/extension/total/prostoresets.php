<?php
class ModelExtensionTotalProstoresets extends Model {


	public function getTotal($total) {
		$this->load->language('extension/total/prostoresets');

		$this->load->model('extension/module/prostore');

		$setids = array();
		if (isset($this->session->data['prostoresetid']) && $this->session->data['prostoresetid']) {
			$setids = $this->session->data['prostoresetid'];
		}

		$cartProducts = array();

		foreach ($this->cart->getProducts() as $product) {
			$cartProducts[$product['product_id']]['quantity'] = $product['quantity'];
			$cartProducts[$product['product_id']]['price'] = $product['price'];
		}



		foreach ($setids as  $setid) { 

			$setInfo = $this->model_extension_module_prostore->getSetInfo($setid);

			$prostoresets = $this->model_extension_module_prostore->getSetDiscount($total,$setInfo,$cartProducts);

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $voucher) {
					$prostoresets += $voucher['amount'];
				}
			}

			if ($prostoresets['discount']) {

				$total['totals'][] = array(
					'code'       => 'prostoresets',
					'title'      => $this->language->get('text_prostoresets').' - '.$setInfo['title'],
					'value'      => -$prostoresets['discount'],
					'sort_order' => $this->config->get('total_prostoresets_sort_order')
				);

				$total['total'] -= $prostoresets['discount'];
				$cartProducts = $prostoresets['cartproducts'];
			}	
		}

	}

}
