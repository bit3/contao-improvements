<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Improvements for Contao Open Source CMS
 * Copyright (C) 2011 Tristan Lins
 *
 * Extension for:
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 * 
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  InfinitySoft 2011
 * @author     Tristan Lins <tristan.lins@infinitysoft.de>
 * @package    Improvements
 * @license    LGPL
 * @filesource
 */


/**
 * Class Improvements
 */
class Improvements extends Controller
{
	public function hookOutputBackendTemplate($strContent, $strTemplate)
	{
		if ($strTemplate == 'be_main' && version_compare(VERSION, '3', '<'))
		{
			$strContent = str_replace('</body>', (version_compare(VERSION, '2.10')>=0 ? '
<script>' : '
<script type="text/javascript">
<!--//--><![CDATA[//><!--') . '
$(window).addEvent(\'domready\', function() {
	var e = $$(\'div.tl_formbody_edit fieldset.tl_tbox input[type="text"], div.tl_formbody_edit fieldset.tl_tbox input[type="checkbox"], div.tl_formbody_edit fieldset.tl_tbox input[type="radio"], div.tl_formbody_edit fieldset.tl_tbox select\');
	if (e && e.length > 0)
	{
		e[0].focus();
	}
});' . (version_compare(VERSION, '2.10')<0 ? '
//--><!]]>' : '') . '
</script>

</body>', $strContent);
		}
		return $strContent;
	}
	
	public function hookReplaceInsertTags($strTag)
	{
		$arrParts = explode('::', $strTag, 2);

		// basic insert tags
		switch ($arrParts[0])
		{
			// access page properties
			case 'page':
				$strProperty = $arrParts[1];
				global $objPage;
				return $objPage->$strProperty;

			case 'image_src':$width = null;
				$height = null;
				$strFile = $arrParts[1];
				$mode = '';

				// Take arguments
				if (strpos($arrParts[1], '?') !== false)
				{
					$this->import('String');

					$arrChunks = explode('?', urldecode($arrParts[1]), 2);
					$strSource = $this->String->decodeEntities($arrChunks[1]);
					$strSource = str_replace('[&]', '&', $strSource);
					$arrParams = explode('&', $strSource);

					foreach ($arrParams as $strParam)
					{
						list($key, $value) = explode('=', $strParam);

						switch ($key)
						{
							case 'width':
								$width = $value;
								break;

							case 'height':
								$height = $value;
								break;

							case 'mode':
								$mode = $value;
								break;
						}
					}

					$strFile = $arrChunks[0];
				}

				// Sanitize path
				$strFile = str_replace('../', '', $strFile);

				// Check maximum image width
				if ($GLOBALS['TL_CONFIG']['maxImageWidth'] > 0 && $width > $GLOBALS['TL_CONFIG']['maxImageWidth'])
				{
					$width = $GLOBALS['TL_CONFIG']['maxImageWidth'];
					$height = null;
				}

				// Generate the thumbnail image
				return $this->getImage($strFile, $width, $height, $mode);
		}

		// wrapper insert tags
		if (in_array($arrParts[0], $GLOBALS['TL_INSERTTAG_WRAPPER']))
		{
			return call_user_func($arrParts[0], $this->replaceInsertTags('{{' . $arrParts[1] . '}}'));
		}

		return false;
	}
}

?>