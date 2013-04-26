<?php
/**
 * Defines the NEFile class to handle file uploads and downloads
 * @package neolith
 * @version $Id: NEFile.php 3053 2007-07-25 23:21:38Z gkrupa $
 * @author NovaEdge Technologies LLC
 * @copyright Copyright &copy; 2006-2007, NovaEdge Technologies LLC
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 */
 
include_once('NEObject.php');

define('NEFILE_TYPE_CSV','NEFILE_TYPE_CSV');
define('NEFILE_TYPE_PDF','NEFILE_TYPE_PDF');
define('NEFILE_TYPE_JPG','NEFILE_TYPE_JPG');
define('NEFILE_TYPE_BIN','NEFILE_TYPE_BIN');

/**
 * Defines the NEFile class to handle file uploads and downloads
 * @package neolith
 */
class NEFile extends NEObject
{
  var $data = null;
  
  /**
   * the class constructor
   */
  function __construct()
  {
    parent::__construct();
  }
   
  /**
   * generic file upload function
   * @param string $filename name of the file on the users filesystem
   * @param string $tempname temporary name of the file generated by php on server
   * @param string $target_path path on server filesystem to write the file to
   * @param string $permissions sets permissions on the file
   * @return boolean returns true or false depending on if successful
   */
  function upload($filename, $tmp_name, $target_path, $permissions)
  {    
    if (move_uploaded_file($tmp_name, $target_path.$filename))
    {
      chmod($target_path.$filename, $permissions);
    	return true;
    }
    else
    {
    	return false;
    }  	
  }
  
  /**
   * generic file download function
   * @param string $filename name of the file on the filesystem
   * @param string $filepath path to the file, relative to site
   * @param string $filetype the type of the file; sets the MIME type
   * @param string $origFilename set the name of the download file
   * @param boolean $inline set TRUE if the download method is "inline", otherwise default is "attachment"
   * @return boolean returns true or false depending on if successful
   */
  function download($fileName, $filePath, $fileType = NEFILE_TYPE_CSV, $origFilename = null, $inline = false)
  {
    //WARNING! this includes header() and die() calls!
    $fileName = $fileName;
    $fileFullName = $filePath.$fileName;
    
    //set the download file name
    if ($origFilename)
    {
      $downloadFilename = $origFilename;
    }
    else
    {
      $downloadFilename = $fileName;
    }
    
    //set the download method
    $method = 'attachment';
    if ($inline)
    {
      $method = 'inline';
    }
    
    if ($this->read($fileName, $filePath) == false)
    {
      return false;
    }
    
    switch ($fileType)
    {
      case NEFILE_TYPE_JPG:
        header("Content-Type: image/jpg; name=\"$downloadFilename\"");
        break;
      case NEFILE_TYPE_PDF:
        header("Content-Type: application/pdf; name=\"$downloadFilename\"");
        break;
      case NEFILE_TYPE_CSV:
        header("Content-Type: text/csv; name=\"$downloadFilename\"");
        break;
      case NEFILE_TYPE_BIN:
      default:
        header("Content-Type: application/octet-stream; name=\"$downloadFilename\"");
        break;
    }
    header('Content-Length: '.strlen($this->data));
    header("Content-Disposition: $method; filename=\"$downloadFilename\"");
    //the following 'header("Pragma: ");' is required for IE HTTPS downloads to work
    //...cf. http://www.php.net/manual/en/function.session-cache-limiter.php#55578
    header("Pragma: ");
    print $this->data;
    die();
  }

  function downloadData($data, $fileName, $fileType = NEFILE_TYPE_CSV, $inline = false)
  {
    switch ($fileType)
    {
      case NEFILE_TYPE_JPG:
        header('Content-Type: application/jpg');
        break;
      case NEFILE_TYPE_PDF:
        header('Content-Type: application/pdf');
        break;
      default:
        header('Content-Type: text/csv');
        break;
    }
    
    $method = 'attachment';
    if ($inline)
    {
      $method = 'inline';
    }
    
    header('Content-Length: '.strlen($data));
    header("Content-Disposition: $method; filename=\"$fileName\"");
    //the following 'header("Pragma: ");' is required for IE HTTPS downloads to work
    //...cf. http://www.php.net/manual/en/function.session-cache-limiter.php#55578
    header("Pragma: ");
    print $data;
    die();
  }
  
  /**
   * generic file write function
   * @param string $filename name of file to write out
   * @param string $data file data to write out
   * @return boolean returns true or false depending on if successful
   * @todo file data is stored in $this-data without any access functions
   */
  function write($filename, $data)
  {
    if (!($file = fopen($filename, 'wb')))
    {
      return false;
    }
    if (fwrite($file, $data) == false)
    {
      //error
      return false;
    }
    fclose($file);
    return true;
  }
  
  /**
   * generic file read function
   * @param string $filename name of file to read in
   * @param string $filepath path to the file, relative to site
   * @return boolean returns true or false depending on if read was successful
   * @todo file data is stored in $this-data without any access functions
   */
  function read($fileName, $filePath)
  {
    //build the file full path name
    $fileName = $fileName;
    $fileFullName = $filePath.$fileName;
    
    //open the file
    if (!($file = fopen($fileFullName, 'rb')))
    {
      return false;
    }
    //read the file in
    $this->data = fread($file, filesize($fileFullName));
    
    //close the file
    fclose($file);
    return true;
  }
  
  /**
   * generic file delete function
   * @param string $filename name of file to delete
   * @param string $filepath path to the file, relative to site
   * @return boolean returns true or false depending on if successful
   */
  function delete($filename, $filepath)
  {
    //checks to make sure file exist on filesystem before trying to delete
    if (file_exists($filepath.$filename) == false)
    {
      return false;
    }
    
  	if (unlink($filepath.$filename) == false)
    {
      return false;  	
    }
    
    return true;
  }
  
  /**
   * sets permissions on a file
   * @param string $filename full path and name of file on server filesystem
   * @param string $permissions permissions to be set on file
   */
  function setPermissions($filename, $permissions)
  {
    chmod($filename, $permissions);  	
  }
  
  /**
   * creates a directory unless exists
   * @param string $path directory to create
   */
  function mkDir($path)
  {
    if (!is_dir($path))
    {
      if (!mkdir($path))
      {
        return false;
      }
    }
    return true;
  }

	function getFileSizeKb($filename)
	{
		$size = filesize($filename);
		return intval( $size/1024 );
	}
}
 
?>