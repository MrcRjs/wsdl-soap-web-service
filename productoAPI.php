<?php
include_once './db.php';
include_once './Product.php';
include_once './Usuario.php';
require_once('nusoap/lib/nusoap.php');

//header('Content-Type: text/xml; charset=utf-8');

ini_set("log_errors", 1);
ini_set("error_log", "/reportes/php-error-producto.log");

$server = new soap_server();
$server->configureWSDL('WebServicesBUAP', 'urn:buap_api');
$server->soap_defencoding = 'UTF-8';
$server->decode_utf8 = false;
$server->encode_utf8 = true;
$server->wsdl->addComplexType(
 'RespuestaConsulta',
 'complexType',
 'struct',
 'all',
 '',
 array(
   'codigo' => ['name' => 'codigo', 'type' => 'xsd:int' ],
   'mensaje' => ['name' => 'mensaje', 'type' => 'xsd:string'],
   'xml' => ['name' => 'xml', 'type' => 'xsd:string']
 )
);


$server->register( 'getProd',
  array('user'=>'xsd:string','pass'=>'xsd:string' ,'categoria' => 'xsd:string'),
  array( 'return' => 'tns:RespuestaConsulta' ),
  'urn:producto',
  'urn:producto#getProd',
  'rpc',
  'encoded',
  'Nos da una lista de productos de cada categoría.'
);

$server->register( 'getDetails',
  array('user'=>'xsd:string','pass'=>'xsd:string','idPoducto' => 'xsd:string'),
  array( 'return' => 'tns:RespuestaConsulta' ),
  'urn:producto',
  'urn:producto#getDetails',
  'rpc',
  'encoded',
  'Nos da los detalles de un producto en especifico.'
);

function verifyUserCredentials($user, $pass){
  $TDB = new TiendaDB();
  $conn = $TDB->getConnection();
  $userBD = new Usuario($conn);
  return $userBD->checkUserPass($user, $pass);
}

function getProd($user, $pass, $categoria) {
  if(verifyUserCredentials($user, $pass)) {
    $TDB = new TiendaDB();
    $DB = $TDB->getConnection();
    $Products = new Product($DB);
    $prodResults = $Products->getProductByCategoria($categoria);
    return array(
     'codigo' => 200,
     'mensaje' => "Categoria ".$categoria. " encontrada.",
     'xml' => getProductsXml($prodResults)
    );
  } else {
    return array(
     'codigo' => 305,
     'mensaje' => "Usuario o contraseña incorrectos.",
     'xml' => ""
    );
  }
}

function getDetails($user, $pass, $clave) {
  if(verifyUserCredentials($user, $pass)) {
    $TDB = new TiendaDB();
    $DB = $TDB->getConnection();
    $Products = new Product($DB);
    $details = $Products->getProductDetails($clave);
    return array(
     'codigo' => 200,
     'mensaje' => "Detalles del producto ".$clave. " encontrados.",
     'xml' => getDetailsXml($details)
    );
  } else {
    return array(
     'codigo' => 305,
     'mensaje' => "Error: usuario o contraseña incorrecto",
     'xml' => ""
    );
  }

}

function getProductsXml($prods) {
  $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
  foreach ($prods as $categoria => $prods_in_categoria) {
    $xml .= "<productos categoria=\"".$categoria."\">";
    foreach ($prods_in_categoria as $clave_prod => $producto) {
      $xml .= "<producto clave=\"".$clave_prod."\">";
      $xml .= "<nombre>".$producto['nombre_prod']."</nombre>";
      $xml .= "<existencias>".$producto['stock']."</existencias>";
      $xml .= "</producto>";
    }
    $xml .= "</productos>";
  }
  return $xml;
}


function getDetailsXml($producto) {
  $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
  $xml .= "<detalle clave=\"".$producto["clave_prod"]."\">";
  $xml .= "<producto categoria=\"".$producto["categoria_prod"]."\">";
  $xml .= "<nombre>".$producto["nombre_prod"]."</nombre>";
  $xml .= "<marca>".$producto['marca_prod']."</marca>";
  $xml .= "<precio>".$producto['precio_prod']."</precio>";
  $xml .= "<descripcion>".$producto['descripcion_prod']."</descripcion>";
  $xml .= "</producto>";
  $xml .= "</detalle>";
 return $xml;
}

$data = !empty($HTTP_RAW_POST_DATA)?$HTTP_RAW_POST_DATA:'';
$server->service($data);

?>
