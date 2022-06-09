	/**
	 * Usefull hexadecimal to decimal converter. Returns an array of RGB from a given hexadecimal color.
	 * @param string $color
	 * @return array $color($R, $G, $B)
	 */
	public function hex2dec($color = '000000') {
		$tbl_color = array();
		
		/** Hier entsteht der Fehler
		  original Stand hier: */
		 //if (!strstr('#', $color)){
		 //ich habe !strstr('#', $color) in True umgewandelt
		 
		if (!strstr('#', $color)){
			$color = '#' . $color;
	}
		$tbl_color['R'] = hexdec(substr($color, 1, 2));
		$tbl_color['G'] = hexdec(substr($color, 3, 2));
		$tbl_color['B'] = hexdec(substr($color, 5, 2));
		return $tbl_color;
	}
