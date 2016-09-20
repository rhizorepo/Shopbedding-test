<?php
/**
 * ChannelBrain_BizSyncXL_Utils
 *
 * @author Gary MacDougall
 * @version $Id$
 * @copyright FreeportWeb, Inc., 19 February, 2012
 * @package ChannelBrain_BizSyncXL
 **/

define ('ENABLE_LOGGING', true);

/**
 * Define DocBlock
 **/

function writeXmlDeclaration()
{
	echo "<?xml version=\"1.0\" standalone=\"yes\" ?>";
}

// $this->write the open xml tag 
function writeStartTag($tag)
{
	echo '<' . $tag . '>';
}

// $this->write closing xml tag
function writeCloseTag($tag)
{
	echo '</' . $tag . '>';
}

// Output the given tag\value pair
function writeElement($tag, $value)
{
	writeStartTag($tag);
	echo htmlspecialchars($value);
	writeCloseTag($tag);
}

// Function used to output an error and quit.
function RestResultError($code, $message, $method)
{	

	writeStartTag("StatusCode");
	writeElement("Code", $code);
	writeElement("Name", $message);
	writeElement("Method", $method);
	writeCloseTag("StatusCode");
	// we always start with an opening BizSync tag, close it because we've ended output on an error.
	writeCloseTag("BizSync");
	die();
}	

/**
 * escapeInventoryFileData function.
 * 
 * @access public
 * @param mixed $str_data
 * @return void
 */
function escapeInventoryFileData($str_data) 
{
	$str_data = strip_tags($str_data);
	$searched_data = array("\r\n", "\n", "\r", "\t", "\\");
	$replaced_data = ' ';
	return str_replace($searched_data, $replaced_data, $str_data);
}


/**
 * toGmt function.
 * 
 * @access public
 * @param mixed $dateSql
 * @return void
 */
function toGmt($dateSql)
{
	$pattern = "/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2}):(\d{2})$/i";

	if (preg_match($pattern, $dateSql, $dt)) 
	{
		$dateUnix = mktime($dt[4], $dt[5], $dt[6], $dt[2], $dt[3], $dt[1]);
		return gmdate("Y-m-d H:i:s", $dateUnix);
	}

	return $dateSql;
}

/**
 * toLocalSqlDate function.
 * 
 * @access public
 * @param mixed $dateUnix
 * @return void
 */
function toLocalSqlDate($dateUnix)
{					       
    return date("Y-m-d H:i:s", $dateUnix);
}

/**
 * catalog_findStatAbbrev function
 *
 * @return void
 * @author Gary MacDougall
 **/
function catalog_findStateAbbrev( $state )
{
	$USStateCodes = array (
		"ALABAMA"        => "AL",
		"ALASKA"         => "AK",
		"AMERICAN SAMOA" => "AS",
		"ARIZONA"        => "AZ",
		"ARKANSAS"       => "AR",
		"CALIFORNIA"     => "CA",
		"COLORADO"       => "CO",
		"CONNECTICUT"    => "CT",
		"DELAWARE"       => "DE",
		"DISTRICT OF COLUMBIA" => "DC",
		"FEDERATED STATES OF MICRONESIA" => "FM",
		"FLORIDA"        => "FL",
		"GEORGIA"        => "GA",
		"GUAM"           => "GU",
		"HAWAII"         => "HI",
		"IDAHO"          => "ID",
		"ILLINOIS"       => "IL",
		"INDIANA"        => "IN",
		"IOWA"           => "IA",
		"KANSAS"         => "KS",
		"KENTUCKY"       => "KY",
		"LOUISIANA"      => "LA",
		"MAINE"          => "ME",
		"MARSHALL ISLANDS" => "MH",
		"MARYLAND"       => "MD",
		"MASSACHUSETTS"  => "MA",
		"MICHIGAN"       => "MI",
		"MINNESOTA"      => "MN",
		"MISSISSIPPI"    => "MS",
		"MISSOURI"       => "MO",
		"MONTANA"        => "MT",
		"NEBRASKA"       => "NE",
		"NEVADA"         => "NV",
		"NEW HAMPSHIRE"  => "NH",
		"NEW JERSEY"     => "NJ",
		"NEW MEXICO"     => "NM",
		"NEW YORK"       => "NY",
		"NORTH CAROLINA" => "NC",
		"NORTH DAKOTA"   => "ND",
		"NORTHERN MARIANA ISLAND" => "MP",
		"OHIO"           => "OH",
		"OKLAHOMA"       => "OK",
		"OREGON"         => "OR",
		"PALAU ISLAND"   => "PW",
		"PENNSYLVANIA"   => "PA",
		"PUERTO RICO"    => "PR",
		"RHODE ISLAND"   => "RI",
		"SOUTH CAROLINA" => "SC",
		"SOUTH DAKOTA"   => "SD",
		"TENNESSEE"      => "TN",
		"TEXAS"          => "TX",
		"UTAH"           => "UT",
		"VERMONT"        => "VT",
		"VIRGIN ISLANDS" => "VI",
		"VIRGINIA"       => "VA",
		"WASHINGTON"     => "WA",
		"WEST VIRGINIA"  => "WV",
		"WISCONSIN"      => "WI",
		"WYOMING"        => "WY",
	);

	$code = $state;
	$state = strtoupper($state);
	if( array_key_exists($state,$USStateCodes) )
	{
	   $code = $USStateCodes[$state];
	}

return $code ;
}

/**
 * logger function.
 * 
 * @access public
 * @param mixed $data
 * @param boolean
 * @return void
 */
function logger ($data, $bLogOverride = false)
{
	if (ENABLE_LOGGING == true || $bLogOverride)
	{
		$date = "[" . date("D M j G:i:s T Y") . "] ";
		$array_dump = '';
		if (is_array ($data))
		{
			foreach ($data as $key=>$value)
			{
				$array_dump .= 'key =>[' . $key . '] value=>[' . $value . ']\n';
			}		
		} else {
			$new_data = $date . $data;
		}
		if ($array_dump != '')
		{
			$new_data = $data . $array_dump;
		}
		$fp = fopen (ERROR_LOG_PATH, "a+");
		fwrite ($fp, $new_data . "\n");
		fclose($fp);
		$this->LastMessage = $data;
	}
}

/**
 * GetLatinLongDescription function
 *
 * @return string
 * @author Gary MacDougall
 **/
function GetLatinLongDescription ()
{
	return "Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem. Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?";
}

/**
 * GetLatinShortDescription function
 *
 * @return string
 * @author Gary MacDougall
 **/
function GetLatinShortDescription ()
{
	return "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";
}

/**
 * GetLatinProductName function
 *
 * @return string
 * @author Gary MacDougall
 **/
function GetLatinProductName ()
{
	return "Lorem Ipsum Dolor Sit Amet";
}
?>
