<?php

/**
 * @project XG Proyect
 * @version 2.10.x build 0000
 * @copyright Copyright (C) 2008 - 2012
 */

if(!defined('INSIDE')){ die(header ( 'location:../../' ));}

class xml
{
	// INSTANCIA DE simplexml
	private $instance_xml = NULL;
	private $sheet = NULL;

	// ABRE LA HOJA y LA LEE
	public function __construct ( $sheet )
	{
		$this->sheet	=	$sheet; // PASAMOS LA HOJA A TOMAR

		// SETEAMOS EL DIRECTORIO BASE DE LECTURA.
		$path			=	"http://" . $_SERVER['HTTP_HOST'];
		$path		   .=	preg_replace ( '@/+$@' , '' , dirname ( $_SERVER['SCRIPT_NAME'] ) ) . '/includes/xml/';
		$path			=	str_replace ( array ( '/adm' , '/install' ) , '' , $path );

		// CHECK  instance_xml EXISTENCE
		if ( !$this->instance_xml )
		{
			// START DOM
			$document = new DOMDocument("1.0");
			$document->load ( $path . $this->sheet );

			// ERROR
			if ( $document == FALSE )
			{
				die ( 'Error nº1: Problems loading the page content.' );
			}

			// CREA UN OBJETO DOM
			$this->instance_xml = $document;
		}
	}

	// OBTIENE LOS ELEMENTOS DE ACUERDO A LA ETIQUETA PASADA
	private function get_elements ( $element )
	{
		return $this->instance_xml->getElementsByTagName( $element );
	}

	// RETORNA LAS CONFIGURACIONES
	public function get_configs ()
	{
		$configs = $this->get_elements ( 'config' );

		foreach ( $configs as $config )
		{
			$name					=	$config->getElementsByTagName( "name" )->item(0)->nodeValue;
			$value					=	$config->getElementsByTagName( "value" )->item(0)->nodeValue;
			$config_array[$name]	=	$value;
		}

		return $config_array;
	}

	// RETORNA UNA CONFIGURACION ESPECIFICA
	public function get_config ( $config_name )
	{
		$xpath				=	new DOMXPath ( $this->instance_xml );
		$result				=	$xpath->query ( '/configurations/config[name="' . $config_name . '"]/value' );
		return $result->item(0)->nodeValue;
	}

	// ESCRIBE LA CONFIGURACION
	public function write_config ( $config_name , $config_value )
	{
		$xpath				=	new DOMXPath ( $this->instance_xml );
		$result				=	$xpath->query ( '/configurations/config[name="' . $config_name . '"]/value' );
		$result->item(0)
				->nodeValue	= $config_value;

	//	header ( "Content-type: text/xml" );
		if ( $this->instance_xml->save ( XGP_ROOT . "includes/xml/" . $this->sheet ) )
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
}
?>