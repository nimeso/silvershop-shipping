<?php

class ShippingEstimateForm extends Form{
	
	function __construct($controller, $name = "ShippingEstimateForm") {
		$countries = SiteConfig::current_site_config()->getCountriesList();
		$countryfield = (count($countries)) ? 
			DropdownField::create("Country", _t('Address.COUNTRY', 'Country'), $countries) : 
			ReadonlyField::create("Country", _t('Address.COUNTRY', 'Country'));
		$countryfield->setHasEmptyDefault(true);
		$fields = new FieldList(
			$countryfield,
			TextField::create('State', _t('Address.STATE', 'State')),
			TextField::create('City', _t('Address.CITY', 'City')),
			TextField::create('PostalCode', _t('Address.POSTALCODE', 'Postal Code'))
		);
		$actions =  new FieldList(
			FormAction::create("submit", "Submit")
		);
		$validator = new RequiredFields(array(
			'Country'
		));
		parent::__construct($controller, $name, $fields, $actions, $validator);
		$this->extend('updateForm');
	}
	
	function submit($data, $form) {
		if($order = ShoppingCart::singleton()->current()){
			$estimator = new ShippingEstimator(
				$order,
				new Address(Convert::raw2sql($data))
			);
			$estimates = $estimator->getEstimates();			
			Session::set("ShippingEstimates", $estimates);
			if(Director::is_ajax()){
				//TODO: replace with an AJAXResponse class that can output to different formats
				return json_encode($estimates->toArray());
			}
		}
		$this->controller->redirectBack();
	}
	
}