<?php
/**
 * CodeIgniter PDF Library
 *
 * Generate PDF in CodeIgniter applications.
 *
 * @package            CodeIgniter
 * @subpackage        Libraries
 * @category        Libraries
 * @author            CodexWorld
 * @license            https://www.codexworld.com/license/
 * @link            https://www.codexworld.com
 */

// reference the Dompdf namespace
use Dompdf\Dompdf;
class pdf
{
    public function __construct(){
        
        // include autoloader
        require_once dirname(__FILE__).'/dompdf/autoload.inc.php';
        
        // instantiate and use the dompdf class 
  //       $options = new Options();
		// $CI->assertFalse($options->getIsJavascriptEnabled());
        $pdf = new DOMPDF();
		$pdf = new Dompdf(array('enable_remote' => true));
        $CI =& get_instance();
        $CI->dompdf = $pdf;
        
    }
}
?>